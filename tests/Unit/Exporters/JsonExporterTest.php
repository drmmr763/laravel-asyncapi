<?php

use Drmmr763\AsyncApi\Exporters\JsonExporter;

describe('JsonExporter', function () {
    beforeEach(function () {
        $this->tempFiles = [];
        $this->tempDirs = [];
    });

    afterEach(function () {
        // Clean up any temporary files
        foreach ($this->tempFiles ?? [] as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        // Clean up any temporary directories
        foreach ($this->tempDirs ?? [] as $dir) {
            if (is_dir($dir)) {
                @rmdir($dir);
            }
        }
    });

    it('can be instantiated', function () {
        $exporter = new JsonExporter;
        expect($exporter)->toBeInstanceOf(JsonExporter::class);
    });

    it('exports array to JSON string', function () {
        $exporter = new JsonExporter;
        $data = ['asyncapi' => '3.0.0', 'info' => ['title' => 'Test']];
        $json = $exporter->export($data);

        expect($json)->toBeString()
            ->and(json_decode($json, true))->toBe($data);
    });

    it('exports pretty JSON when requested', function () {
        $exporter = new JsonExporter;
        $data = ['asyncapi' => '3.0.0', 'info' => ['title' => 'Test']];
        $json = $exporter->export($data, true);

        expect($json)->toContain("\n")
            ->and($json)->toContain('  ');
    });

    it('exports compact JSON by default', function () {
        $exporter = new JsonExporter(false); // Disable pretty print for compact JSON
        $data = ['asyncapi' => '3.0.0'];
        $json = $exporter->export($data);

        // Compact JSON should not have newlines
        expect($json)->not->toContain("\n")
            ->and($json)->toBe('{"asyncapi":"3.0.0"}');
    });

    it('can export to file', function () {
        $exporter = new JsonExporter;
        $data = ['asyncapi' => '3.0.0', 'info' => ['title' => 'Test']];
        $tempFile = sys_get_temp_dir().'/test_'.uniqid().'.json';
        $this->tempFiles[] = $tempFile;

        $exporter->exportToFile($data, $tempFile);

        expect(file_exists($tempFile))->toBeTrue()
            ->and(json_decode(file_get_contents($tempFile), true))->toBe($data);
    });

    it('creates directory when exporting to non-existent path', function () {
        $exporter = new JsonExporter;
        $data = ['asyncapi' => '3.0.0'];
        $tempDir = sys_get_temp_dir().'/asyncapi_test_'.uniqid();
        $tempFile = $tempDir.'/spec.json';
        $this->tempFiles[] = $tempFile;
        $this->tempDirs[] = $tempDir;

        $exporter->exportToFile($data, $tempFile);

        expect(file_exists($tempFile))->toBeTrue();
    });

    it('returns correct file extension', function () {
        $exporter = new JsonExporter;
        expect($exporter->getExtension())->toBe('json');
    });

    it('handles empty arrays', function () {
        $exporter = new JsonExporter;
        $json = $exporter->export([]);

        // PHP json_encode returns [] for empty arrays
        expect($json)->toBe('[]');
    });

    it('handles nested arrays', function () {
        $exporter = new JsonExporter;
        $data = [
            'level1' => [
                'level2' => [
                    'level3' => 'value',
                ],
            ],
        ];
        $json = $exporter->export($data);
        $decoded = json_decode($json, true);

        expect($decoded)->toBe($data);
    });

    it('preserves data types', function () {
        $exporter = new JsonExporter;
        $data = [
            'string' => 'text',
            'integer' => 42,
            'float' => 3.14,
            'boolean' => true,
            'null' => null,
            'array' => [1, 2, 3],
        ];
        $json = $exporter->export($data);
        $decoded = json_decode($json, true);

        expect($decoded['string'])->toBe('text')
            ->and($decoded['integer'])->toBe(42)
            ->and($decoded['float'])->toBe(3.14)
            ->and($decoded['boolean'])->toBe(true)
            ->and($decoded['null'])->toBeNull()
            ->and($decoded['array'])->toBe([1, 2, 3]);
    });
});
