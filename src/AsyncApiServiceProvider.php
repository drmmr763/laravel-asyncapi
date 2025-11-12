<?php

namespace Drmmr763\AsyncApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AsyncApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('asyncapi')
            ->hasConfigFile()
            ->hasCommands([
                Commands\GenerateCommand::class,
                Commands\ExportCommand::class,
                Commands\ValidateCommand::class,
                Commands\ListCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        // Register the annotation scanner
        $this->app->singleton(AnnotationScanner::class, function ($app) {
            return new AnnotationScanner(
                config('asyncapi.scan_paths', [app_path()])
            );
        });

        // Register the spec builder
        $this->app->singleton(SpecificationBuilder::class, function ($app) {
            return new SpecificationBuilder(
                $app->make(AnnotationScanner::class)
            );
        });

        // Register the main AsyncApi class
        $this->app->singleton(AsyncApi::class, function ($app) {
            return new AsyncApi(
                $app->make(AnnotationScanner::class),
                $app->make(SpecificationBuilder::class)
            );
        });

        // Register exporters
        $this->app->singleton('asyncapi.exporter.json', Exporters\JsonExporter::class);
        $this->app->singleton('asyncapi.exporter.yaml', Exporters\YamlExporter::class);
    }
}
