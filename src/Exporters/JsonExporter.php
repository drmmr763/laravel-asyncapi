<?php

namespace Drmmr763\AsyncApi\Exporters;

class JsonExporter implements ExporterInterface
{
    protected bool $prettyPrint;

    public function __construct(bool $prettyPrint = true)
    {
        $this->prettyPrint = $prettyPrint;
    }

    /**
     * Export the AsyncAPI specification to JSON string
     */
    public function export(array $specification): string
    {
        $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        
        if ($this->prettyPrint) {
            $options |= JSON_PRETTY_PRINT;
        }

        $json = json_encode($specification, $options);

        if ($json === false) {
            throw new \RuntimeException('Failed to encode specification to JSON: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Export the AsyncAPI specification to a JSON file
     */
    public function exportToFile(array $specification, string $filePath): void
    {
        $json = $this->export($specification);

        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_put_contents($filePath, $json) === false) {
            throw new \RuntimeException("Failed to write JSON file to: {$filePath}");
        }
    }

    /**
     * Get the file extension for JSON files
     */
    public function getExtension(): string
    {
        return 'json';
    }

    /**
     * Set whether to pretty print the JSON output
     */
    public function setPrettyPrint(bool $prettyPrint): self
    {
        $this->prettyPrint = $prettyPrint;
        return $this;
    }
}

