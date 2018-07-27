<?php

namespace Pbmedia\ApiHealth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Pbmedia\ApiHealth\LaravelFacade
 */
class ApiHealth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-api-health';
    }
}
