<?php

namespace Drmmr763\AsyncApi\Commands;

use Drmmr763\AsyncApi\SpecificationBuilder;
use Illuminate\Console\Command;

class ValidateCommand extends Command
{
    protected $signature = 'asyncapi:validate';

    protected $description = 'Validate AsyncAPI annotations in the codebase';

    public function handle(SpecificationBuilder $builder): int
    {
        $this->info('Validating AsyncAPI annotations...');

        try {
            $specification = $builder->build();

            // Basic validation
            $errors = $this->validateSpecification($specification);

            if (empty($errors)) {
                $this->info('✓ AsyncAPI specification is valid!');
                $this->newLine();
                $this->displaySummary($specification);

                return self::SUCCESS;
            } else {
                $this->error('✗ AsyncAPI specification has validation errors:');
                $this->newLine();
                foreach ($errors as $error) {
                    $this->line("  • {$error}");
                }

                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Validation failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    protected function validateSpecification(array $specification): array
    {
        $errors = [];

        // Validate required fields
        if (! isset($specification['asyncapi'])) {
            $errors[] = 'Missing required field: asyncapi';
        }

        if (! isset($specification['info'])) {
            $errors[] = 'Missing required field: info';
        } else {
            if (! isset($specification['info']['title'])) {
                $errors[] = 'Missing required field: info.title';
            }
            if (! isset($specification['info']['version'])) {
                $errors[] = 'Missing required field: info.version';
            }
        }

        // Validate AsyncAPI version format
        if (isset($specification['asyncapi'])) {
            if (! preg_match('/^\d+\.\d+\.\d+$/', $specification['asyncapi'])) {
                $errors[] = 'Invalid AsyncAPI version format. Expected: X.Y.Z';
            }
        }

        // Validate that at least one of channels or operations exists
        if (! isset($specification['channels']) && ! isset($specification['operations'])) {
            $errors[] = 'Specification must define at least one channel or operation';
        }

        return $errors;
    }

    protected function displaySummary(array $specification): void
    {
        $this->line('<fg=cyan>Specification Summary:</>');
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

        if (isset($specification['components'])) {
            $componentCount = 0;
            foreach ($specification['components'] as $componentType => $components) {
                $componentCount += count($components);
            }
            $this->line('  Components: '.$componentCount);
        }
    }
}
