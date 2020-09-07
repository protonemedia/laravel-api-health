<?php

namespace ProtoneMedia\ApiHealth\Facades;

use Illuminate\Support\Facades\Facade;
use ProtoneMedia\ApiHealth\Testing\ApiHealthFake;

/**
 * @see \ProtoneMedia\ApiHealth\LaravelFacade
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
     * @return \ProtoneMedia\ApiHealth\Testing\ApiHealthFake
     */
    public static function fake()
    {
        return tap(new ApiHealthFake, function ($fake) {
            static::swap($fake);
        });
    }
}
