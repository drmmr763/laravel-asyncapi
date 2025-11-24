# Changelog

All notable changes to `laravel-asyncapi` will be documented in this file.

## v1.0.1 - AsyncAPI 3.0.0 Spec Compliance - 2025-11-24

### Laravel AsyncAPI v1.0.1 - AsyncAPI 3.0.0 Spec Compliance Fixes

This patch release fixes critical AsyncAPI 3.0.0 specification compliance issues that were causing validation errors in generated specifications.

#### 🐛 Bug Fixes

##### AsyncAPI 3.0.0 Specification Compliance (#3)

Fixed multiple issues that prevented generated AsyncAPI specifications from validating against the AsyncAPI 3.0.0 schema:

###### Reference Objects

- **Fixed**: Reference objects now correctly serialize to `{ "$ref": "..." }` instead of `{ "ref": "..." }`
- **Impact**: All `$ref` references in the generated specification now conform to JSON Schema and AsyncAPI standards
- **Example**: `{ "$ref": "#/components/messages/UserCreated" }` ✅

###### Messages Wrapper Objects

- **Fixed**: Messages objects no longer create double nesting in the output
- **Impact**: Channel messages are now properly structured
- **Before**: `messages: { messages: { userCreated: {...} } }` ❌
- **After**: `messages: { userCreated: {...} }` ✅

###### Parameters Wrapper Objects

- **Fixed**: Parameters objects are now properly unwrapped (similar to Messages)
- **Impact**: Channel parameters are correctly structured without double nesting
- **Example**: `parameters: { userId: {...} }` ✅

###### Extension Properties

- **Fixed**: Extension properties (x) are now properly serialized with `x-` prefix
- **Impact**: Custom extension properties are now spec-compliant
- **Example**: `x: { 'internal-id': '123' }` → `{ "x-internal-id": "123" }` ✅
- **Note**: 27+ AsyncAPI attribute classes support extension properties

##### Scanner Improvements

- **Fixed**: Scanner now correctly handles `abstract`, `final`, and `readonly` class modifiers
- **Fixed**: Scanner regex now properly matches class declarations even when "class" keyword appears in comments or strings
- **Added**: Comprehensive tests and documentation for scanner regex fixes

#### ✅ Testing

- **138 tests passing** (265 assertions)
- Added 5 new tests for spec compliance fixes:
  - Extension properties serialization
  - Messages wrapper unwrapping
  - Parameters wrapper unwrapping
  - Reference object serialization
  
- All existing tests continue to pass

#### 📝 Changes Since v1.0.0

- Fix AsyncAPI 3.0 spec compliance issues (#3)
- Add support for abstract, final, and readonly class modifiers in scanner
- Add comprehensive tests for scanner regex bug fix
- Add documentation for scanner regex bug fix
- Add tests for extension properties and wrapper objects

#### 🔄 Upgrade Guide

This is a **patch release** with no breaking changes. Simply update your `composer.json`:

```bash
composer update drmmr763/laravel-asyncapi

```
Your existing code will continue to work, but the generated AsyncAPI specifications will now be fully compliant with AsyncAPI 3.0.0.

#### 📦 Installation

```bash
composer require drmmr763/laravel-asyncapi:^1.0.1

```
#### 🙏 Credits

Thanks to everyone who reported validation issues and helped identify these spec compliance problems!

#### 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## v1.0.0 - Initial Stable Release - 2025-11-12

### Laravel AsyncAPI v1.0.0 - Initial Stable Release

We're excited to announce the first stable release of Laravel AsyncAPI! This package provides seamless integration between Laravel and AsyncAPI 3.0.0 specification using PHP 8.3+ attributes.

#### 🎉 Features

##### Core Functionality

- **AsyncAPI 3.0.0 Support**: Full support for the latest AsyncAPI specification
- **PHP 8.3+ Attributes**: Modern, type-safe API definitions using PHP attributes
- **Annotation Scanner**: Automatically discover AsyncAPI attributes across your codebase
- **Specification Builder**: Generate complete AsyncAPI specifications from your annotations
- **Multiple Export Formats**: Export to JSON and YAML formats

##### Laravel Integration

- **Artisan Commands**:
  
  - `asyncapi:generate` - Generate AsyncAPI specification from annotations
  - `asyncapi:export` - Export specification to file
  - `asyncapi:validate` - Validate AsyncAPI annotations
  - `asyncapi:list` - List all AsyncAPI annotations
  
- **Facade Support**: Easy access via `AsyncApi` facade
  
- **Service Provider**: Automatic registration and configuration
  
- **Configuration File**: Comprehensive configuration options
  

##### Quality & Compatibility

- **121 Tests**: Comprehensive test coverage with Pest
- **PHPStan Level 9**: Maximum static analysis strictness
- **Cross-Platform**: Tested on Linux, macOS, and Windows
- **Laravel 11.x & 12.x**: Support for latest Laravel versions
- **PHP 8.3 & 8.4**: Support for modern PHP versions

#### 📦 Installation

```bash
composer require drmmr763/laravel-asyncapi


```
#### 📚 Documentation

Full documentation is available in the [README](https://github.com/drmmr763/laravel-asyncapi#readme).

#### 🔧 Examples

The package includes comprehensive examples:

- **LaravelBroadcastExample.php** - Laravel broadcast events with AsyncAPI
- **ReusableComponentsExample.php** - Using refs and reusable components
- **ExampleAsyncApiSpec.php** - Complete AsyncAPI specification
- **LaravelControllerExample.php** - Practical Laravel integration

#### 🙏 Credits

This package integrates with [php-asyncapi-annotations](https://github.com/drmmr763/php-asyncapi-annotations) for core AsyncAPI attribute support.

#### 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

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
  
