<?php

namespace Integsoft\Hub\Probe\Configuration;

use Integsoft\Hub\Probe\Mapper\DateTimeMapper;
use Integsoft\Hub\Probe\Mapper\ExceptionMapper;
use Integsoft\Hub\Probe\Mapper\JsonMapper;
use Integsoft\Hub\Probe\Mapper\Mapper;
use Integsoft\Hub\Probe\Provider\DefaultPrincipalProvider;
use Integsoft\Hub\Probe\Provider\PrincipalProvider;
use Psr\Log\LogLevel;

class MessageFactory
{
    private const PROBE_TYPE = 'php';

    private const OPTIONAL_METADATA = [
        '@exception',
        '@rootException',
        '@stackTrace',
        '@logPoint',
        '@principal',
    ];

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var PrincipalProvider
     */
    private $principalProvider;

    /**
     * @var Mapper[]
     */
    private $mappers = [];

    /**
     * @param Configuration          $configuration
     * @param PrincipalProvider|null $principalProvider
     * @param iterable|Mapper[]      $mappers
     */
    public function __construct(Configuration $configuration, ?PrincipalProvider $principalProvider = null, iterable $mappers = [])
    {
        $this->configuration = $configuration;
        $this->principalProvider = $principalProvider ?? new DefaultPrincipalProvider(null);

        foreach ($mappers as $mapper) {
            $this->mappers[] = $mapper;
        }
    }

    /**
     * @param Configuration $configuration
     * @return static
     */
    public static function create(Configuration $configuration): self
    {
        return new self($configuration);
    }

    /**
     * @param Configuration $configuration
     * @return static
     */
    public static function instance(Configuration $configuration): self
    {
        return self::create($configuration)
            ->addDateTimeMapper()
            ->addExceptionMapper()
            ->addJsonMapper();
    }

    /**
     * @param Mapper $mapper
     * @return static
     */
    public function addMapper(Mapper $mapper): self
    {
        $this->mappers[] = $mapper;

        return $this;
    }

    /**
     * @param PrincipalProvider $principalProvider
     * @return static
     */
    public function setPrincipalProvider(PrincipalProvider $principalProvider): self
    {
        $this->principalProvider = $principalProvider;

        return $this;
    }

    /**
     * @param string $exceptionKey
     * @return static
     */
    public function addExceptionMapper(string $exceptionKey = ExceptionMapper::DEFAULT_EXCEPTION_KEY): self
    {
        return $this->addMapper(new ExceptionMapper($exceptionKey));
    }

    /**
     * @return static
     */
    public function addJsonMapper(): self
    {
        return $this->addMapper(new JsonMapper());
    }

    /**
     * @param string $format
     * @return static
     */
    public function addDateTimeMapper(string $format = DateTimeMapper::DEFAULT_FORMAT): self
    {
        return $this->addMapper(new DateTimeMapper($format));
    }

    /**
     * @param string|null $principal
     * @return $this
     */
    public function setPrincipal(?string $principal): self
    {
        return $this->setPrincipalProvider(new DefaultPrincipalProvider($principal));
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @param mixed $level
     * @param mixed $message
     * @param array $context
     * @return array
     */
    public function createMessage($level, $message, array $context): array
    {
        $metadata = [
            '@timestamp' => (int) (microtime(true) * 1000),
            '@probeType' => self::PROBE_TYPE,
            '@messageType' => $this->getMessageType((string) $level),
            '@logLevel' => (string) $level,
            '@messageText' => (string) $message,
            '@principal' => $this->principalProvider->getPrincipal(),
        ];

        if ($appName = $this->configuration->getAppName()) {
            $metadata['@applicationName'] = $appName;
        }

        if ($serverName = $this->configuration->getServerName()) {
            $metadata['@serverName'] = $serverName;
        }

        if ($serverIp = $this->configuration->getServerIp()) {
            $metadata['@serverIp'] = $serverIp;
        }

        $extra = [];

        foreach ($this->applyMappers($context) as $key => $value) {
            if (in_array($key, self::OPTIONAL_METADATA, true)) {
                $metadata[$key] = $value;
            } else {
                $extra[$key] = $value;
            }
        }

        if (count($extra)) {
            $metadata['@extraData'] = $extra;
        }

        return $metadata;
    }

    /**
     * @param array $context
     * @return array
     */
    private function applyMappers(array $context): array
    {
        $result = [];

        foreach ($context as $key => $value) {
            $key = (string) $key;
            $mapped = [$key => $value];

            foreach ($this->mappers as $mapper) {
                if ($mapper->supports($key, $value)) {
                    $mapped = $mapper->map($key, $value);
                    break;
                }
            }

            foreach ($mapped as $newKey => $newValue) {
                $result[$newKey] = (string) $newValue;
            }
        }

        return $result;
    }

    /**
     * @param string $level
     * @return string
     */
    private function getMessageType(string $level): string
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::CRITICAL:
            case LogLevel::WARNING:
                return 'error';

            default:
                return 'normal';
        }
    }
}
