<?php

describe('ExportCommand', function () {
    it('can run export command', function () {
        $tempFile = sys_get_temp_dir().'/asyncapi_test_'.uniqid().'.yaml';

        $this->artisan('asyncapi:export '.$tempFile)
            ->assertSuccessful();

        expect(file_exists($tempFile))->toBeTrue();

        unlink($tempFile);
    });

    it('exports to YAML by default', function () {
        $tempFile = sys_get_temp_dir().'/asyncapi_test_'.uniqid().'.yaml';

        $this->artisan('asyncapi:export '.$tempFile)
            ->assertSuccessful();

        $content = file_get_contents($tempFile);
        expect($content)->toContain('asyncapi:');

        unlink($tempFile);
    });

    it('can export to JSON format', function () {
        $tempFile = sys_get_temp_dir().'/asyncapi_test_'.uniqid().'.json';

        $this->artisan('asyncapi:export '.$tempFile.' --format=json')
            ->assertSuccessful();

        $content = file_get_contents($tempFile);
        expect(json_decode($content, true))->toBeArray();

        unlink($tempFile);
    });

    it('auto-detects format from file extension', function () {
        $jsonFile = sys_get_temp_dir().'/asyncapi_test_'.uniqid().'.json';

        $this->artisan('asyncapi:export '.$jsonFile)
            ->assertSuccessful();

        $content = file_get_contents($jsonFile);
        expect(json_decode($content, true))->toBeArray();

        unlink($jsonFile);
    });

    it('accepts pretty option', function () {
        $tempFile = sys_get_temp_dir().'/asyncapi_test_'.uniqid().'.json';

        $this->artisan('asyncapi:export '.$tempFile.' --format=json --pretty')
            ->assertSuccessful();

        $content = file_get_contents($tempFile);
        // Pretty JSON should have newlines
        expect($content)->toContain("\n");

        unlink($tempFile);
    });

    it('creates directory if it does not exist', function () {
        $tempDir = sys_get_temp_dir().'/asyncapi_test_'.uniqid();
        $tempFile = $tempDir.'/spec.yaml';

        $this->artisan('asyncapi:export '.$tempFile)
            ->assertSuccessful();

        expect(file_exists($tempFile))->toBeTrue();

        unlink($tempFile);
        rmdir($tempDir);
    });

    it('displays success message', function () {
        $tempFile = sys_get_temp_dir().'/asyncapi_test_'.uniqid().'.yaml';

        $this->artisan('asyncapi:export '.$tempFile)
            ->expectsOutputToContain('exported successfully')
            ->assertSuccessful();

        unlink($tempFile);
    });
});
