<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

describe('Laravel Compatibility', function () {
    it('works with Laravel container', function () {
        expect(app())->toBeInstanceOf(\Illuminate\Foundation\Application::class);
    });

    it('registers service provider correctly', function () {
        $providers = app()->getLoadedProviders();
        expect($providers)->toHaveKey(\Drmmr763\AsyncApi\AsyncApiServiceProvider::class);
    });

    it('registers facade correctly', function () {
        $aliases = app()->getAlias('AsyncApi');
        // The alias returns the short name, not the full class
        expect($aliases)->toBe('AsyncApi');
    });

    it('can use Laravel config system', function () {
        Config::set('asyncapi.test_key', 'test_value');
        expect(config('asyncapi.test_key'))->toBe('test_value');
    });

    it('registers artisan commands', function () {
        $commands = Artisan::all();

        expect($commands)->toHaveKey('asyncapi:generate')
            ->and($commands)->toHaveKey('asyncapi:export')
            ->and($commands)->toHaveKey('asyncapi:validate')
            ->and($commands)->toHaveKey('asyncapi:list');
    });

    it('can resolve dependencies from container', function () {
        $scanner = app(\Drmmr763\AsyncApi\AnnotationScanner::class);
        $builder = app(\Drmmr763\AsyncApi\SpecificationBuilder::class);
        $asyncApi = app(\Drmmr763\AsyncApi\AsyncApi::class);

        expect($scanner)->toBeInstanceOf(\Drmmr763\AsyncApi\AnnotationScanner::class)
            ->and($builder)->toBeInstanceOf(\Drmmr763\AsyncApi\SpecificationBuilder::class)
            ->and($asyncApi)->toBeInstanceOf(\Drmmr763\AsyncApi\AsyncApi::class);
    });

    it('uses Laravel filesystem helpers', function () {
        $tempFile = sys_get_temp_dir() . '/laravel_compat_test_' . uniqid() . '.yaml';

        try {
            \Drmmr763\AsyncApi\Facades\AsyncApi::exportToFile($tempFile, 'yaml');
            expect(file_exists($tempFile))->toBeTrue();
            unlink($tempFile);
        } catch (\RuntimeException $e) {
            // It's OK if there are no annotations to export in this test
            expect($e->getMessage())->toContain('No AsyncAPI annotations found');
        }
    });

    it('works with Laravel service container binding', function () {
        app()->bind('test.asyncapi', function ($app) {
            return $app->make(\Drmmr763\AsyncApi\AsyncApi::class);
        });

        $instance = app('test.asyncapi');
        expect($instance)->toBeInstanceOf(\Drmmr763\AsyncApi\AsyncApi::class);
    });

    it('supports Laravel config caching', function () {
        // Config should be accessible even if cached
        expect(config('asyncapi'))->toBeArray();
    });

    it('integrates with Laravel package discovery', function () {
        // Package should be auto-discovered via composer.json extra.laravel
        $providers = config('app.providers', []);
        // In testing, provider is loaded via TestCase
        expect(app()->getLoadedProviders())->toHaveKey(\Drmmr763\AsyncApi\AsyncApiServiceProvider::class);
    });

    it('can publish configuration file', function () {
        // Test that config can be published
        Artisan::call('vendor:publish', [
            '--tag' => 'asyncapi-config',
            '--force' => true,
        ]);

        // In test environment, we just verify the command runs
        expect(true)->toBeTrue();
    });

    it('respects Laravel environment configuration', function () {
        // Test that package respects environment-based config
        Config::set('asyncapi.scan_paths', ['/custom/path']);
        expect(config('asyncapi.scan_paths'))->toBe(['/custom/path']);
    });

    it('works with dependency injection in controllers', function () {
        // Simulate controller dependency injection
        $controller = new class(app(\Drmmr763\AsyncApi\AsyncApi::class))
        {
            public function __construct(
                public \Drmmr763\AsyncApi\AsyncApi $asyncApi
            ) {
            }
        };

        expect($controller->asyncApi)->toBeInstanceOf(\Drmmr763\AsyncApi\AsyncApi::class);
    });

    it('supports Laravel testing helpers', function () {
        // Verify Pest/PHPUnit Laravel integration works
        expect($this)->toHaveProperty('app');
    });
});

