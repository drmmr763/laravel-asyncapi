<?php

namespace Drmmr763\AsyncApi\Commands;

use Drmmr763\AsyncApi\SpecificationBuilder;
use Illuminate\Console\Command;

class GenerateCommand extends Command
{
    protected $signature = 'asyncapi:generate 
                            {--format=yaml : The output format (json or yaml)}
                            {--output= : The output file path}
                            {--pretty : Pretty print the output}';

    protected $description = 'Generate AsyncAPI specification from annotations';

    public function handle(SpecificationBuilder $builder): int
    {
        $this->info('Scanning for AsyncAPI annotations...');

        try {
            $specification = $builder->build();

            $this->info('AsyncAPI specification generated successfully!');
            $this->newLine();

            // Display summary
            $this->displaySummary($specification);

            // Export if output path is specified
            $outputPath = $this->option('output');
            if ($outputPath) {
                $this->exportSpecification($specification, $outputPath);
            } else {
                $this->displaySpecification($specification);
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate AsyncAPI specification: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    protected function displaySummary(array $specification): void
    {
        $this->line('<fg=cyan>Summary:</>');
        $this->line('  AsyncAPI Version: '.($specification['asyncapi'] ?? 'N/A'));
        $this->line('  Title: '.($specification['info']['title'] ?? 'N/A'));
        $this->line('  Version: '.($specification['info']['version'] ?? 'N/A'));

        if (isset($specification['servers'])) {
            $this->line('  Servers: '.count($specification['servers']));
        }

        if (isset($specification['channels'])) {
            $this->line('  Channels: '.count($specification['channels']));
        }

        if (isset($specification['operations'])) {
            $this->line('  Operations: '.count($specification['operations']));
        }

        $this->newLine();
    }

    protected function displaySpecification(array $specification): void
    {
        $format = $this->option('format') ?? config('asyncapi.default_export_format', 'yaml');

        $exporter = $this->getExporter($format);
        $output = $exporter->export($specification);

        $this->line('<fg=cyan>Generated Specification:</>');
        $this->newLine();
        $this->line($output);
    }

    protected function exportSpecification(array $specification, string $outputPath): void
    {
        $format = $this->option('format') ?? config('asyncapi.default_export_format', 'yaml');

        $exporter = $this->getExporter($format);

        // Add extension if not present
        if (! str_ends_with($outputPath, '.'.$exporter->getExtension())) {
            $outputPath .= '.'.$exporter->getExtension();
        }

        $exporter->exportToFile($specification, $outputPath);

        $this->info("Specification exported to: {$outputPath}");
    }

    protected function getExporter(string $format)
    {
        $exporterClass = match ($format) {
            'json' => \Drmmr763\AsyncApi\Exporters\JsonExporter::class,
            'yaml', 'yml' => \Drmmr763\AsyncApi\Exporters\YamlExporter::class,
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };

        $prettyPrint = $this->option('pretty') ?? config('asyncapi.pretty_print', true);

        if ($format === 'json') {
            return new $exporterClass($prettyPrint);
        }

        return new $exporterClass;
    }
}
