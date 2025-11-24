<?php

namespace Drmmr763\AsyncApi;

use AsyncApi\Attributes\AsyncApi as AsyncApiAttribute;
use AsyncApi\Attributes\Channels;
use AsyncApi\Attributes\Components;
use AsyncApi\Attributes\Info;
use AsyncApi\Attributes\Messages;
use AsyncApi\Attributes\Operations;
use AsyncApi\Attributes\Parameters;
use AsyncApi\Attributes\Reference;
use AsyncApi\Attributes\Servers;

class SpecificationBuilder
{
    protected AnnotationScanner $scanner;

    public function __construct(AnnotationScanner $scanner)
    {
        $this->scanner = $scanner;
    }

    /**
     * Build the complete AsyncAPI specification from scanned annotations
     */
    public function build(): array
    {
        $scannedClasses = $this->scanner->scan();

        if (empty($scannedClasses)) {
            throw new \RuntimeException('No AsyncAPI annotations found in the scanned paths.');
        }

        // Find the main AsyncAPI attribute
        $mainSpec = $this->findMainSpecification($scannedClasses);

        if (! $mainSpec) {
            throw new \RuntimeException('No main AsyncAPI attribute found. Please add an #[AsyncApi] attribute to a class.');
        }

        // Build the specification
        $spec = $this->buildSpecification($mainSpec, $scannedClasses);

        return $spec;
    }

    /**
     * Find the main AsyncAPI specification attribute
     */
    protected function findMainSpecification(array $scannedClasses): ?array
    {
        foreach ($scannedClasses as $className => $annotations) {
            foreach ($annotations as $annotation) {
                if ($annotation['type'] === 'AsyncApi') {
                    return [
                        'class' => $className,
                        'attribute' => $annotation['attribute'],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Build the complete specification array
     */
    protected function buildSpecification(array $mainSpec, array $scannedClasses): array
    {
        /** @var AsyncApiAttribute $asyncApi */
        $asyncApi = $mainSpec['attribute'];

        $spec = [
            'asyncapi' => $asyncApi->asyncapi ?? '3.0.0',
        ];

        // Add info
        if ($asyncApi->info) {
            $spec['info'] = $this->buildInfo($asyncApi->info);
        }

        // Add id
        if ($asyncApi->id) {
            $spec['id'] = $asyncApi->id;
        }

        // Add default content type
        if ($asyncApi->defaultContentType) {
            $spec['defaultContentType'] = $asyncApi->defaultContentType;
        }

        // Add servers
        if ($asyncApi->servers) {
            $spec['servers'] = $this->buildServers($asyncApi->servers);
        }

        // Add channels
        if ($asyncApi->channels) {
            $spec['channels'] = $this->buildChannels($asyncApi->channels);
        }

        // Add operations
        if ($asyncApi->operations) {
            $spec['operations'] = $this->buildOperations($asyncApi->operations);
        }

        // Add components
        if ($asyncApi->components) {
            $spec['components'] = $this->buildComponents($asyncApi->components);
        }

        return $spec;
    }

    /**
     * Build the info section
     */
    protected function buildInfo(Info $info): array
    {
        $result = [
            'title' => $info->title,
            'version' => $info->version,
        ];

        if ($info->description) {
            $result['description'] = $info->description;
        }

        if ($info->termsOfService) {
            $result['termsOfService'] = $info->termsOfService;
        }

        if ($info->contact) {
            $result['contact'] = $this->attributeToArray($info->contact);
        }

        if ($info->license) {
            $result['license'] = $this->attributeToArray($info->license);
        }

        if ($info->tags) {
            $result['tags'] = array_map([$this, 'attributeToArray'], $info->tags);
        }

        if ($info->externalDocs) {
            $result['externalDocs'] = $this->attributeToArray($info->externalDocs);
        }

        return $result;
    }

    /**
     * Build the servers section
     */
    protected function buildServers(?Servers $servers): array
    {
        if (! $servers || ! $servers->servers) {
            return [];
        }

        $result = [];
        foreach ($servers->servers as $name => $server) {
            $result[$name] = $this->attributeToArray($server);
        }

        return $result;
    }

    /**
     * Build the channels section
     */
    protected function buildChannels(?Channels $channels): array
    {
        if (! $channels || ! $channels->channels) {
            return [];
        }

        $result = [];
        foreach ($channels->channels as $name => $channel) {
            $result[$name] = $this->attributeToArray($channel);
        }

        return $result;
    }

    /**
     * Build the operations section
     */
    protected function buildOperations(?Operations $operations): array
    {
        if (! $operations || ! $operations->operations) {
            return [];
        }

        $result = [];
        foreach ($operations->operations as $name => $operation) {
            $result[$name] = $this->attributeToArray($operation);
        }

        return $result;
    }

    /**
     * Build the components section
     */
    protected function buildComponents(?Components $components): array
    {
        if (! $components) {
            return [];
        }

        $result = [];

        if ($components->schemas) {
            $result['schemas'] = $this->arrayToSpec($components->schemas);
        }

        if ($components->servers) {
            $result['servers'] = $this->arrayToSpec($components->servers);
        }

        if ($components->channels) {
            $result['channels'] = $this->arrayToSpec($components->channels);
        }

        if ($components->operations) {
            $result['operations'] = $this->arrayToSpec($components->operations);
        }

        if ($components->messages) {
            $result['messages'] = $this->arrayToSpec($components->messages);
        }

        if ($components->securitySchemes) {
            $result['securitySchemes'] = $this->arrayToSpec($components->securitySchemes);
        }

        if ($components->parameters) {
            $result['parameters'] = $this->arrayToSpec($components->parameters);
        }

        if ($components->correlationIds) {
            $result['correlationIds'] = $this->arrayToSpec($components->correlationIds);
        }

        if ($components->replies) {
            $result['replies'] = $this->arrayToSpec($components->replies);
        }

        if ($components->replyAddresses) {
            $result['replyAddresses'] = $this->arrayToSpec($components->replyAddresses);
        }

        if ($components->externalDocs) {
            $result['externalDocs'] = $this->arrayToSpec($components->externalDocs);
        }

        if ($components->tags) {
            $result['tags'] = $this->arrayToSpec($components->tags);
        }

        return $result;
    }

    /**
     * Convert an attribute object to an array
     */
    protected function attributeToArray(object $attribute): array
    {
        // Handle Reference objects specially - use $ref instead of ref
        if ($attribute instanceof Reference) {
            return ['$ref' => $attribute->ref];
        }

        // Handle Messages objects specially - unwrap the messages property
        if ($attribute instanceof Messages) {
            return $this->arrayToSpec($attribute->messages);
        }

        // Handle Parameters objects specially - unwrap the parameters property
        if ($attribute instanceof Parameters) {
            return $this->arrayToSpec($attribute->parameters);
        }

        $result = [];
        $reflection = new \ReflectionObject($attribute);

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($attribute);

            if ($value === null) {
                continue;
            }

            $name = $property->getName();

            // Handle extension properties - add them with x- prefix
            if ($name === 'x' && is_array($value)) {
                foreach ($value as $key => $extensionValue) {
                    $result['x-'.$key] = $extensionValue;
                }

                continue;
            }

            if (is_object($value)) {
                $result[$name] = $this->attributeToArray($value);
            } elseif (is_array($value)) {
                $result[$name] = $this->arrayToSpec($value);
            } else {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    /**
     * Convert an array to specification format
     */
    protected function arrayToSpec(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_object($value)) {
                $result[$key] = $this->attributeToArray($value);
            } elseif (is_array($value)) {
                $result[$key] = $this->arrayToSpec($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
