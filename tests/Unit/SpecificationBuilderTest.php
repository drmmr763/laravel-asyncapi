<?php

use Drmmr763\AsyncApi\AnnotationScanner;
use Drmmr763\AsyncApi\SpecificationBuilder;

describe('SpecificationBuilder', function () {
    it('can be instantiated', function () {
        $scanner = new AnnotationScanner([__DIR__]);
        $builder = new SpecificationBuilder($scanner);

        expect($builder)->toBeInstanceOf(SpecificationBuilder::class);
    });

    it('builds a valid AsyncAPI specification', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $spec = $builder->build();

        expect($spec)->toBeArray()
            ->and($spec)->toHaveKey('asyncapi')
            ->and($spec['asyncapi'])->toBe('3.0.0');
    });

    it('includes info section in specification', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $spec = $builder->build();

        expect($spec)->toHaveKey('info')
            ->and($spec['info'])->toHaveKey('title')
            ->and($spec['info'])->toHaveKey('version');
    });

    it('includes servers section when defined', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $spec = $builder->build();

        expect($spec)->toHaveKey('servers');
    });

    it('includes channels section when defined', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $spec = $builder->build();

        expect($spec)->toHaveKey('channels');
    });

    it('includes operations section when defined', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $spec = $builder->build();

        expect($spec)->toHaveKey('operations');
    });

    it('includes components section when defined', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $spec = $builder->build();

        expect($spec)->toHaveKey('components');
    });

    it('handles empty scan results', function () {
        $scanner = new AnnotationScanner(['/non/existent/path']);
        $builder = new SpecificationBuilder($scanner);

        // Should throw an exception when no annotations are found
        expect(fn() => $builder->build())
            ->toThrow(\RuntimeException::class, 'No AsyncAPI annotations found in the scanned paths.');
    });

    it('builds specification with correct structure', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $spec = $builder->build();

        expect($spec)->toHaveKeys(['asyncapi', 'info']);
    });

    it('preserves AsyncAPI version', function () {
        $scanner = new AnnotationScanner([__DIR__ . '/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        $spec = $builder->build();

        expect($spec['asyncapi'])->toBe('3.0.0');
    });
});

