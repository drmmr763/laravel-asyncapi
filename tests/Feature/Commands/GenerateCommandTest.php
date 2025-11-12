<?php

describe('GenerateCommand', function () {
    it('can run generate command', function () {
        $this->artisan('asyncapi:generate')
            ->assertSuccessful();
    });

    it('displays specification when run', function () {
        $this->artisan('asyncapi:generate')
            ->expectsOutputToContain('AsyncAPI specification generated successfully!')
            ->assertSuccessful();
    });

    it('accepts format option', function () {
        $this->artisan('asyncapi:generate --format=json')
            ->assertSuccessful();
    });

    it('accepts yaml format', function () {
        $this->artisan('asyncapi:generate --format=yaml')
            ->assertSuccessful();
    });

    it('accepts output option', function () {
        $tempFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'asyncapi_test_'.uniqid().'.yaml';

        $this->artisan('asyncapi:generate --output='.$tempFile)
            ->assertSuccessful();

        expect(file_exists($tempFile))->toBeTrue();

        unlink($tempFile);
    });

    it('accepts pretty option', function () {
        $this->artisan('asyncapi:generate --pretty')
            ->assertSuccessful();
    });

    it('can combine options', function () {
        $tempFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'asyncapi_test_'.uniqid().'.json';

        $this->artisan('asyncapi:generate --format=json --output='.$tempFile.' --pretty')
            ->assertSuccessful();

        expect(file_exists($tempFile))->toBeTrue();

        unlink($tempFile);
    });

    it('displays summary information', function () {
        $this->artisan('asyncapi:generate')
            ->expectsOutputToContain('Summary:')
            ->assertSuccessful();
    });
});
