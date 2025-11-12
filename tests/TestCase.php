<?php

namespace Drmmr763\AsyncApi\Tests;

use Drmmr763\AsyncApi\AsyncApiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            AsyncApiServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configure AsyncAPI package for testing
        config()->set('asyncapi.scan_paths', [
            __DIR__.'/Fixtures',
        ]);

        config()->set('asyncapi.cache.enabled', false);
    }

    /**
     * Define package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'AsyncApi' => \Drmmr763\AsyncApi\Facades\AsyncApi::class,
        ];
    }
}
