<?php

namespace Integsoft\Hub\Probe\Provider;

interface PrincipalProvider
{
    /**
     * @return string|null
     */
    public function getPrincipal(): ?string;
}
