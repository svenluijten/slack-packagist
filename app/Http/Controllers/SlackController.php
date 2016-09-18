<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class SlackController extends Controller
{
    protected $client;

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

        if ($package) {
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
                // We'll just request an access token and do nothing with it, which will complete the OAuth flow.
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
     * @param array $package
     * @return array
     */
    protected function respondWithPackage(array $package)
    {
        return [
            'response_type' => 'in_channel',
            'attachments' => [
                [
                    'fallback' => $package['description'],
                    'title' => $package['name'],
                    'title_link' => $package['url'],
                    'text' => $package['description'],
                    'fields' => [
                        [
                            'title' => ':sparkles: Stars',
                            'value' => number_format($package['favers']),
                            'short' => true,
                        ],
                        [
                            'title' => ':arrow_down: Downloads',
                            'value' => number_format($package['downloads']),
                            'short' => true,
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
     * @return array|bool
     */
    protected function searchWithoutVendor($query)
    {
        $result = $this->client->request('GET', 'https://packagist.org/search.json?q='.$query);
        $packages = json_decode($result->getBody());

        if (empty($packages->results)) {
            return false;
        }

        $package = $packages->results[0];

        return [
            'name' => $package->name,
            'description' => $package->description,
            'url' => $package->url,
            'favers' => $package->favers,
            'downloads' => $package->downloads,
        ];
    }

    /**
     * Go to a specific package directly.
     *
     * @param $query
     * @return array|bool
     */
    protected function searchWithVendor($query)
    {
        try {
            $result = $this->client->request('GET', 'https://packagist.org/packages/'.$query.'.json');
        } catch (\Exception $e) {
            return $this->searchWithoutVendor($query);
        }

        $response = json_decode($result->getBody());
        $package = $response->package;

        return [
            'name' => $package->name,
            'description' => $package->description,
            'url' => 'https://packagist.org/packages/'.$query,
            'favers' => $package->github_stars,
            'downloads' => $package->downloads->total,
        ];
    }
}
