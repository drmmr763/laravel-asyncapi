<?php

use Drmmr763\AsyncApi\AnnotationScanner;
use Drmmr763\AsyncApi\AsyncApi;
use Drmmr763\AsyncApi\SpecificationBuilder;

describe('AsyncApi', function () {
    it('can be instantiated', function () {
        $scanner = new AnnotationScanner([__DIR__]);
        $builder = new SpecificationBuilder($scanner);
        $asyncApi = new AsyncApi($scanner, $builder);

        expect($asyncApi)->toBeInstanceOf(AsyncApi::class);
    });

    it('can scan for annotations', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $asyncApi = new AsyncApi($scanner, $builder);

        $annotations = $asyncApi->scan();

        expect($annotations)->toBeArray();
    });

    it('can build specification', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $asyncApi = new AsyncApi($scanner, $builder);

        $spec = $asyncApi->build();

        expect($spec)->toBeArray()
            ->and($spec)->toHaveKey('asyncapi');
    });

    it('can export to JSON', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $asyncApi = new AsyncApi($scanner, $builder);

        $json = $asyncApi->toJson();

        expect($json)->toBeString()
            ->and(json_decode($json, true))->toBeArray();
    });

    it('can export to YAML', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $asyncApi = new AsyncApi($scanner, $builder);

        $yaml = $asyncApi->toYaml();

        expect($yaml)->toBeString()
            ->and($yaml)->toContain('asyncapi:');
    });

    it('can export to file', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $asyncApi = new AsyncApi($scanner, $builder);

        $tempFile = sys_get_temp_dir() . '/asyncapi_test_' . uniqid() . '.yaml';
        $asyncApi->exportToFile($tempFile, 'yaml');

        expect(file_exists($tempFile))->toBeTrue()
            ->and(file_get_contents($tempFile))->toContain('asyncapi:');

        unlink($tempFile);
    });

    it('creates directory when exporting to non-existent path', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $asyncApi = new AsyncApi($scanner, $builder);

        $tempDir = sys_get_temp_dir() . '/asyncapi_test_' . uniqid();
        $tempFile = $tempDir . '/spec.yaml';
        $asyncApi->exportToFile($tempFile, 'yaml');

        expect(file_exists($tempFile))->toBeTrue();

        unlink($tempFile);
        rmdir($tempDir);
    });

    it('exports valid JSON format', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $asyncApi = new AsyncApi($scanner, $builder);

        $json = $asyncApi->toJson();
        $decoded = json_decode($json, true);

        expect(json_last_error())->toBe(JSON_ERROR_NONE)
            ->and($decoded)->toBeArray();
    });

    it('exports pretty JSON when requested', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $asyncApi = new AsyncApi($scanner, $builder);

        $json = $asyncApi->toJson(true);

        // Pretty JSON should have newlines and indentation
        expect($json)->toContain("\n")
            ->and($json)->toContain('  ');
    });
});

