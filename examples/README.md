# Laravel AsyncAPI Examples

This directory contains comprehensive examples demonstrating how to use the Laravel AsyncAPI package.

## 📚 Examples Overview

### 1. **ExampleAsyncApiSpec.php** - Complete AsyncAPI Specification
A full-featured AsyncAPI 3.0.0 specification demonstrating:
- ✅ **Reusable Schema Components** - Define schemas once, reference everywhere
- ✅ **Multiple Channels** - Kafka and WebSocket channels
- ✅ **Laravel Broadcast Events** - Integration with Laravel's broadcasting system
- ✅ **Using References** - How to reference schemas from other classes
- ✅ **Server Configurations** - Multiple server definitions with security
- ✅ **Operations** - Send and receive operations for each channel
- ✅ **Security Schemes** - SASL/SCRAM authentication

**Key Features:**
```php
// Define reusable schemas
class UserSchemas {
    public const USER_SCHEMA = '#/components/schemas/User';
    public const NOTIFICATION_SCHEMA = '#/components/schemas/Notification';
}

// Reference them in messages
payload: new Reference(ref: UserSchemas::USER_SCHEMA)
```

### 2. **LaravelBroadcastExample.php** - Laravel Broadcast Events
Real-world Laravel broadcast event examples with AsyncAPI annotations:
- ✅ **Public Channel** - `UserRegistered` event on public channel
- ✅ **Private Channel** - `UserProfileUpdated` event on user-specific private channel
- ✅ **Presence Channel** - `UserTyping` event for real-time collaboration

**Perfect for:**
- Documenting your Laravel broadcast events
- Real-time features (chat, notifications, live updates)
- WebSocket API documentation

**Example:**
```php
#[Message(
    name: 'UserRegistered',
    title: 'User Registered Event',
    payload: new Schema(/* ... */)
)]
class UserRegistered implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [new Channel('user.registered')];
    }
}
```

### 3. **ReusableComponentsExample.php** - E-Commerce with Refs
Advanced example showing best practices for large specifications:
- ✅ **Component Organization** - Centralized schema definitions
- ✅ **Schema Composition** - Complex schemas built from simpler ones
- ✅ **Nested References** - Orders reference Products, Products reference Categories
- ✅ **Consistent Patterns** - Reusable timestamp, error, and pagination schemas

**Best Practices Demonstrated:**
```php
// Centralized schema references
class CommonSchemas {
    public const USER = '#/components/schemas/User';
    public const ORDER = '#/components/schemas/Order';
    public const PRODUCT = '#/components/schemas/Product';
}

// Compose complex schemas
'Order' => new Schema(
    properties: [
        'user' => new Reference(ref: CommonSchemas::USER),
        'items' => new Schema(
            type: 'array',
            items: new Reference(ref: CommonSchemas::PRODUCT)
        )
    ]
)
```

### 4. **LaravelControllerExample.php** - Practical Integration
Production-ready examples of integrating AsyncAPI into your Laravel application:
- ✅ **HTTP Endpoints** - Serve AsyncAPI specs via API routes
- ✅ **Multiple Formats** - JSON and YAML endpoints
- ✅ **Interactive Docs** - Render AsyncAPI documentation in your app
- ✅ **Event Triggering** - How to trigger documented broadcast events
- ✅ **Caching** - Performance optimization strategies
- ✅ **Custom Commands** - Artisan commands for spec generation

**Includes:**
```php
// Serve AsyncAPI specification
Route::get('/asyncapi.json', [AsyncApiController::class, 'getSpecJson']);
Route::get('/asyncapi.yaml', [AsyncApiController::class, 'getSpecYaml']);
Route::get('/asyncapi/docs', [AsyncApiController::class, 'renderDocs']);

// Trigger documented events
event(new UserRegistered($user, $request->ip()));
```

### 5. **QUICK_REFERENCE.md** - Cheat Sheet
Quick reference guide with code snippets for:
- Common patterns and use cases
- All Artisan commands with examples
- Configuration options
- Schema validation patterns
- Tips and best practices

## 🚀 Getting Started

### Step 1: Choose Your Starting Point

**New to AsyncAPI?**
→ Start with `ExampleAsyncApiSpec.php` to understand the basics

**Working with Laravel Events?**
→ Check out `LaravelBroadcastExample.php`

