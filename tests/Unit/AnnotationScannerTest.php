<?php

use Drmmr763\AsyncApi\AnnotationScanner;
use Drmmr763\AsyncApi\Tests\Fixtures\TestAsyncApiSpec;
use Drmmr763\AsyncApi\Tests\Fixtures\TestBroadcastEvent;

describe('AnnotationScanner', function () {
    it('can be instantiated', function () {
        $scanner = new AnnotationScanner([__DIR__]);
        expect($scanner)->toBeInstanceOf(AnnotationScanner::class);
    });

    it('can scan directories for AsyncAPI attributes', function () {
        $scanner = new AnnotationScanner([__DIR__.'/../Fixtures']);
        $annotations = $scanner->scan();

        expect($annotations)->toBeArray()
            ->and($annotations)->not->toBeEmpty();
    });

    it('finds AsyncApi attributes on classes', function () {
        $scanner = new AnnotationScanner([__DIR__.'/../Fixtures']);
        $annotations = $scanner->scan();

        expect($annotations)->toHaveKey(TestAsyncApiSpec::class);

        $classAnnotations = $annotations[TestAsyncApiSpec::class];
        expect($classAnnotations)->toBeArray();

        $hasAsyncApi = false;
        foreach ($classAnnotations as $annotation) {
            if ($annotation['type'] === 'AsyncApi') {
                $hasAsyncApi = true;
                break;
            }
        }

        expect($hasAsyncApi)->toBeTrue();
    });

    it('finds Message attributes on classes', function () {
        $scanner = new AnnotationScanner([__DIR__.'/../Fixtures']);
        $annotations = $scanner->scan();

        expect($annotations)->toHaveKey(TestBroadcastEvent::class);

        $classAnnotations = $annotations[TestBroadcastEvent::class];
        expect($classAnnotations)->toBeArray();

        $hasMessage = false;
        foreach ($classAnnotations as $annotation) {
            if ($annotation['type'] === 'Message') {
                $hasMessage = true;
                break;
            }
        }

        expect($hasMessage)->toBeTrue();
    });

    it('handles empty directories gracefully', function () {
        $tempDir = sys_get_temp_dir().'/asyncapi_test_'.uniqid();
        mkdir($tempDir);

        $scanner = new AnnotationScanner([$tempDir]);
        $annotations = $scanner->scan();

        expect($annotations)->toBeArray()
            ->and($annotations)->toBeEmpty();

        rmdir($tempDir);
    });

    it('handles non-existent directories gracefully', function () {
        $scanner = new AnnotationScanner(['/non/existent/path']);
        $annotations = $scanner->scan();

        expect($annotations)->toBeArray()
            ->and($annotations)->toBeEmpty();
    });

    it('scans multiple directories', function () {
        $scanner = new AnnotationScanner([
            __DIR__.'/../Fixtures',
            __DIR__,
        ]);
        $annotations = $scanner->scan();

        expect($annotations)->toBeArray()
            ->and($annotations)->not->toBeEmpty();
    });

    it('only scans PHP files', function () {
        $tempDir = sys_get_temp_dir().'/asyncapi_test_'.uniqid();
        mkdir($tempDir);
        file_put_contents($tempDir.'/test.txt', 'not a php file');
        file_put_contents($tempDir.'/test.php', '<?php class Test {}');

        $scanner = new AnnotationScanner([$tempDir]);
        $annotations = $scanner->scan();

        // Should not throw errors for non-PHP files
        expect($annotations)->toBeArray();

        unlink($tempDir.'/test.txt');
        unlink($tempDir.'/test.php');
        rmdir($tempDir);
    });

    it('handles classes without attributes', function () {
        $tempDir = sys_get_temp_dir().'/asyncapi_test_'.uniqid();
        mkdir($tempDir);
        file_put_contents($tempDir.'/NoAttributes.php', '<?php class NoAttributes {}');

        $scanner = new AnnotationScanner([$tempDir]);
        $annotations = $scanner->scan();

        expect($annotations)->toBeArray();

        unlink($tempDir.'/NoAttributes.php');
        rmdir($tempDir);
    });
});
