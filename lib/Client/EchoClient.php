<?php

namespace Integsoft\Hub\Probe\Client;

use Integsoft\Hub\Probe\Configuration\Configuration;

class EchoClient implements Client
{
    /**
     * @param Configuration $configuration
     * @param array         $payload
     */
    public function sendMessage(Configuration $configuration, array $payload): void
    {
        echo sprintf("Url: %s\n", $configuration->getUrl());
        echo sprintf("Authorization: Basic %s\n", $configuration->getBasicAuth());
        echo sprintf("Payload:\n%s\n", json_encode($payload, JSON_PRETTY_PRINT));
    }
}
