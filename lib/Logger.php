<?php

namespace Integsoft\Hub\Probe;

use Integsoft\Hub\Probe\Client\Client;
use Integsoft\Hub\Probe\Client\DefaultClient;
use Integsoft\Hub\Probe\Configuration\Configuration;
use Integsoft\Hub\Probe\Configuration\MessageFactory;
use Integsoft\Hub\Probe\Mapper\ExceptionMapper;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Symfony\Component\HttpClient\HttpClient;

class Logger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param MessageFactory $messageFactory
     * @param Client         $client
     */
    public function __construct(MessageFactory $messageFactory, Client $client)
    {
        $this->messageFactory = $messageFactory;
        $this->client = $client;
    }

    /**
     * @param MessageFactory $messageFactory
     * @return static
     */
    public static function create(MessageFactory $messageFactory)
    {
        if (!class_exists(HttpClient::class)) {
            throw new \RuntimeException('Please install package "symfony/http-client" or provide custom Client implementation.');
        }

        return new self($messageFactory, new DefaultClient());
    }

    /**
     * @param string $dsn
     * @return static
     */
    public static function fromDsn(string $dsn)
    {
        return self::create(MessageFactory::instance(Configuration::fromDsn($dsn)));
    }

    /**
     * @param mixed $level
     * @param mixed $message
     * @param array $context
     */
    public function log($level, $message, array $context = array()): void
    {
        if ($message instanceof \Throwable) {
            $context[ExceptionMapper::DEFAULT_EXCEPTION_KEY] = $message;
            $message = $message->getMessage();
        }

        $this->client->sendMessage(
            $this->messageFactory->getConfiguration(),
            $this->messageFactory->createMessage($level, $message, $context)
        );
    }
}
