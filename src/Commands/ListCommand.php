<?php

namespace Drmmr763\AsyncApi\Commands;

use Drmmr763\AsyncApi\AnnotationScanner;
use Illuminate\Console\Command;

class ListCommand extends Command
{
    protected $signature = 'asyncapi:list 
                            {--type= : Filter by annotation type (e.g., Channel, Operation, Message)}';

    protected $description = 'List all AsyncAPI annotations found in the codebase';

    public function handle(AnnotationScanner $scanner): int
    {
        $this->info('Scanning for AsyncAPI annotations...');

        try {
            $scannedClasses = $scanner->scan();

            if (empty($scannedClasses)) {
                $this->warn('No AsyncAPI annotations found in the configured scan paths.');
                return self::SUCCESS;
            }

            $type = $this->option('type');

            if ($type) {
                $this->displayFilteredAnnotations($scannedClasses, $type);
            } else {
                $this->displayAllAnnotations($scannedClasses);
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to scan annotations: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    protected function displayAllAnnotations(array $scannedClasses): void
    {
        $totalCount = 0;
        $typeCount = [];

        foreach ($scannedClasses as $className => $annotations) {
            foreach ($annotations as $annotation) {
                $type = $annotation['type'];
                $typeCount[$type] = ($typeCount[$type] ?? 0) + 1;
                $totalCount++;
            }
        }

        $this->newLine();
        $this->line('<fg=cyan>Found ' . $totalCount . ' AsyncAPI annotation(s) in ' . count($scannedClasses) . ' class(es)</>');
        $this->newLine();

        // Display by type
        foreach ($typeCount as $type => $count) {
            $this->line("<fg=yellow>{$type}:</> {$count}");
        }

        $this->newLine();
        $this->line('<fg=cyan>Details:</>');
        $this->newLine();

        foreach ($scannedClasses as $className => $annotations) {
            $this->line("<fg=green>{$className}</>");
            
            foreach ($annotations as $annotation) {
                $location = isset($annotation['method']) 
                    ? "  └─ Method: {$annotation['method']}" 
                    : "  └─ Class";
                
                $this->line("  <fg=yellow>@{$annotation['type']}</> {$location}");
            }
            
            $this->newLine();
        }
    }

    protected function displayFilteredAnnotations(array $scannedClasses, string $type): void
    {
        $filtered = [];
        $count = 0;

        foreach ($scannedClasses as $className => $annotations) {
            foreach ($annotations as $annotation) {
                if (strcasecmp($annotation['type'], $type) === 0) {
                    $filtered[$className][] = $annotation;
                    $count++;
                }
            }
        }

        if (empty($filtered)) {
            $this->warn("No annotations of type '{$type}' found.");
            return;
        }

        $this->newLine();
        $this->line("<fg=cyan>Found {$count} annotation(s) of type '{$type}'</>");
        $this->newLine();

        foreach ($filtered as $className => $annotations) {
            $this->line("<fg=green>{$className}</>");
            
            foreach ($annotations as $annotation) {
                $location = isset($annotation['method']) 
                    ? "  └─ Method: {$annotation['method']}" 
                    : "  └─ Class";
                
                $this->line("  <fg=yellow>@{$annotation['type']}</> {$location}");
            }
            
            $this->newLine();
        }
    }
}

