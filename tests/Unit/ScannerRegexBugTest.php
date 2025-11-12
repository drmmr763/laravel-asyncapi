<?php

use Drmmr763\AsyncApi\AnnotationScanner;

describe('Scanner Regex Bug Fix', function () {
    it('handles class keyword in comments and strings before actual class declaration', function () {
        $tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'asyncapi_test_'.uniqid();
        mkdir($tempDir);

        // Create a file that has "class <word>" in comments/strings BEFORE the actual class
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
}
PHP;

        $testFile = $tempDir.DIRECTORY_SEPARATOR.'BugReproduction.php';
        file_put_contents($testFile, $content);

        // Load the class so it exists
        eval(str_replace('<?php', '', $content));

        $scanner = new AnnotationScanner([$tempDir]);
        $result = $scanner->scan();

        // Should find BugReproduction, not "name"
        expect($result)->toHaveKey('App\\Test\\BugReproduction')
            ->and($result)->not->toHaveKey('App\\Test\\name');

        unlink($testFile);
        rmdir($tempDir);
    });

    it('matches class declaration at start of line with multiline mode', function () {
        $content = <<<'PHP'
<?php
namespace Test;
// This mentions class name in a comment
/* Another class reference */
/**
 * The fully qualified class name of the model
 */
class ActualClass {
}
PHP;

        // Test the regex pattern directly
        $pattern = '/^\s*(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)/m';
        preg_match($pattern, $content, $matches);

        expect($matches[1])->toBe('ActualClass')
            ->and($matches[1])->not->toBe('name');
    });

    it('does not match class keyword in docblock comments', function () {
        $content = <<<'PHP'
<?php
namespace Test;
/**
 * This is a class that does something
 * The class name should be extracted correctly
 */
class RealClass {
}
PHP;

        $pattern = '/^\s*(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)/m';
        preg_match($pattern, $content, $matches);

        expect($matches[1])->toBe('RealClass');
    });

    it('does not match class keyword in string literals', function () {
        $content = <<<'PHP'
<?php
namespace Test;
$description = "The class name should be extracted";
$another = 'This mentions class identifier';
class CorrectClass {
}
PHP;

        $pattern = '/^\s*(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)/m';
        preg_match($pattern, $content, $matches);

        expect($matches[1])->toBe('CorrectClass')
            ->and($matches[1])->not->toBe('name')
            ->and($matches[1])->not->toBe('identifier');
    });

    it('handles multiple occurrences of class keyword correctly', function () {
        $content = <<<'PHP'
<?php
namespace Test;
// First mention: class example
/* Second mention: class reference */
$var = "Third mention: class name";
/**
 * Fourth mention: class identifier
 */
class TheActualClass {
    // Fifth mention: class property
}
PHP;

        $pattern = '/^\s*(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)/m';
        preg_match($pattern, $content, $matches);

        // Should match only the actual class declaration
        expect($matches[1])->toBe('TheActualClass');
    });

    it('matches abstract class with modifiers', function () {
        $content = <<<'PHP'
<?php
namespace Test;
// This mentions class name
abstract class AbstractExample {
}
PHP;

        $pattern = '/^\s*(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)/m';
        preg_match($pattern, $content, $matches);

        expect($matches[1])->toBe('AbstractExample');
    });

    it('matches final class with modifiers', function () {
        $content = <<<'PHP'
<?php
namespace Test;
$description = "The class name is important";
final class FinalExample {
}
PHP;

        $pattern = '/^\s*(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)/m';
        preg_match($pattern, $content, $matches);

        expect($matches[1])->toBe('FinalExample');
    });

    it('matches readonly class with modifiers', function () {
        $content = <<<'PHP'
<?php
namespace Test;
/**
 * Mentions class identifier in docs
 */
readonly class ReadonlyExample {
}
PHP;

        $pattern = '/^\s*(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)/m';
        preg_match($pattern, $content, $matches);

        expect($matches[1])->toBe('ReadonlyExample');
    });

    it('handles indented class declarations', function () {
        $content = <<<'PHP'
<?php
namespace Test;
    class IndentedClass {
    }
PHP;

        $pattern = '/^\s*(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)/m';
        preg_match($pattern, $content, $matches);

        expect($matches[1])->toBe('IndentedClass');
    });
});
