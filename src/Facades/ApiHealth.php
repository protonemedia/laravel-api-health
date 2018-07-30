<?php

namespace Pbmedia\ApiHealth\Facades;

use Illuminate\Support\Facades\Facade;
use Pbmedia\ApiHealth\Testing\ApiHealthFake;

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

    /**
     * Replace the bound instance with a fake.
     *
     * @return \Pbmedia\ApiHealth\Testing\ApiHealthFake
     */
    public static function fake()
    {
        return tap(new ApiHealthFake, function ($fake) {
            static::swap($fake);
        });
    }
}
