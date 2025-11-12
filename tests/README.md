# Laravel AsyncAPI Tests

This directory contains comprehensive tests for the Laravel AsyncAPI package using Orchestra Testbench and Pest.

## Test Structure

```
tests/
├── Feature/                    # Feature/Integration tests
│   ├── Commands/              # Artisan command tests
│   │   ├── ExportCommandTest.php
│   │   ├── GenerateCommandTest.php
│   │   ├── ListCommandTest.php
│   │   └── ValidateCommandTest.php
│   ├── FacadeTest.php         # Facade functionality tests
│   ├── IntegrationTest.php    # Full integration tests
│   ├── LaravelCompatibilityTest.php  # Laravel compatibility tests
│   └── ServiceProviderTest.php      # Service provider tests
├── Fixtures/                  # Test fixtures
│   ├── TestAsyncApiSpec.php   # Sample AsyncAPI specification
│   └── TestBroadcastEvent.php # Sample broadcast event
├── Unit/                      # Unit tests
│   ├── Exporters/
│   │   ├── JsonExporterTest.php
│   │   └── YamlExporterTest.php
│   ├── AnnotationScannerTest.php
│   ├── AsyncApiTest.php
│   └── SpecificationBuilderTest.php
├── ArchTest.php              # Architecture tests
├── ExampleTest.php           # Basic integration tests
├── Pest.php                  # Pest configuration
├── README.md                 # This file
└── TestCase.php              # Base test case using Orchestra Testbench
```

## Running Tests

### Run All Tests
```bash
composer test
```

### Run Specific Test Suite
```bash
# Unit tests only
vendor/bin/pest tests/Unit

# Feature tests only
vendor/bin/pest tests/Feature

# Specific test file
vendor/bin/pest tests/Unit/AsyncApiTest.php
```

### Run with Coverage
```bash
composer test-coverage
```

### Run Specific Test
```bash
vendor/bin/pest --filter "can scan for annotations"
```

## Test Categories

### Unit Tests
Test individual components in isolation:
- **AnnotationScannerTest**: Tests annotation scanning functionality
- **SpecificationBuilderTest**: Tests specification building logic
- **AsyncApiTest**: Tests main AsyncApi class methods
- **JsonExporterTest**: Tests JSON export functionality
- **YamlExporterTest**: Tests YAML export functionality

### Feature Tests
Test Laravel integration and features:
- **ServiceProviderTest**: Tests service provider registration
- **FacadeTest**: Tests facade functionality
- **IntegrationTest**: Tests complete workflows
- **LaravelCompatibilityTest**: Tests Laravel framework compatibility
- **Command Tests**: Tests all Artisan commands

### Architecture Tests
- **ArchTest**: Ensures code quality and architecture rules

## Orchestra Testbench

This package uses [Orchestra Testbench](https://github.com/orchestral/testbench) to test Laravel package compatibility without requiring a full Laravel installation.

### What Orchestra Testbench Provides

1. **Laravel Application Instance**: Full Laravel application for testing
2. **Service Provider Testing**: Test package service providers
3. **Artisan Command Testing**: Test custom Artisan commands
4. **Facade Testing**: Test package facades
5. **Configuration Testing**: Test package configuration
6. **Database Testing**: SQLite in-memory database for tests
7. **Multiple Laravel Version Support**: Test against Laravel 11.x and 12.x

### TestCase Configuration

The `TestCase.php` file configures:
- Package service providers
- Package aliases/facades
- Test environment setup
- Database configuration
- Package-specific configuration

## Writing Tests

### Basic Test Structure

```php
<?php

use Drmmr763\AsyncApi\AsyncApi;

describe('Feature Name', function () {
    it('does something', function () {
        $asyncApi = app(AsyncApi::class);
        
        expect($asyncApi)->toBeInstanceOf(AsyncApi::class);
    });
});
```

### Testing Artisan Commands

```php
it('can run command', function () {
    $this->artisan('asyncapi:generate')
        ->expectsOutput('AsyncAPI Specification Generated')
        ->assertSuccessful();
});
```

### Testing Facades

```php
use Drmmr763\AsyncApi\Facades\AsyncApi;

it('can use facade', function () {
    $spec = AsyncApi::build();
    
    expect($spec)->toBeArray();
});
```

### Testing with Fixtures

```php
it('scans test fixtures', function () {
    $scanner = new AnnotationScanner([__DIR__ . '/Fixtures']);
    $annotations = $scanner->scan();
    
    expect($annotations)->not->toBeEmpty();
});
```

## Test Fixtures

### TestAsyncApiSpec.php
A complete AsyncAPI 3.0.0 specification with:
- Info section
- Servers
- Channels
- Operations
- Components

### TestBroadcastEvent.php
A Laravel broadcast event with AsyncAPI annotations demonstrating:
- Message attributes
- Schema definitions
- Laravel broadcasting integration

## Continuous Integration

Tests are automatically run on:
- Pull requests
- Pushes to main branch
- Multiple PHP versions (8.3+)
- Multiple Laravel versions (11.x, 12.x)

## Coverage

Run tests with coverage report:
```bash
composer test-coverage
```

Coverage reports are generated in:
- HTML: `coverage/html/index.html`
- Clover: `coverage/clover.xml`

## Best Practices

1. **Use Descriptive Test Names**: Tests should read like documentation
2. **Test One Thing**: Each test should verify one specific behavior
3. **Use Fixtures**: Reuse test data via fixtures
4. **Clean Up**: Remove temporary files after tests
5. **Test Edge Cases**: Include tests for error conditions
6. **Keep Tests Fast**: Use in-memory database, avoid external dependencies
7. **Test Laravel Integration**: Verify package works with Laravel features

## Debugging Tests

### Run Single Test with Verbose Output
```bash
vendor/bin/pest --filter "test name" -v
```

### Stop on Failure
```bash
vendor/bin/pest --stop-on-failure
```

### Show Test Output
```bash
vendor/bin/pest --display-errors
```

## Common Issues

### Issue: Tests Can't Find Fixtures
**Solution**: Check that `scan_paths` in TestCase includes fixtures directory

### Issue: Commands Not Found
**Solution**: Ensure service provider is registered in `getPackageProviders()`

### Issue: Config Not Loaded
**Solution**: Check `getEnvironmentSetUp()` method in TestCase

### Issue: Singleton Not Working
**Solution**: Verify service provider registers bindings as singletons

## Contributing

When adding new features:
1. Write tests first (TDD)
2. Ensure all tests pass
3. Add tests for edge cases
4. Update this README if adding new test categories
5. Maintain test coverage above 80%

## Resources

- [Pest Documentation](https://pestphp.com)
- [Orchestra Testbench](https://github.com/orchestral/testbench)
- [Laravel Testing](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de)

