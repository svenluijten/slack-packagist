<?php

namespace App\Http\Controllers;

use Packagist\Api\Client;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class SlackController extends Controller
{
    /**
     * Respond to the webhook from Slack.
     *
     * @param Request $request
     * @return array
     */
    public function hook(Request $request)
    {
        $packagist = new Client();
        $query = trim($request->get('text'), '/');
        $results = $packagist->search($query);

        if (isset($results[0])) {
            return $this->respondWithPackage($results[0]);
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
     * @param $package
     * @return array
     */
    protected function respondWithPackage($package)
    {
        return [
            'response_type' => 'in_channel',
            'attachments' => [
                [
                    'fallback' => $package->getDescription(),
                    'title' => $package->getName(),
                    'title_link' => $package->getRepository(),
                    'text' => $package->getDescription(),
                    'fields' => [
                        [
                            'title' => 'Stars',
                            'value' => $package->getFavers(),
                            'short' => true,
                        ],
                        [
                            'title' => 'Downloads',
                            'value' => $package->getDownloads(),
                            'short' => true,
                        ],
                    ],
                ]
            ],
        ];
    }

    public function installed()
    {
    	return view('installed');
    }

    public function home()
    {
    	return view('home');
    }
}
