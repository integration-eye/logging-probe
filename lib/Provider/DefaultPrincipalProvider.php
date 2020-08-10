<?php

namespace Integsoft\Hub\Probe\Provider;

class DefaultPrincipalProvider implements PrincipalProvider
{
    /**
     * @var string|null
     */
    private $principal;

    /**
     * @param string|null $principal
     */
    public function __construct(?string $principal)
    {
        $this->principal = $principal;
    }

    /**
     * @return string|null
     */
    public function getPrincipal(): ?string
    {
        return $this->principal;
    }
}
