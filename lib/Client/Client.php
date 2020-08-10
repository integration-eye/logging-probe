<?php

namespace Integsoft\Hub\Probe\Client;

use Integsoft\Hub\Probe\Configuration\Configuration;

interface Client
{
    /**
     * @param Configuration $configuration
     * @param array         $payload
     */
    public function sendMessage(Configuration $configuration, array $payload): void;
}
