<?php

use Drmmr763\AsyncApi\AsyncApi;
use Drmmr763\AsyncApi\Facades\AsyncApi as AsyncApiFacade;

describe('Full Integration', function () {
    it('can scan, build, and export complete workflow', function () {
        // Scan for annotations
        $annotations = AsyncApiFacade::scan();
        expect($annotations)->toBeArray();

        // Build specification
        $spec = AsyncApiFacade::build();
        expect($spec)->toBeArray()
            ->and($spec)->toHaveKey('asyncapi');

        // Export to JSON
        $json = AsyncApiFacade::toJson();
        expect($json)->toBeString();
        $decoded = json_decode($json, true);
        expect($decoded)->toBeArray();

        // Export to YAML
        $yaml = AsyncApiFacade::toYaml();
        expect($yaml)->toBeString()
            ->and($yaml)->toContain('asyncapi:');

        // Export to file
        $tempFile = sys_get_temp_dir() . '/integration_test_' . uniqid() . '.yaml';
        AsyncApiFacade::exportToFile($tempFile, 'yaml');
        expect(file_exists($tempFile))->toBeTrue();

        unlink($tempFile);
    });

    it('generates valid AsyncAPI 3.0.0 specification', function () {
        $spec = AsyncApiFacade::build();

        expect($spec)->toHaveKey('asyncapi')
            ->and($spec['asyncapi'])->toBe('3.0.0')
            ->and($spec)->toHaveKey('info');
    });

    it('can handle fixtures correctly', function () {
        $scanner = app(\Drmmr763\AsyncApi\AnnotationScanner::class);
        $annotations = $scanner->scan();

        // Should find our test fixtures
        expect($annotations)->toBeArray();
        expect($annotations)->toHaveKey(\Drmmr763\AsyncApi\Tests\Fixtures\TestAsyncApiSpec::class);
    });

    it('builds specification from fixtures', function () {
        $spec = AsyncApiFacade::build();

        expect($spec)->toBeArray()
            ->and($spec)->toHaveKey('info')
            ->and($spec['info'])->toHaveKey('title')
            ->and($spec['info']['title'])->toBe('Test API');
    });

    it('includes all specification sections from fixtures', function () {
        $spec = AsyncApiFacade::build();

        expect($spec)->toHaveKey('asyncapi')
            ->and($spec)->toHaveKey('info')
            ->and($spec)->toHaveKey('servers')
            ->and($spec)->toHaveKey('channels')
            ->and($spec)->toHaveKey('operations')
            ->and($spec)->toHaveKey('components');
    });

    it('preserves data integrity through export cycle', function () {
        $spec = AsyncApiFacade::build();

        // Export to JSON and parse back
        $json = AsyncApiFacade::toJson();
        $fromJson = json_decode($json, true);

        expect($fromJson['asyncapi'])->toBe($spec['asyncapi'])
            ->and($fromJson['info']['title'])->toBe($spec['info']['title']);
    });

    it('can export to multiple formats', function () {
        $jsonFile = sys_get_temp_dir() . '/test_' . uniqid() . '.json';
        $yamlFile = sys_get_temp_dir() . '/test_' . uniqid() . '.yaml';

        AsyncApiFacade::exportToFile($jsonFile, 'json');
        AsyncApiFacade::exportToFile($yamlFile, 'yaml');

        expect(file_exists($jsonFile))->toBeTrue()
            ->and(file_exists($yamlFile))->toBeTrue();

        // Verify content
        $jsonContent = json_decode(file_get_contents($jsonFile), true);
        expect($jsonContent)->toHaveKey('asyncapi');

        $yamlContent = file_get_contents($yamlFile);
        expect($yamlContent)->toContain('asyncapi:');

        unlink($jsonFile);
        unlink($yamlFile);
    });

    it('maintains singleton pattern across requests', function () {
        $instance1 = app(AsyncApi::class);
        $instance2 = app(AsyncApi::class);
        $instance3 = AsyncApiFacade::getFacadeRoot();

        expect($instance1)->toBe($instance2)
            ->and($instance1)->toBe($instance3);
    });

    it('respects configuration settings', function () {
        expect(config('asyncapi.version'))->toBe('3.0.0')
            ->and(config('asyncapi.default_content_type'))->toBe('application/json')
            ->and(config('asyncapi.default_export_format'))->toBe('yaml')
            ->and(config('asyncapi.cache.enabled'))->toBe(false); // Disabled in tests
    });
});

