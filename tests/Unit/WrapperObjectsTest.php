<?php

use AsyncApi\Attributes\Messages;
use AsyncApi\Attributes\Parameters;
use AsyncApi\Attributes\Parameter;
use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Reference;
use AsyncApi\Attributes\Schema;
use Drmmr763\AsyncApi\SpecificationBuilder;
use Drmmr763\AsyncApi\AnnotationScanner;

describe('Wrapper Objects', function () {
    it('unwraps Messages object correctly', function () {
        $scanner = new AnnotationScanner([__DIR__.'/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        
        // Create a Messages wrapper with message definitions
        $messages = new Messages(
            messages: [
                'userCreated' => new Message(
                    name: 'UserCreated',
                    title: 'User Created Event',
                    payload: new Schema(type: 'object')
                ),
                'userUpdated' => new Reference(ref: '#/components/messages/UserUpdated')
            ]
        );
        
        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($builder);
        $method = $reflection->getMethod('attributeToArray');
        $method->setAccessible(true);
        
        $result = $method->invoke($builder, $messages);
        
        // Verify the Messages wrapper is unwrapped
        expect($result)->toHaveKey('userCreated')
            ->and($result)->toHaveKey('userUpdated')
            ->and($result)->not->toHaveKey('messages') // Should not have the wrapper property
            ->and($result['userCreated'])->toBeArray()
            ->and($result['userCreated'])->toHaveKey('name')
            ->and($result['userCreated']['name'])->toBe('UserCreated')
            ->and($result['userUpdated'])->toBeArray()
            ->and($result['userUpdated'])->toHaveKey('$ref')
            ->and($result['userUpdated']['$ref'])->toBe('#/components/messages/UserUpdated');
    });

    it('unwraps Parameters object correctly', function () {
        $scanner = new AnnotationScanner([__DIR__.'/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        
        // Create a Parameters wrapper with parameter definitions
        $parameters = new Parameters(
            parameters: [
                'userId' => new Parameter(
                    description: 'User ID parameter'
                ),
                'tenantId' => new Reference(ref: '#/components/parameters/TenantId')
            ]
        );
        
        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($builder);
        $method = $reflection->getMethod('attributeToArray');
        $method->setAccessible(true);
        
        $result = $method->invoke($builder, $parameters);
        
        // Verify the Parameters wrapper is unwrapped
        expect($result)->toHaveKey('userId')
            ->and($result)->toHaveKey('tenantId')
            ->and($result)->not->toHaveKey('parameters') // Should not have the wrapper property
            ->and($result['userId'])->toBeArray()
            ->and($result['userId'])->toHaveKey('description')
            ->and($result['userId']['description'])->toBe('User ID parameter')
            ->and($result['tenantId'])->toBeArray()
            ->and($result['tenantId'])->toHaveKey('$ref')
            ->and($result['tenantId']['$ref'])->toBe('#/components/parameters/TenantId');
    });

    it('serializes Reference objects with $ref key', function () {
        $scanner = new AnnotationScanner([__DIR__.'/../Fixtures']);
        $builder = new SpecificationBuilder($scanner);
        
        // Create a Reference object
        $reference = new Reference(ref: '#/components/schemas/User');
        
        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($builder);
        $method = $reflection->getMethod('attributeToArray');
        $method->setAccessible(true);
        
        $result = $method->invoke($builder, $reference);
        
        // Verify Reference uses $ref instead of ref
        expect($result)->toHaveKey('$ref')
            ->and($result['$ref'])->toBe('#/components/schemas/User')
            ->and($result)->not->toHaveKey('ref'); // Should not have 'ref' key
    });
});

