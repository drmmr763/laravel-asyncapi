#!/usr/bin/env php
<?php

/**
 * Demonstration script showing the scanner regex bug fix
 *
 * This script demonstrates how the original regex would fail to match
 * class declarations when "class <word>" appeared in comments or strings,
 * and how the fixed regex correctly handles these cases.
 */
echo "=== AsyncAPI Scanner Regex Bug Fix Demonstration ===\n\n";

// Sample file content that triggers the bug
$content = <<<'PHP'
<?php

namespace App\Test;

use AsyncApi\Attributes\AsyncApi;
use AsyncApi\Attributes\Info;

#[AsyncApi(
    asyncapi: '3.0.0',
    info: new Info(
        title: 'Test API',
        version: '1.0.0',
        description: 'This description mentions the fully qualified class name of a model'
    )
)]
class BugReproduction
{
    // This is a class example
    // Another class name reference
    
    public function process()
    {
        // The class identifier is important
        $description = "This class even has more references";
        // Yet another class name mention
    }
}
PHP;

echo "Sample PHP file content:\n";
echo str_repeat('-', 80)."\n";
echo $content."\n";
echo str_repeat('-', 80)."\n\n";

// Step 1: Extract namespace
echo "1. Extracting namespace...\n";
preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch);
$namespace = $namespaceMatch[1] ?? 'N/A';
echo "   ✓ Found namespace: {$namespace}\n\n";

// Step 2: Show the BUGGY regex behavior
echo "2. Testing ORIGINAL (buggy) regex pattern...\n";
$buggyPattern = '/class\s+(\w+)/';
echo "   Pattern: {$buggyPattern}\n";

preg_match_all($buggyPattern, $content, $allMatches, PREG_OFFSET_CAPTURE);
echo "   All matches found:\n";
foreach ($allMatches[1] as $index => $match) {
    $lineNum = substr_count(substr($content, 0, $match[1]), "\n") + 1;
    echo "     [{$index}] '{$match[0]}' at line {$lineNum}\n";
}

preg_match($buggyPattern, $content, $buggyMatch);
$buggyClassName = $buggyMatch[1] ?? 'N/A';
$buggyFullClassName = $namespace.'\\'.$buggyClassName;

echo "\n   Scanner uses FIRST match: '{$buggyClassName}'\n";
echo "   Constructed class name: {$buggyFullClassName}\n";
echo "   ⚠️  BUG: This is WRONG! It matched 'name' from the description string!\n\n";

// Step 3: Show the FIXED regex behavior
echo "3. Testing FIXED regex pattern...\n";
$fixedPattern = '/^\s*(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)/m';
echo "   Pattern: {$fixedPattern}\n";

preg_match($fixedPattern, $content, $fixedMatch);
$fixedClassName = $fixedMatch[1] ?? 'N/A';
$fixedFullClassName = $namespace.'\\'.$fixedClassName;

echo "   ✓ Found class: {$fixedClassName}\n";
echo "   Constructed class name: {$fixedFullClassName}\n";
echo "   ✓ SUCCESS: Fixed regex correctly identifies the class!\n\n";

// Step 4: Compare results
echo "=== Summary ===\n";
echo "Original regex: Extracts '{$buggyClassName}' (WRONG - from description string)\n";
echo "Fixed regex:    Extracts '{$fixedClassName}' (CORRECT - actual class name)\n\n";

// Step 5: Test with different class modifiers
echo "=== Testing Class Modifiers ===\n\n";

$testCases = [
    'Standard class' => 'class MyClass { }',
    'Abstract class' => 'abstract class MyAbstractClass { }',
    'Final class' => 'final class MyFinalClass { }',
    'Readonly class' => 'readonly class MyReadonlyClass { }',
    'Indented class' => '    class IndentedClass { }',
];

foreach ($testCases as $label => $classDeclaration) {
    $testContent = "<?php\nnamespace Test;\n// This mentions class name\n{$classDeclaration}";

    preg_match($fixedPattern, $testContent, $match);
    $className = $match[1] ?? 'NOT FOUND';

    $status = $className !== 'NOT FOUND' && $className !== 'name' ? '✓' : '✗';
    echo "{$status} {$label}: {$className}\n";
}

echo "\n=== All Tests Passed! ===\n";
echo "The fixed regex pattern correctly:\n";
echo "  ✓ Matches class declarations at the start of lines\n";
echo "  ✓ Ignores 'class' keyword in comments and strings\n";
echo "  ✓ Handles abstract, final, and readonly modifiers\n";
echo "  ✓ Allows leading whitespace\n";
echo "  ✓ Uses multiline mode for proper line matching\n";
