# AsyncAPI Laravel Package - Quick Reference

## Table of Contents
1. [Basic Usage](#basic-usage)
2. [Laravel Broadcast Events](#laravel-broadcast-events)
3. [Using References](#using-references)
4. [Artisan Commands](#artisan-commands)
5. [Common Patterns](#common-patterns)

## Basic Usage

### Minimal AsyncAPI Specification

```php
use AsyncApi\Attributes\AsyncApi;
use AsyncApi\Attributes\Info;

#[AsyncApi(
    asyncapi: '3.0.0',
    info: new Info(
        title: 'My API',
        version: '1.0.0'
    )
)]
class MySpec {}
```

### Adding a Channel

```php
use AsyncApi\Attributes\Channel;
use AsyncApi\Attributes\Channels;
use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Schema;

#[AsyncApi(
    asyncapi: '3.0.0',
    info: new Info(title: 'My API', version: '1.0.0'),
    channels: new Channels(
        channels: [
            'user/events' => new Channel(
                address: 'user.events',
                messages: [
                    'userEvent' => new Message(
                        name: 'UserEvent',
                        payload: new Schema(
                            type: 'object',
                            properties: [
                                'user_id' => new Schema(type: 'integer'),
                                'event_type' => new Schema(type: 'string')
                            ]
                        )
                    )
                ]
            )
        ]
    )
)]
class MySpec {}
```

## Laravel Broadcast Events

### Public Channel Event

```php
use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Schema;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

#[Message(
    name: 'PublicEvent',
    title: 'Public Event',
    payload: new Schema(
        type: 'object',
        properties: [
            'event' => new Schema(type: 'string'),
            'data' => new Schema(type: 'object')
        ]
    )
)]
class PublicEvent implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [new Channel('public.events')];
    }
}
```

### Private Channel Event

```php
#[Message(
    name: 'PrivateUserEvent',
    title: 'Private User Event',
    payload: new Schema(/* ... */)
)]
class PrivateUserEvent implements ShouldBroadcast
{
    public function __construct(public User $user) {}
    
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->user->id)];
    }
}
```

### Presence Channel Event

```php
#[Message(
    name: 'ChatMessage',
    title: 'Chat Message',
    payload: new Schema(/* ... */)
)]
class ChatMessage implements ShouldBroadcast
{
    public function __construct(
        public User $user,
        public string $roomId,
        public string $message
    ) {}
    
    public function broadcastOn(): array
    {
        return [new PresenceChannel('chat.' . $this->roomId)];
    }
}
```

## Using References

### Define Reusable Schemas

```php
class CommonSchemas
{
    public const USER = '#/components/schemas/User';
    public const TIMESTAMP = '#/components/schemas/Timestamp';
    public const ERROR = '#/components/schemas/Error';
}
```

### Reference in Components

```php
use AsyncApi\Attributes\Components;
use AsyncApi\Attributes\Schema;

#[AsyncApi(
    // ...
    components: new Components(
        schemas: [
            'User' => new Schema(
                type: 'object',
                properties: [
                    'id' => new Schema(type: 'integer'),
                    'name' => new Schema(type: 'string'),
                    'email' => new Schema(type: 'string', format: 'email')
                ]
            ),
            'Timestamp' => new Schema(
                type: 'string',
                format: 'date-time'
            )
        ]
    )
)]
class MySpec {}
```

### Reference in Messages

```php
use AsyncApi\Attributes\Reference;

new Message(
    name: 'UserCreated',
    payload: new Schema(
        type: 'object',
        properties: [
            'user' => new Reference(ref: CommonSchemas::USER),
            'created_at' => new Reference(ref: CommonSchemas::TIMESTAMP)
        ]
    )
)
```

### Nested References

```php
// Order references Product
'Order' => new Schema(
    type: 'object',
    properties: [
        'id' => new Schema(type: 'integer'),
        'items' => new Schema(
            type: 'array',
            items: new Reference(ref: CommonSchemas::PRODUCT)
        ),
        'user' => new Reference(ref: CommonSchemas::USER),
        'created_at' => new Reference(ref: CommonSchemas::TIMESTAMP)
    ]
)
```

## Artisan Commands

### Generate Specification

```bash
# Display specification
php artisan asyncapi:generate

# Generate as JSON
php artisan asyncapi:generate --format=json

# Generate and save to file
php artisan asyncapi:generate --output=asyncapi.yaml

# Pretty print
php artisan asyncapi:generate --pretty
```

### Export Specification

```bash
# Export to YAML (default)
php artisan asyncapi:export asyncapi.yaml

# Export to JSON
php artisan asyncapi:export asyncapi.json --format=json

# Auto-detect format from extension
php artisan asyncapi:export spec.yaml  # Uses YAML
php artisan asyncapi:export spec.json  # Uses JSON
```

### Validate Specification

```bash
# Validate all annotations
php artisan asyncapi:validate
```

### List Annotations

```bash
# List all annotations
php artisan asyncapi:list

# Filter by type
php artisan asyncapi:list --type=Channel
php artisan asyncapi:list --type=Message
php artisan asyncapi:list --type=Operation
```

## Common Patterns

### Server Configuration

```php
use AsyncApi\Attributes\Server;
use AsyncApi\Attributes\Servers;

servers: new Servers(
    servers: [
        'production' => new Server(
            host: 'kafka.example.com:9092',
            protocol: 'kafka',
            description: 'Production Kafka cluster'
        ),
        'websocket' => new Server(
            host: 'ws.example.com',
            protocol: 'ws',
            description: 'WebSocket server'
        )
    ]
)
```

### Operations

```php
use AsyncApi\Attributes\Operation;
use AsyncApi\Attributes\Operations;

operations: new Operations(
    operations: [
        'sendMessage' => new Operation(
            action: 'send',
            channel: 'messages/send',
            title: 'Send Message',
            messages: ['messageCreated']
        ),
        'receiveMessage' => new Operation(
            action: 'receive',
            channel: 'messages/receive',
            title: 'Receive Message',
            messages: ['messageReceived']
        )
    ]
)
```

### Schema Validation

```php
new Schema(
    type: 'object',
    properties: [
        'email' => new Schema(
            type: 'string',
            format: 'email',
            pattern: '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
        ),
        'age' => new Schema(
            type: 'integer',
            minimum: 0,
            maximum: 150
        ),
        'status' => new Schema(
            type: 'string',
            enum: ['active', 'inactive', 'pending']
        ),
        'tags' => new Schema(
            type: 'array',
            items: new Schema(type: 'string'),
            minItems: 1,
            maxItems: 10
        )
    ],
    required: ['email', 'age']
)
```

### Using the Facade

```php
use Drmmr763\AsyncApi\Facades\AsyncApi;

// Build specification
$spec = AsyncApi::build();

// Export to JSON
$json = AsyncApi::toJson();

// Export to YAML
$yaml = AsyncApi::toYaml();

// Export to file
AsyncApi::exportToFile('asyncapi.yaml', 'yaml');

// Scan for annotations
$annotations = AsyncApi::scan();
```

### Dependency Injection

```php
use Drmmr763\AsyncApi\AsyncApi;

class MyService
{
    public function __construct(private AsyncApi $asyncApi) {}
    
    public function generateSpec(): array
    {
        return $this->asyncApi->build();
    }
}
```

## Configuration

### Publish Config

```bash
php artisan vendor:publish --tag="asyncapi-config"
```

### Key Configuration Options

```php
// config/asyncapi.php
return [
    // AsyncAPI version
    'version' => '3.0.0',
    
    // Paths to scan for annotations
    'scan_paths' => [
        app_path(),
        app_path('Events'),
        app_path('AsyncApi'),
    ],
    
    // Default export format
    'default_export_format' => 'yaml',
    
    // Enable caching
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
];
```

## Tips and Best Practices

1. **Use References**: Define schemas once in components, reference everywhere
2. **Organize by Domain**: Group related schemas in separate classes
3. **Document Everything**: Add descriptions to all schemas and properties
4. **Version Your API**: Include version in info and update when making breaking changes
5. **Cache in Production**: Enable caching for better performance
6. **Validate Regularly**: Run `asyncapi:validate` in CI/CD pipeline
7. **Keep Examples**: Add example values to schemas for better documentation
8. **Use Enums**: Define allowed values using enum for better validation
9. **Follow Naming Conventions**: Use consistent naming for channels and operations
10. **Test Your Events**: Ensure broadcast events match their AsyncAPI definitions

