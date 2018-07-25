<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Pbmedia\ApiHealth\LaravelFacade
 */
class LaravelFacade extends Facade
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
