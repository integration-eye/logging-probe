<?php

namespace Integsoft\Hub\Probe\Mapper;

class DateTimeMapper implements Mapper
{
    public const DEFAULT_FORMAT = 'c';

    /**
     * @var string
     */
    private $format;

    /**
     * @param string $format
     */
    public function __construct(string $format = self::DEFAULT_FORMAT)
    {
        $this->format = $format;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public function supports(string $key, $value): bool
    {
        return $value instanceof \DateTime;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return array
     */
    public function map(string $key, $value): array
    {
        assert($value instanceof \DateTime);

        return [$key => $value->format($this->format)];
    }
}
