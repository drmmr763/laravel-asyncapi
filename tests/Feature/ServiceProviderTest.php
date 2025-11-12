<?php

use Drmmr763\AsyncApi\AnnotationScanner;
use Drmmr763\AsyncApi\AsyncApi;
use Drmmr763\AsyncApi\Commands\ExportCommand;
use Drmmr763\AsyncApi\Commands\GenerateCommand;
use Drmmr763\AsyncApi\Commands\ListCommand;
use Drmmr763\AsyncApi\Commands\ValidateCommand;
use Drmmr763\AsyncApi\Exporters\JsonExporter;
use Drmmr763\AsyncApi\Exporters\YamlExporter;
use Drmmr763\AsyncApi\SpecificationBuilder;

describe('ServiceProvider', function () {
    it('registers AnnotationScanner as singleton', function () {
        $scanner1 = app(AnnotationScanner::class);
        $scanner2 = app(AnnotationScanner::class);

        expect($scanner1)->toBeInstanceOf(AnnotationScanner::class)
            ->and($scanner1)->toBe($scanner2);
    });

    it('registers SpecificationBuilder as singleton', function () {
        $builder1 = app(SpecificationBuilder::class);
        $builder2 = app(SpecificationBuilder::class);

        expect($builder1)->toBeInstanceOf(SpecificationBuilder::class)
            ->and($builder1)->toBe($builder2);
    });

    it('registers AsyncApi as singleton', function () {
        $asyncApi1 = app(AsyncApi::class);
        $asyncApi2 = app(AsyncApi::class);

        expect($asyncApi1)->toBeInstanceOf(AsyncApi::class)
            ->and($asyncApi1)->toBe($asyncApi2);
    });

    it('registers JsonExporter', function () {
        $exporter = app('asyncapi.exporter.json');

        expect($exporter)->toBeInstanceOf(JsonExporter::class);
    });

    it('registers YamlExporter', function () {
        $exporter = app('asyncapi.exporter.yaml');

        expect($exporter)->toBeInstanceOf(YamlExporter::class);
    });

    it('registers GenerateCommand', function () {
        expect(app(GenerateCommand::class))
            ->toBeInstanceOf(GenerateCommand::class);
    });

    it('registers ExportCommand', function () {
        expect(app(ExportCommand::class))
            ->toBeInstanceOf(ExportCommand::class);
    });

    it('registers ValidateCommand', function () {
        expect(app(ValidateCommand::class))
            ->toBeInstanceOf(ValidateCommand::class);
    });

    it('registers ListCommand', function () {
        expect(app(ListCommand::class))
            ->toBeInstanceOf(ListCommand::class);
    });

    it('publishes config file', function () {
        $configPath = config_path('asyncapi.php');

        // The config should be available
        expect(config('asyncapi'))->toBeArray();
    });

    it('loads default configuration', function () {
        expect(config('asyncapi.version'))->toBe('3.0.0')
            ->and(config('asyncapi.default_content_type'))->toBe('application/json')
            ->and(config('asyncapi.scan_paths'))->toBeArray()
            ->and(config('asyncapi.default_export_format'))->toBe('yaml');
    });

    it('configures scan paths from config', function () {
        $scanner = app(AnnotationScanner::class);

        expect($scanner)->toBeInstanceOf(AnnotationScanner::class);
    });
});
