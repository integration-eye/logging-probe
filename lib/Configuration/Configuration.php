<?php

namespace Integsoft\Hub\Probe\Configuration;

class Configuration
{
    public const HTTP = 'http';
    public const HTTPS = 'https';

    private const DEFAULT_PATH = '/logging-hub-api/store-log-data';

    /**
     * @var string
     */
    private $scheme;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string|null
     */
    private $port;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var string|null
     */
    private $user;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var string|null
     */
    private $appName;

    /**
     * @var string|null
     */
    private $serverName;

    /**
     * @var string|null
     */
    private $serverIp;

    /**
     * @param string|null $scheme
     * @param string|null $host
     * @param string|null $port
     * @param string|null $path
     * @param string|null $user
     * @param string|null $password
     * @param string|null $appName
     * @param string|null $serverName
     * @param string|null $serverIp
     */
    public function __construct(?string $scheme, ?string $host, ?string $port, ?string $path, ?string $user, ?string $password, ?string $appName, ?string $serverName, ?string $serverIp)
    {
        if (!in_array($scheme, [self::HTTP, self::HTTPS], true)) {
            throw new \InvalidArgumentException("Unsupported scheme '$scheme'.");
        }

        if (!$host) {
            throw new \InvalidArgumentException("Host is required.");
        }

        if ((!$user && $password) || ($user && !$password)) {
            throw new \InvalidArgumentException("Password is available only in combination with user.");
        }

        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->user = $user;
        $this->password = $password;
        $this->appName = $appName;
        $this->serverName = $serverName;
        $this->serverIp = $serverIp;
    }

    /**
     * @param string $dsn
     * @return static
     */
    public static function fromDsn(string $dsn): self
    {
        $parts = parse_url($dsn);
        $query = [];

        parse_str($parts['query'] ?? '', $query);

        return new self(
            $parts['scheme'] ?? null,
            $parts['host'] ?? null,
            $parts['port'] ?? null,
            $parts['path'] ?? null,
            $parts['user'] ?? null,
            $parts['pass'] ?? null,
            $query['appName'] ?? null,
            $query['serverName'] ?? null,
            $query['serverIp'] ?? null
        );
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        $url = $this->scheme.'://'.$this->host;

        if ($this->port) {
            $url .= ':'.$this->port;
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path ?? self::DEFAULT_PATH;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->getBaseUrl().$this->getPath();
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return string|null
     */
    public function getBasicAuth(): ?string
    {
        if ($this->user && $this->password) {
            return $this->user.':'.$this->password;
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getAppName(): ?string
    {
        return $this->appName;
    }

    /**
     * @return string|null
     */
    public function getServerName(): ?string
    {
        return $this->serverName;
    }

    /**
     * @return string|null
     */
    public function getServerIp(): ?string
    {
        return $this->serverIp;
    }
}