**Building a Large API?**
→ Study `ReusableComponentsExample.php` for organization patterns

**Ready to Integrate?**
→ Use `LaravelControllerExample.php` as a template

### Step 2: Copy and Customize

```bash
# Copy an example to your app
cp examples/LaravelBroadcastExample.php app/Events/

# Or create your own AsyncAPI spec
cp examples/ExampleAsyncApiSpec.php app/AsyncApi/MyApiSpec.php
```

### Step 3: Generate Your Specification

```bash
# Generate and view
php artisan asyncapi:generate

# Export to file
php artisan asyncapi:export asyncapi.yaml

# Validate
php artisan asyncapi:validate
```

## 📖 Learning Path

### Beginner
1. Read `QUICK_REFERENCE.md` for basic concepts
2. Study `ExampleAsyncApiSpec.php` structure
3. Try generating your first spec with `asyncapi:generate`

### Intermediate
1. Implement `LaravelBroadcastExample.php` patterns in your events
2. Learn to use references from `ReusableComponentsExample.php`
3. Set up HTTP endpoints from `LaravelControllerExample.php`

### Advanced
1. Organize large specs across multiple files
2. Implement custom exporters
3. Create custom validation rules
4. Integrate with CI/CD pipelines

## 🎯 Common Use Cases

### Use Case 1: Document Existing Laravel Events
```php
// Add AsyncAPI annotations to your existing events
#[Message(name: 'OrderPlaced', payload: new Schema(/* ... */))]
class OrderPlaced implements ShouldBroadcast { /* ... */ }
```

### Use Case 2: API-First Development
```php
// Define your AsyncAPI spec first
#[AsyncApi(/* complete specification */)]
class MyApiSpec {}

// Then implement events matching the spec
```

### Use Case 3: Multi-Protocol APIs
```php
// Document Kafka, WebSocket, MQTT in one spec
servers: new Servers([
    'kafka' => new Server(protocol: 'kafka'),
    'websocket' => new Server(protocol: 'ws'),
    'mqtt' => new Server(protocol: 'mqtt')
])
```

### Use Case 4: Microservices Documentation
```php
// Each service has its own AsyncAPI spec
// Reference shared schemas across services
payload: new Reference(ref: 'https://api.example.com/schemas/User')
```

## 🔧 Customization Tips

### Tip 1: Organize by Domain
```
app/AsyncApi/
├── Schemas/
│   ├── UserSchemas.php
│   ├── OrderSchemas.php
│   └── ProductSchemas.php
├── Events/
│   ├── UserEvents.php
│   └── OrderEvents.php
└── MainSpec.php
```

### Tip 2: Environment-Specific Servers
```php
servers: new Servers([
    'production' => new Server(
        host: env('KAFKA_PROD_HOST'),
        protocol: 'kafka'
    ),
    'staging' => new Server(
        host: env('KAFKA_STAGING_HOST'),
        protocol: 'kafka'
    )
])
```

### Tip 3: Versioning Strategy
```php
info: new Info(
    title: 'My API',
    version: '2.1.0',  // Semantic versioning
    description: 'Breaking changes in v2.0.0'
)
```

## 📚 Additional Resources

- **AsyncAPI Specification**: https://www.asyncapi.com/docs/reference/specification/v3.0.0
- **Laravel Broadcasting**: https://laravel.com/docs/broadcasting
- **Package Repository**: https://github.com/drmmr763/laravel-asyncapi
- **Base Annotations**: https://github.com/drmmr763/php-asyncapi-annotations

## 💡 Pro Tips

1. **Use References Liberally** - Define once, use everywhere
2. **Add Examples** - Include example values in your schemas
3. **Document Everything** - Add descriptions to all properties
4. **Validate Early** - Run `asyncapi:validate` in development
5. **Cache in Production** - Enable caching for performance
6. **Version Your API** - Use semantic versioning
7. **Test Your Events** - Ensure events match their AsyncAPI definitions
8. **Keep It DRY** - Use components for repeated patterns
9. **Follow Conventions** - Consistent naming makes specs easier to read
10. **Automate Generation** - Add spec generation to your CI/CD pipeline

## 🤝 Contributing

Found a better pattern? Have a useful example? Contributions are welcome!

## 📝 License

These examples are provided as part of the Laravel AsyncAPI package and are licensed under the MIT License.

