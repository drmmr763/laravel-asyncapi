<?php

use Drmmr763\AsyncApi\Exporters\YamlExporter;
use Symfony\Component\Yaml\Yaml;

describe('YamlExporter', function () {
    it('can be instantiated', function () {
        $exporter = new YamlExporter();
        expect($exporter)->toBeInstanceOf(YamlExporter::class);
    });

    it('exports array to YAML string', function () {
        $exporter = new YamlExporter();
        $data = ['asyncapi' => '3.0.0', 'info' => ['title' => 'Test']];
        $yaml = $exporter->export($data);

        expect($yaml)->toBeString()
            ->and($yaml)->toContain('asyncapi:')
            ->and($yaml)->toContain('3.0.0');
    });

    it('can export to file', function () {
        $exporter = new YamlExporter();
        $data = ['asyncapi' => '3.0.0', 'info' => ['title' => 'Test']];
        $tempFile = sys_get_temp_dir() . '/test_' . uniqid() . '.yaml';

        $exporter->exportToFile($data, $tempFile);

        expect(file_exists($tempFile))->toBeTrue()
            ->and(file_get_contents($tempFile))->toContain('asyncapi:');

        unlink($tempFile);
    });

    it('creates directory when exporting to non-existent path', function () {
        $exporter = new YamlExporter();
        $data = ['asyncapi' => '3.0.0'];
        $tempDir = sys_get_temp_dir() . '/asyncapi_test_' . uniqid();
        $tempFile = $tempDir . '/spec.yaml';

        $exporter->exportToFile($data, $tempFile);

        expect(file_exists($tempFile))->toBeTrue();

        unlink($tempFile);
        rmdir($tempDir);
    });

    it('returns correct file extension', function () {
        $exporter = new YamlExporter();
        expect($exporter->getExtension())->toBe('yaml');
    });

    it('handles empty arrays', function () {
        $exporter = new YamlExporter();
        $yaml = $exporter->export([]);

        expect($yaml)->toBeString();
    });

    it('handles nested arrays', function () {
        $exporter = new YamlExporter();
        $data = [
            'level1' => [
                'level2' => [
                    'level3' => 'value',
                ],
            ],
        ];
        $yaml = $exporter->export($data);
        $parsed = Yaml::parse($yaml);

        expect($parsed)->toBe($data);
    });

    it('preserves data types', function () {
        $exporter = new YamlExporter();
        $data = [
            'string' => 'text',
            'integer' => 42,
            'float' => 3.14,
            'boolean' => true,
            'null' => null,
            'array' => [1, 2, 3],
        ];
        $yaml = $exporter->export($data);
        $parsed = Yaml::parse($yaml);

        expect($parsed['string'])->toBe('text')
            ->and($parsed['integer'])->toBe(42)
            ->and($parsed['float'])->toBe(3.14)
            ->and($parsed['boolean'])->toBe(true)
            ->and($parsed['null'])->toBeNull()
            ->and($parsed['array'])->toBe([1, 2, 3]);
    });

    it('produces valid YAML', function () {
        $exporter = new YamlExporter();
        $data = [
            'asyncapi' => '3.0.0',
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
            ],
            'channels' => [
                'test' => [
                    'address' => 'test.channel',
                ],
            ],
        ];
        $yaml = $exporter->export($data);

        // Should be parseable back to the same structure
        $parsed = Yaml::parse($yaml);
        expect($parsed)->toBe($data);
    });

    it('handles special characters in strings', function () {
        $exporter = new YamlExporter();
        $data = [
            'description' => 'This has: colons, and "quotes"',
            'example' => "Line 1\nLine 2",
        ];
        $yaml = $exporter->export($data);
        $parsed = Yaml::parse($yaml);

        expect($parsed)->toBe($data);
    });
});

