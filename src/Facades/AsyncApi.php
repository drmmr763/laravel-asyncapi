<?php

namespace Drmmr763\AsyncApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Drmmr763\AsyncApi\AsyncApi
 */
class AsyncApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Drmmr763\AsyncApi\AsyncApi::class;
    }
}
