<?php

namespace Drmmr763\AsyncApi\Commands;

use Drmmr763\AsyncApi\SpecificationBuilder;
use Illuminate\Console\Command;

class ExportCommand extends Command
{
    protected $signature = 'asyncapi:export 
                            {path : The output file path}
                            {--format= : The output format (json or yaml)}
                            {--pretty : Pretty print the output}';

    protected $description = 'Export AsyncAPI specification to a file';

    public function handle(SpecificationBuilder $builder): int
    {
        $this->info('Generating AsyncAPI specification...');

        try {
            $specification = $builder->build();

            $path = $this->argument('path');
            $format = $this->option('format');

            // Auto-detect format from file extension if not specified
            if (!$format) {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                $format = match ($extension) {
                    'json' => 'json',
                    'yaml', 'yml' => 'yaml',
                    default => config('asyncapi.default_export_format', 'yaml'),
                };
            }

            $exporter = $this->getExporter($format);

            // Add extension if not present
            if (!str_ends_with($path, '.' . $exporter->getExtension())) {
                $path .= '.' . $exporter->getExtension();
            }

            $exporter->exportToFile($specification, $path);

            $this->info("AsyncAPI specification exported successfully to: {$path}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to export AsyncAPI specification: ' . $e->getMessage());
            return self::FAILURE;
        }
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

        return new $exporterClass();
    }
}

