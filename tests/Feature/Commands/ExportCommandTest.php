<?php

describe('ExportCommand', function () {
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

    it('can run export command', function () {
        $tempFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'asyncapi_test_'.uniqid().'.yaml';
        $this->tempFiles[] = $tempFile;

        $this->artisan('asyncapi:export', ['path' => $tempFile])
            ->assertSuccessful();

        expect(file_exists($tempFile))->toBeTrue();
    });

    it('exports to YAML by default', function () {
        $tempFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'asyncapi_test_'.uniqid().'.yaml';
        $this->tempFiles[] = $tempFile;

        $this->artisan('asyncapi:export', ['path' => $tempFile])
            ->assertSuccessful();

        $content = file_get_contents($tempFile);
        expect($content)->toContain('asyncapi:');
    });

    it('can export to JSON format', function () {
        $tempFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'asyncapi_test_'.uniqid().'.json';
        $this->tempFiles[] = $tempFile;

        $this->artisan('asyncapi:export', ['path' => $tempFile, '--format' => 'json'])
            ->assertSuccessful();

        $content = file_get_contents($tempFile);
        expect(json_decode($content, true))->toBeArray();
    });

    it('auto-detects format from file extension', function () {
        $jsonFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'asyncapi_test_'.uniqid().'.json';
        $this->tempFiles[] = $jsonFile;

        $this->artisan('asyncapi:export', ['path' => $jsonFile])
            ->assertSuccessful();

        $content = file_get_contents($jsonFile);
        expect(json_decode($content, true))->toBeArray();
    });

    it('accepts pretty option', function () {
        $tempFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'asyncapi_test_'.uniqid().'.json';
        $this->tempFiles[] = $tempFile;

        $this->artisan('asyncapi:export', ['path' => $tempFile, '--format' => 'json', '--pretty' => true])
            ->assertSuccessful();

        $content = file_get_contents($tempFile);
        // Pretty JSON should have newlines
        expect($content)->toContain("\n");
    });

    it('creates directory if it does not exist', function () {
        $tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'asyncapi_test_'.uniqid();
        $tempFile = $tempDir.DIRECTORY_SEPARATOR.'spec.yaml';
        $this->tempFiles[] = $tempFile;
        $this->tempDirs[] = $tempDir;

        $this->artisan('asyncapi:export', ['path' => $tempFile])
            ->assertSuccessful();

        expect(file_exists($tempFile))->toBeTrue();
    });

    it('displays success message', function () {
        $tempFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'asyncapi_test_'.uniqid().'.yaml';
        $this->tempFiles[] = $tempFile;

        $this->artisan('asyncapi:export', ['path' => $tempFile])
            ->expectsOutputToContain('exported successfully')
            ->assertSuccessful();
    });
});
