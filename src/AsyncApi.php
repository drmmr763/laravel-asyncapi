<?php

namespace Drmmr763\AsyncApi;

use Drmmr763\AsyncApi\Exporters\JsonExporter;
use Drmmr763\AsyncApi\Exporters\YamlExporter;

class AsyncApi
{
    protected AnnotationScanner $scanner;

    protected SpecificationBuilder $builder;

    public function __construct(AnnotationScanner $scanner, SpecificationBuilder $builder)
    {
        $this->scanner = $scanner;
        $this->builder = $builder;
    }

    /**
     * Scan for AsyncAPI annotations
     */
    public function scan(): array
    {
        return $this->scanner->scan();
    }

    /**
     * Build the AsyncAPI specification
     */
    public function build(): array
    {
        return $this->builder->build();
    }

    /**
     * Export specification to JSON
     */
    public function toJson(bool $prettyPrint = true): string
    {
        $specification = $this->build();
        $exporter = new JsonExporter($prettyPrint);

        return $exporter->export($specification);
    }

    /**
     * Export specification to YAML
     */
    public function toYaml(int $inline = 10, int $indent = 2): string
    {
        $specification = $this->build();
        $exporter = new YamlExporter($inline, $indent);

        return $exporter->export($specification);
    }

    /**
     * Export specification to a file
     */
    public function exportToFile(string $path, string $format = 'yaml'): void
    {
        $specification = $this->build();

        $exporter = match ($format) {
            'json' => new JsonExporter(config('asyncapi.pretty_print', true)),
            'yaml', 'yml' => new YamlExporter,
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };

        $exporter->exportToFile($specification, $path);
    }

    /**
     * Get the annotation scanner
     */
    public function getScanner(): AnnotationScanner
    {
        return $this->scanner;
    }

    /**
     * Get the specification builder
     */
    public function getBuilder(): SpecificationBuilder
    {
        return $this->builder;
    }
}
