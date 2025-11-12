<?php

use Drmmr763\AsyncApi\AsyncApi;
use Drmmr763\AsyncApi\Facades\AsyncApi as AsyncApiFacade;

describe('AsyncApi Facade', function () {
    it('resolves to AsyncApi instance', function () {
        $instance = AsyncApiFacade::getFacadeRoot();

        expect($instance)->toBeInstanceOf(AsyncApi::class);
    });

    it('can call scan method through facade', function () {
        $annotations = AsyncApiFacade::scan();

        expect($annotations)->toBeArray();
    });

    it('can call build method through facade', function () {
        $spec = AsyncApiFacade::build();

        expect($spec)->toBeArray();
    });

    it('can call toJson method through facade', function () {
        $json = AsyncApiFacade::toJson();

        expect($json)->toBeString()
            ->and(json_decode($json, true))->toBeArray();
    });

    it('can call toYaml method through facade', function () {
        $yaml = AsyncApiFacade::toYaml();

        expect($yaml)->toBeString();
    });

    it('can call exportToFile method through facade', function () {
        $tempFile = sys_get_temp_dir().'/asyncapi_facade_test_'.uniqid().'.yaml';
        AsyncApiFacade::exportToFile($tempFile, 'yaml');

        expect(file_exists($tempFile))->toBeTrue();

        unlink($tempFile);
    });

    it('returns same instance on multiple calls', function () {
        $instance1 = AsyncApiFacade::getFacadeRoot();
        $instance2 = AsyncApiFacade::getFacadeRoot();

        expect($instance1)->toBe($instance2);
    });
});
