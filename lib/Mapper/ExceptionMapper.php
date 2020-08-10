<?php

namespace Integsoft\Hub\Probe\Mapper;

class ExceptionMapper implements Mapper
{
    public const DEFAULT_EXCEPTION_KEY = 'exception';

    /**
     * @var string
     */
    private $exceptionKey;

    /**
     * @param string $exceptionKey
     */
    public function __construct(string $exceptionKey = self::DEFAULT_EXCEPTION_KEY)
    {
        $this->exceptionKey = $exceptionKey;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    public function supports(string $key, $value): bool
    {
        return $value instanceof \Throwable;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return array
     */
    public function map(string $key, $value): array
    {
        assert($value instanceof \Throwable);

        $class = get_class($value);
        $trace = $value->getTraceAsString();

        $data = [
            "${key}_class" => $class,
            "${key}_stacktrace" => $trace,
            "${key}_message" => $value->getMessage(),
            "${key}_code" => $value->getCode(),
            "${key}_file" => $value->getFile(),
            "${key}_line" => $value->getLine(),
        ];

        if ($key === $this->exceptionKey) {
            $data['@exception'] = $class;
            $data['@stackTrace'] = $trace;
        }

        return $data;
    }
}
