<?php

namespace Drmmr763\AsyncApi;

use AsyncApi\Attributes\Operation;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;

class AnnotationScanner
{
    protected array $scanPaths;

    protected array $scannedClasses = [];

    public function __construct(array $scanPaths)
    {
        $this->scanPaths = $scanPaths;
    }

    /**
     * Scan all configured paths for AsyncAPI annotations
     */
    public function scan(): array
    {
        $this->scannedClasses = [];

        foreach ($this->scanPaths as $path) {
            if (is_dir($path)) {
                $this->scanDirectory($path);
            } elseif (is_file($path)) {
                $this->scanFile($path);
            }
        }

        return $this->scannedClasses;
    }

    /**
     * Scan a directory for PHP files with AsyncAPI annotations
     */
    protected function scanDirectory(string $directory): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        $phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        foreach ($phpFiles as $file) {
            $this->scanFile($file[0]);
        }
    }

    /**
     * Scan a single PHP file for AsyncAPI annotations
     */
    protected function scanFile(string $filePath): void
    {
        if (! file_exists($filePath)) {
            return;
        }

        $content = file_get_contents($filePath);

        // Extract namespace and class name
        if (! preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            return;
        }

        if (! preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            return;
        }

        $className = $namespaceMatch[1].'\\'.$classMatch[1];

        // Check if class exists and can be loaded
        if (! class_exists($className)) {
            return;
        }

        $this->scanClass($className);
    }

    /**
     * Scan a class for AsyncAPI annotations
     */
    protected function scanClass(string $className): void
    {
        try {
            $reflection = new ReflectionClass($className);
            $attributes = $reflection->getAttributes();

            $classAnnotations = [];

            foreach ($attributes as $attribute) {
                $attributeName = $attribute->getName();

                // Check if this is an AsyncAPI attribute
                if ($this->isAsyncApiAttribute($attributeName)) {
                    $classAnnotations[] = [
                        'type' => $this->getAttributeType($attributeName),
                        'attribute' => $attribute->newInstance(),
                        'class' => $className,
                    ];
                }
            }

            if (! empty($classAnnotations)) {
                $this->scannedClasses[$className] = $classAnnotations;
            }

            // Also scan methods for operation annotations
            $this->scanMethods($reflection, $className);
        } catch (\Exception $e) {
            // Skip classes that can't be reflected
        }
    }

    /**
     * Scan class methods for AsyncAPI annotations
     */
    protected function scanMethods(ReflectionClass $reflection, string $className): void
    {
        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes();

            foreach ($attributes as $attribute) {
                $attributeName = $attribute->getName();

                if ($this->isAsyncApiAttribute($attributeName)) {
                    if (! isset($this->scannedClasses[$className])) {
                        $this->scannedClasses[$className] = [];
                    }

                    $this->scannedClasses[$className][] = [
                        'type' => $this->getAttributeType($attributeName),
                        'attribute' => $attribute->newInstance(),
                        'class' => $className,
                        'method' => $method->getName(),
                    ];
                }
            }
        }
    }

    /**
     * Check if an attribute is an AsyncAPI attribute
     */
    protected function isAsyncApiAttribute(string $attributeName): bool
    {
        return str_starts_with($attributeName, 'AsyncApi\\Attributes\\');
    }

    /**
     * Get the type of AsyncAPI attribute
     */
    protected function getAttributeType(string $attributeName): string
    {
        $parts = explode('\\', $attributeName);

        return end($parts);
    }

    /**
     * Get all scanned classes
     */
    public function getScannedClasses(): array
    {
        return $this->scannedClasses;
    }

    /**
     * Get classes with a specific attribute type
     */
    public function getClassesByAttributeType(string $type): array
    {
        $result = [];

        foreach ($this->scannedClasses as $className => $annotations) {
            foreach ($annotations as $annotation) {
                if ($annotation['type'] === $type) {
                    $result[$className][] = $annotation;
                }
            }
        }

        return $result;
    }
}
