<?php

describe('ListCommand', function () {
    it('can run list command', function () {
        $this->artisan('asyncapi:list')
            ->assertSuccessful();
    });

    it('displays annotations list', function () {
        $this->artisan('asyncapi:list')
            ->expectsOutputToContain('AsyncAPI annotation')
            ->assertSuccessful();
    });

    it('accepts type filter option', function () {
        $this->artisan('asyncapi:list --type=Channel')
            ->assertSuccessful();
    });

    it('can filter by Message type', function () {
        $this->artisan('asyncapi:list --type=Message')
            ->assertSuccessful();
    });

    it('can filter by Operation type', function () {
        $this->artisan('asyncapi:list --type=Operation')
            ->assertSuccessful();
    });

    it('displays found annotations count', function () {
        $this->artisan('asyncapi:list')
            ->assertSuccessful();
    });
});
