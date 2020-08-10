# Integration Eye Logging Probe

This library is implementation of [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3) sending log messages and exceptions to [Integration Eye](https://integrationeye.com) instance.

## Installation

This library is available in [Packagist](https://packagist.org/), so you can install it using [Composer](https://getcomposer.org/):

```sh
composer require integration-eye/logging-probe
``` 

For usage of this library, you need to provide implementation of `Client` interface.
If you install `symfony/http-client` package, default implementation will be provided.

```sh
composer require symfony/http-client
``` 
 
For more information about custom `Client` implementation see [Client](#client).

## Simple Usage

There is `Logger::fromDsn($dsn)` factory function for simple instantiation of `Logger`.

```php
$logger = Logger::fromDsn('https://username:password@integrationeye.example.com');
```

Logger implements [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3) so you can use is as follows:

```php
$logger->debug('Trying out Integration Eye');

$logger->critical(new RuntimeException('Sometimes something goes wrong.'));

$logger->notice('It is shiny today', [
    'temperature' => '28',
    'date' => new DateTime(),
]);
```

You can pass internal flags like `@principal` into context, and it will be used as metadata.

```php
$logger->info('User just logged in.', [
    '@principal' => 'username',
    'method' => 'password',
]);
```

For more information about providing `@principal`, see [PrincipalProvider](#principalprovider).

## Advanced Usage

Logger is composed of `MessageFactory` instance and `Client` implementation.
 
Here is an example of creating `Logger` manually without `Logger::fromDsn($dsn)` factory method.

```php
$configuration = Configuration::fromDsn('https://username:password@integrationeye.example.com');
$messageFactory = new MessageFactory($configuration);

$client = new DefaultClient();
$logger = new Logger($messageFactory, $client);
```

## Extensions

### Client

`Client` interface consist of single method `sendMessage(Configuration $configuration, array $payload): void` which purpose is to send POST request to configured endpoint. 

Optional default implementation is using `symfony/http-client`, but you can provide your own, if it is not what you need.

Here is a simplified example of existing client for testing purposes.

```php
class EchoClient implements Client
{
    public function sendMessage(Configuration $configuration, array $payload): void
    {
        echo sprintf("Url: %s\n", $configuration->getUrl());
        echo sprintf("Authorization: Basic %s\n", $configuration->getBasicAuth());
        echo sprintf("Payload:\n%s\n", json_encode($payload, JSON_PRETTY_PRINT));
    }
}
```

### PrincipalProvider

To send `@principal` metadata with your logs, you can provide implementation of `PrincipalProvider`. It has single method `getPrincipal(): ?string` which you can override.

Here is an example of custom implementation:

```php
class CustomPrincipalProvider implements PrincipalProvider
{
    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function getPrincipal(): ?string
    {
        if ($this->securityContext->isLoggedIn()) {
            return $this->securityContext->getUsername();
        }

        return null;
    }
}

$messageFactory->setPrincipalProvider(new CustomPrincipalProvider($securityContext));
```

If you want, you can use built-in `DefaultPrincipalProvider` for static resolution of principal.

```php
$messageFactory->setPrincipalProvider(new DefaultPrincipalProvider('username'));
// or just
$messageFactory->setPrincipal('username');
```

### Mapper

You can provide implementations of `Mapper` interface for simplifying your logging. It consists of two methods `supports(string $key, $value): bool` and `map(string $key, $value): array`.

Here is a simplified example of existing mapper for `JsonSerializable` objects:

```php
class JsonMapper implements Mapper
{
    public function supports(string $key, $value): bool
    {
        return $value instanceof \JsonSerializable;
    }

    public function map(string $key, $value): array
    {
        return [$key => json_encode($value)];
    }
}

$messageFactory->addMapper(new JsonMapper());
```

This library provides some built-in mappers:

- `DateTimeMapper` for formatting `DateTime` objects
- `JsonMapper` for serializing `JsonSerializable` objects
- `ExceptionMapper` for serializing `Throwable` objects and providing `@exception`, `@stackTrace` metadata

```php
$messageFactory->addMapper(new DateTimeMapper('d.m.Y H:i:s'));
// or just
$messageFactory->addDateTimeMapper(); // default format 'c'

$messageFactory->addMapper(new JsonMapper());
// or just
$messageFactory->addJsonMapper();

$messageFactory->addMapper(new ExceptionMapper('exception_key'));
// or just
$messageFactory->addExceptionMapper(); // default key 'exception'
``` 

When you use `Logger::fromDsn($dsn)` or `MessageFactory::instance($configuration)`, all built-in mappers will be automatically registered with their default configuration.
