<?php

use Drmmr763\AsyncApi\AnnotationScanner;
use Drmmr763\AsyncApi\AsyncApi;
use Drmmr763\AsyncApi\SpecificationBuilder;

describe('Package Integration', function () {
    it('can instantiate annotation scanner', function () {
        $scanner = new AnnotationScanner([__DIR__]);
        expect($scanner)->toBeInstanceOf(AnnotationScanner::class);
    });

    it('can instantiate specification builder', function () {
        $scanner = new AnnotationScanner([__DIR__]);
        $builder = new SpecificationBuilder($scanner);
        expect($builder)->toBeInstanceOf(SpecificationBuilder::class);
    });

    it('can instantiate asyncapi class', function () {
        $scanner = new AnnotationScanner([__DIR__]);
        $builder = new SpecificationBuilder($scanner);
        $asyncApi = new AsyncApi($scanner, $builder);
        expect($asyncApi)->toBeInstanceOf(AsyncApi::class);
    });

    it('can resolve asyncapi from container', function () {
        $asyncApi = app(AsyncApi::class);
        expect($asyncApi)->toBeInstanceOf(AsyncApi::class);
    });

    it('can access asyncapi via facade', function () {
        expect(\Drmmr763\AsyncApi\Facades\AsyncApi::getFacadeRoot())
            ->toBeInstanceOf(AsyncApi::class);
    });

    it('has config loaded', function () {
        expect(config('asyncapi'))->toBeArray()
            ->and(config('asyncapi.version'))->toBe('3.0.0');
    });
});
