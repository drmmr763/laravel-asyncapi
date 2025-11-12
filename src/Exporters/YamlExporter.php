<?php

namespace Drmmr763\AsyncApi\Exporters;

use Symfony\Component\Yaml\Yaml;

class YamlExporter implements ExporterInterface
{
    protected int $inline;
    protected int $indent;

    public function __construct(int $inline = 10, int $indent = 2)
    {
        $this->inline = $inline;
        $this->indent = $indent;
    }

    /**
     * Export the AsyncAPI specification to YAML string
     */
    public function export(array $specification): string
    {
        return Yaml::dump(
            $specification,
            $this->inline,
            $this->indent,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE
        );
    }

    /**
     * Export the AsyncAPI specification to a YAML file
     */
    public function exportToFile(array $specification, string $filePath): void
    {
        $yaml = $this->export($specification);

        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_put_contents($filePath, $yaml) === false) {
            throw new \RuntimeException("Failed to write YAML file to: {$filePath}");
        }
    }

    /**
     * Get the file extension for YAML files
     */
    public function getExtension(): string
    {
        return 'yaml';
    }

    /**
     * Set the inline level for YAML output
     */
    public function setInline(int $inline): self
    {
        $this->inline = $inline;
        return $this;
    }

    /**
     * Set the indent level for YAML output
     */
    public function setIndent(int $indent): self
    {
        $this->indent = $indent;
        return $this;
    }
}

