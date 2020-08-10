<?php

namespace Integsoft\Hub\Probe\Mapper;

interface Mapper
{
    /**
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public function supports(string $key, $value): bool;

    /**
     * @param string $key
     * @param mixed  $value
     * @return array
     */
    public function map(string $key, $value): array;
}
