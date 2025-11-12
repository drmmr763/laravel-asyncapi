# Laravel package for AsyncAPI 3.0.0 specification with annotations and code generation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/drmmr763/laravel-asyncapi.svg?style=flat-square)](https://packagist.org/packages/drmmr763/laravel-asyncapi)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/drmmr763/laravel-asyncapi/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/drmmr763/laravel-asyncapi/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/drmmr763/laravel-asyncapi/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/drmmr763/laravel-asyncapi/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/drmmr763/laravel-asyncapi.svg?style=flat-square)](https://packagist.org/packages/drmmr763/laravel-asyncapi)

A Laravel package that integrates [php-asyncapi-annotations](https://github.com/drmmr763/php-asyncapi-annotations) to provide AsyncAPI 3.0.0 specification generation using PHP 8.3+ attributes. This package allows you to define your AsyncAPI specifications directly in your Laravel code using attributes and generate specification files in JSON or YAML format.

## Installation

You can install the package via composer:

```bash
composer require drmmr763/laravel-asyncapi
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="asyncapi-config"
```

This is the contents of the published config file:

```php
return [
    'version' => env('ASYNCAPI_VERSION', '3.0.0'),
    'default_content_type' => env('ASYNCAPI_DEFAULT_CONTENT_TYPE', 'application/json'),
    'scan_paths' => [
        app_path(),
    ],
    'output_path' => base_path('asyncapi'),
    'export_formats' => ['json', 'yaml'],
    'default_export_format' => env('ASYNCAPI_EXPORT_FORMAT', 'yaml'),
    'pretty_print' => env('ASYNCAPI_PRETTY_PRINT', true),
    'cache' => [
        'enabled' => env('ASYNCAPI_CACHE_ENABLED', true),
        'ttl' => env('ASYNCAPI_CACHE_TTL', 3600),
        'key' => 'asyncapi_annotations',
    ],
];
```

## Usage

### Define AsyncAPI Specifications with Attributes

Use PHP 8.3+ attributes to define your AsyncAPI specifications directly in your code:

```php
<?php

namespace App\AsyncApi;

use AsyncApi\Attributes\AsyncApi;
use AsyncApi\Attributes\Info;
use AsyncApi\Attributes\Server;
use AsyncApi\Attributes\Channel;
use AsyncApi\Attributes\Operation;

#[AsyncApi(
    asyncapi: '3.0.0',
    info: new Info(
        title: 'My Application API',
        version: '1.0.0',
        description: 'AsyncAPI specification for my application'
    ),
    servers: [
        'production' => new Server(
            host: 'api.example.com',
            protocol: 'kafka',
            description: 'Production Kafka server'
        )
    ]
)]
class MyAsyncApiSpec
{
}
```

### Available Commands

The package provides several Artisan commands:

#### Generate Specification

Generate and display the AsyncAPI specification:

```bash
php artisan asyncapi:generate
```

Generate with specific format:

```bash
php artisan asyncapi:generate --format=json
php artisan asyncapi:generate --format=yaml
```

Generate and save to file:

```bash
php artisan asyncapi:generate --output=asyncapi.yaml
php artisan asyncapi:generate --output=asyncapi.json --format=json
```

#### Export Specification

Export the specification to a file:

```bash
php artisan asyncapi:export asyncapi.yaml
php artisan asyncapi:export asyncapi.json --format=json
```

#### Validate Specification

Validate your AsyncAPI annotations:

```bash
php artisan asyncapi:validate
```

#### List Annotations

List all AsyncAPI annotations found in your codebase:

```bash
php artisan asyncapi:list
```

Filter by annotation type:

```bash
php artisan asyncapi:list --type=Channel
php artisan asyncapi:list --type=Operation
```

### Using the Facade

You can also use the AsyncApi facade in your code:

```php
use Drmmr763\AsyncApi\Facades\AsyncApi;

// Build the specification
$spec = AsyncApi::build();

// Export to JSON
$json = AsyncApi::toJson();

// Export to YAML
$yaml = AsyncApi::toYaml();

// Export to file
AsyncApi::exportToFile('asyncapi.yaml', 'yaml');
AsyncApi::exportToFile('asyncapi.json', 'json');

// Scan for annotations
$annotations = AsyncApi::scan();
```

### Using Dependency Injection

You can also inject the AsyncApi class directly:

```php
use Drmmr763\AsyncApi\AsyncApi;

class MyController
{
    public function __construct(private AsyncApi $asyncApi)
    {
    }

    public function generateSpec()
    {
        $specification = $this->asyncApi->build();
        return response()->json($specification);
    }
}
```

## Examples

The package includes several comprehensive examples in the `examples/` directory:

### 1. Laravel Broadcast Events

See `examples/LaravelBroadcastExample.php` for examples of annotating Laravel broadcast events:

```php
use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Schema;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

#[Message(
    name: 'UserRegistered',
    title: 'User Registered Event',
    summary: 'Broadcast when a new user registers',
    payload: new Schema(
        type: 'object',
        properties: [
            'event' => new Schema(type: 'string', example: 'user.registered'),
            'data' => new Schema(
                type: 'object',
                properties: [
                    'user' => new Schema(/* user schema */),
                    'ip_address' => new Schema(type: 'string')
                ]
            )
        ]
    )
)]
class UserRegistered implements ShouldBroadcast
{
    // Your broadcast event implementation
}
```

This example includes:
- Public channel broadcasts
- Private channel broadcasts
- Presence channel broadcasts
- Typing indicators and real-time features

### 2. Reusable Components with References

See `examples/ReusableComponentsExample.php` for a complete example of using refs:

```php
// Define reusable schemas
class CommonSchemas
{
    public const USER = '#/components/schemas/User';
    public const ORDER = '#/components/schemas/Order';
    public const PRODUCT = '#/components/schemas/Product';
}

// Reference them in your messages
#[AsyncApi(
    channels: new Channels(
        channels: [
            'orders/created' => new Channel(
                messages: [
                    'orderCreated' => new Message(
                        payload: new Schema(
                            type: 'object',
                            properties: [
                                'order' => new Reference(ref: CommonSchemas::ORDER),
                                'user' => new Reference(ref: CommonSchemas::USER)
                            ]
                        )
                    )
                ]
            )
        ]
    ),
    components: new Components(
        schemas: [
            'User' => new Schema(/* user schema definition */),
            'Order' => new Schema(
                properties: [
                    'items' => new Schema(
                        type: 'array',
                        items: new Reference(ref: CommonSchemas::PRODUCT)
                    )
                ]
            )
        ]
    )
)]
class MyAsyncApiSpec {}
```

Benefits of using refs:
- **DRY Principle**: Define schemas once, reference everywhere
- **Consistency**: Ensures the same schema is used across all messages
- **Maintainability**: Update schema in one place, changes reflect everywhere
- **Composition**: Build complex schemas from simpler reusable components

### 3. Complete E-Commerce Example

See `examples/ExampleAsyncApiSpec.php` for a full specification including:
- Multiple channels (Kafka, WebSocket)
- Server configurations
- Operations (send/receive)
- Security schemes
- Complete component definitions

### 4. Laravel Controller Integration

See `examples/LaravelControllerExample.php` for practical examples of:
- Serving AsyncAPI specs via HTTP endpoints
- Triggering broadcast events documented in AsyncAPI
- Rendering AsyncAPI documentation in your app
- Caching generated specifications
- Creating custom Artisan commands

Example routes:
```php
// Serve AsyncAPI specification
Route::get('/asyncapi.json', [AsyncApiController::class, 'getSpecJson']);
Route::get('/asyncapi.yaml', [AsyncApiController::class, 'getSpecYaml']);

// Render interactive documentation
Route::get('/asyncapi/docs', [AsyncApiController::class, 'renderDocs']);
```

## Advanced Usage

### Organizing Large Specifications

For large applications, organize your AsyncAPI definitions across multiple files:

```php
// app/AsyncApi/Schemas/UserSchemas.php
class UserSchemas
{
    public const USER = '#/components/schemas/User';
    public const USER_PROFILE = '#/components/schemas/UserProfile';
}

// app/AsyncApi/Schemas/OrderSchemas.php
class OrderSchemas
{
    public const ORDER = '#/components/schemas/Order';
    public const ORDER_ITEM = '#/components/schemas/OrderItem';
}

// app/AsyncApi/MainSpec.php
#[AsyncApi(
    // Reference schemas from different classes
    components: new Components(
        schemas: [
            'User' => new Schema(/* ... */),
            'Order' => new Schema(/* ... */)
        ]
    )
)]
class MainSpec {}
```

### Custom Scan Paths

Configure which directories to scan for AsyncAPI annotations:

```php
// config/asyncapi.php
return [
    'scan_paths' => [
        app_path('Events'),
        app_path('AsyncApi'),
        app_path('Broadcasting'),
    ],
];
```

### Caching for Performance

Enable caching to improve performance in production:

```php
// .env
ASYNCAPI_CACHE_ENABLED=true
ASYNCAPI_CACHE_TTL=3600
```

Clear the cache when you update your annotations:

```bash
php artisan cache:forget asyncapi_annotations
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Chad Windnagle](https://github.com/drmmr763)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
