<?php

namespace Drmmr763\AsyncApi\Exporters;

interface ExporterInterface
{
    /**
     * Export the AsyncAPI specification to a string
     */
    public function export(array $specification): string;

    /**
     * Export the AsyncAPI specification to a file
     */
    public function exportToFile(array $specification, string $filePath): void;

    /**
     * Get the file extension for this exporter
     */
    public function getExtension(): string;
}
