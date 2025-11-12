<?php

describe('ValidateCommand', function () {
    it('can run validate command', function () {
        $this->artisan('asyncapi:validate')
            ->assertSuccessful();
    });

    it('displays validation results', function () {
        $this->artisan('asyncapi:validate')
            ->expectsOutputToContain('Validating AsyncAPI')
            ->assertSuccessful();
    });

    it('validates specification structure', function () {
        $this->artisan('asyncapi:validate')
            ->assertSuccessful();
    });
});

