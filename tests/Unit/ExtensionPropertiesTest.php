<?php

use AsyncApi\Attributes\Server;
use Drmmr763\AsyncApi\SpecificationBuilder;
use Drmmr763\AsyncApi\AnnotationScanner;

describe('Extension Properties', function () {
    it('serializes x properties with x- prefix', function () {
        $scanner = new AnnotationScanner([__DIR__.'/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        
        // Create a server with extension properties
        $server = new Server(
            host: 'localhost:9092',
            protocol: 'kafka',
            description: 'Test server with extensions',
            x: [
                'internal-id' => 'server-123',
                'region' => 'us-east-1',
                'custom-property' => ['nested' => 'value']
            ]
        );
        
        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($builder);
        $method = $reflection->getMethod('attributeToArray');
        $method->setAccessible(true);
        
        $result = $method->invoke($builder, $server);
        
        // Verify extension properties are prefixed with x-
        expect($result)->toHaveKey('x-internal-id')
            ->and($result['x-internal-id'])->toBe('server-123')
            ->and($result)->toHaveKey('x-region')
            ->and($result['x-region'])->toBe('us-east-1')
            ->and($result)->toHaveKey('x-custom-property')
            ->and($result['x-custom-property'])->toBe(['nested' => 'value'])
            ->and($result)->not->toHaveKey('x'); // Original 'x' property should not be in output
    });

    it('handles null x property gracefully', function () {
        $scanner = new AnnotationScanner([__DIR__.'/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        
        // Create a server without extension properties
        $server = new Server(
            host: 'localhost:9092',
            protocol: 'kafka',
            description: 'Test server without extensions'
        );
        
        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($builder);
        $method = $reflection->getMethod('attributeToArray');
        $method->setAccessible(true);
        
        $result = $method->invoke($builder, $server);
        
        // Verify no x- properties are added
        expect($result)->not->toHaveKey('x');
        
        // Check that no keys start with 'x-'
        $xKeys = array_filter(array_keys($result), fn($key) => str_starts_with($key, 'x-'));
        expect($xKeys)->toBeEmpty();
    });
});

