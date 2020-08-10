<?php

namespace Integsoft\Hub\Probe\Mapper;

class JsonMapper implements Mapper
{
    /**
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public function supports(string $key, $value): bool
    {
        return $value instanceof \JsonSerializable;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return array
     */
    public function map(string $key, $value): array
    {
        assert($value instanceof \JsonSerializable);

        return [$key => json_encode($value)];
    }
}
