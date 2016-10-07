<?php

namespace App\Http\Controllers;

use App\ValueObjects\Package;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class SlackController extends Controller
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Instantiate the SlackController.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Respond to the webhook from Slack.
     *
     * @param Request $request
     * @return array
     */
    public function hook(Request $request)
    {
        $query = trim($request->get('text'), '/');

        if (str_contains($query, '/')) {
            $package = $this->searchWithVendor($query);
        } else {
            $package = $this->searchWithoutVendor($query);
        }

        if ($package instanceof Package) {
            return $this->respondWithPackage($package);
        }

        return [
            'response_type' => 'ephemeral',
            'text' => 'I couldn\'t find a package with those search terms, sorry!',
        ];
    }

    /**
     * Authenticate with Slack.
     * Only slightly stolen from Chris White <https://github.com/cwhite92/XKCDBot>
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function auth(Request $request)
    {
        $provider = new GenericProvider([
            'clientId' => getenv('SLACK_CLIENT_ID'),
            'clientSecret' => getenv('SLACK_CLIENT_SECRET'),
            'redirectUri' => getenv('SLACK_REDIRECT_URI'),
            'urlAccessToken' => 'https://slack.com/api/oauth.access',
            'urlAuthorize' => 'https://slack.com/oauth/authorize',
            'urlResourceOwnerDetails' => 'https://slack.com/api/users.info',
        ]);

        if ($request->get('code')) {
            try {
                // We'll just request an access token and do nothing with it, completing the OAuth flow
                $provider->getAccessToken('authorization_code', [
                    'code' => $request->get('code')
                ]);
            } catch (IdentityProviderException $e) {
                // Silently fail... shhhh.
            }
        }

        return redirect('/installed');
    }

    /**
     * Respond with a formatted response for Slack.
     *
     * @param Package $package
     * @return array
     */
    protected function respondWithPackage(Package $package)
    {
        return [
            'response_type' => 'in_channel',
            'attachments' => [
                [
                    'fallback' => $package->getDescription(),
                    'title' => $package->getName(),
                    'title_link' => $package->getUrl(),
                    'text' => $package->getDescription(),
                    'mrkdwn_in' => ['fields'],
                    'fields' => [
                        [
                            'title' => ':sparkles: Stars',
                            'value' => $package->getFavers(),
                            'short' => true,
                        ],
                        [
                            'title' => ':arrow_down: Downloads',
                            'value' => $package->getDownloads(),
                            'short' => true,
                        ],
                        [
                            'title' => ':package: Install command',
                            'value' => $package->getInstallCommand(),
                            'short' => false,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Search packagist regularly.
     *
     * @param $query
     * @return Package|bool
     */
    protected function searchWithoutVendor($query)
    {
        $result = $this->client->request('GET', 'https://packagist.org/search.json?q=' . $query);
        $packages = json_decode($result->getBody(), true);

        if (empty($packages['results'])) {
            return false;
        }

        $package = $packages['results'][0];

        return Package::fromSearchResult($package);
    }

    /**
     * Go to a specific package directly.
     *
     * @param $query
     * @return Package|bool
     */
    protected function searchWithVendor($query)
    {
        try {
            $result = $this->client->request('GET', 'https://packagist.org/packages/' . $query . '.json');
        } catch (\Exception $e) {
            return $this->searchWithoutVendor($query);
        }

        $response = json_decode($result->getBody(), true);

        return Package::fromPackageDetails($response);
    }
}
