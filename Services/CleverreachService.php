<?php

declare(strict_types=1);

namespace RevisionTen\Cleverreach\Services;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class CleverreachService
{
    /** @var RequestStack */
    private $requestStack;

    /** @var array */
    private $config;

    /** @var Client */
    private $client;

    public function __construct(RequestStack $requestStack, array $config)
    {
        $this->requestStack = $requestStack;
        $this->config = $config;

        $this->client = new Client([
            'base_uri' => 'https://rest.cleverreach.com/v2/',
            'timeout' => 60,
            'allow_redirects' => true,
        ]);
    }

    public function subscribe(string $campaign, string $email, string $source = null, array $globalAttributes = []): bool
    {
        if (!isset($this->config['campaigns'][$campaign])) {
            return false;
        }

        $token = $this->getApiToken();
        if (null === $token) {
            return false;
        }

        if (null === $source) {
            $source = 'symfony';
        }

        $requestBody = json_encode([
            'email' => $email,
            'registered' => time(),
            'activated' => 0,
            'source' => $source,
            'global_attributes' => $globalAttributes,
        ]);

        // Add subscriber to recipient list.
        $response = $this->client->request('POST', 'groups.json/'.$this->config['campaigns'][$campaign]['list_id'].'/receivers', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
            'body' => $requestBody,
            'http_errors' => false,
        ]);

        if (200 !== $response->getStatusCode()) {
            return false;
        }

        $subscriber = json_decode($response->getBody()->getContents());

        // Send optin mail to (inactive) new subscriber.
        $masterRequest = $this->requestStack->getMasterRequest();
        $response = $this->client->request('POST', 'forms.json/'.$this->config['campaigns'][$campaign]['form_id'].'/send/activate', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
            \GuzzleHttp\RequestOptions::JSON => [
                'email' => $email,
                'doidata' => [
                    'user_ip' => $masterRequest->getClientIp(),
                    'referer' => $masterRequest->getSchemeAndHttpHost().$masterRequest->getRequestUri(),
                    'user_agent' => $masterRequest->headers->get('User-Agent'),
                ],
            ],
            'http_errors' => false,
        ]);
        $optinMailSent = 200 === $response->getStatusCode();

        return $token && $subscriber && $optinMailSent;
    }

    /**
     * Unsubscribes a user from all lists.
     *
     * @param string $campaign
     * @param string $email
     *
     * @return bool
     */
    public function unsubscribe(string $campaign, string $email): bool
    {
        if (!isset($this->config['campaigns'][$campaign])) {
            return false;
        }

        $token = $this->getApiToken();
        if (null === $token) {
            return false;
        }

        // Delete subscriber.
        $response = $this->client->request('DELETE', '/receivers.json/'.urlencode($email), [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
            'http_errors' => false,
        ]);

        return 200 === $response->getStatusCode();
    }

    /**
     * Retrieves a new api token.
     *
     * @return string|null
     */
    private function getApiToken(): ?string
    {
        $response = $this->client->request('POST', 'login.json', [
            \GuzzleHttp\RequestOptions::JSON => [
                'client_id' => $this->config['client_id'],
                'login' => $this->config['user'],
                'password' => $this->config['password'],
            ],
            'http_errors' => true,
        ]);

        if (200 === $response->getStatusCode()) {
            // Return the api token.
            return json_decode($response->getBody()->getContents());
        }

        return null;
    }
}
