<?php

namespace Integsoft\Hub\Probe\Client;

use Integsoft\Hub\Probe\Configuration\Configuration;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DefaultClient implements Client
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->client = HttpClient::create();
    }

    /**
     * @param Configuration $configuration
     * @param array         $payload
     * @throws TransportExceptionInterface
     */
    public function sendMessage(Configuration $configuration, array $payload): void
    {
        $this->client->request('POST', $configuration->getUrl(), [
            'auth_basic' => $configuration->getBasicAuth(),
            'json' => $payload,
        ]);
    }
}
