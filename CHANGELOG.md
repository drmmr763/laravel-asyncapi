# Changelog

All notable changes to `laravel-asyncapi` will be documented in this file.

## 1.0.0 - Initial Release

### Added
- Initial release of Laravel AsyncAPI package
- Integration with php-asyncapi-annotations library
- Support for AsyncAPI 3.0.0 specification
- PHP 8.3+ attribute-based API definitions
- Annotation scanner for discovering AsyncAPI attributes in codebase
- Specification builder for generating AsyncAPI specifications
- JSON and YAML exporters
- Laravel Artisan commands:
  - `asyncapi:generate` - Generate AsyncAPI specification
  - `asyncapi:export` - Export specification to file
  - `asyncapi:validate` - Validate AsyncAPI annotations
  - `asyncapi:list` - List all AsyncAPI annotations
- AsyncApi facade for easy access
- Comprehensive configuration file
- Service provider with automatic registration
- Full documentation in README

### Examples
- **LaravelBroadcastExample.php** - Complete examples of Laravel broadcast events with AsyncAPI annotations
  - Public channel broadcasts
  - Private channel broadcasts
  - Presence channel broadcasts
  - Real-time typing indicators
- **ReusableComponentsExample.php** - Demonstrates using refs and reusable components
  - Schema references across multiple messages
  - Component composition
  - E-commerce event specification
- **ExampleAsyncApiSpec.php** - Full AsyncAPI specification example
  - Multiple channels (Kafka, WebSocket)
  - User notifications system
  - Server configurations and security
- **LaravelControllerExample.php** - Practical Laravel integration examples
  - Serving AsyncAPI specs via HTTP endpoints
  - Triggering documented broadcast events
  - Rendering interactive documentation
  - Caching strategies
  - Custom Artisan commands
