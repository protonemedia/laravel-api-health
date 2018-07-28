<?php

namespace Pbmedia\ApiHealth\Tests;

use Orchestra\Testbench\TestCase;
use Pbmedia\ApiHealth\Facades\ApiHealth;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailOnceChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\PassOnceChecker;

class ApiHealthTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('api-health.cache_driver', 'array');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Pbmedia\ApiHealth\LaravelServiceProvider::class,
        ];
    }

    /** @test */
    public function it_uses_the_storage_the_determinate_the_state()
    {
        $this->assertTrue(ApiHealth::isPassing(PassOnceChecker::class));
        $this->assertTrue(ApiHealth::isPassing(PassOnceChecker::class));
    }

    /** @test */
    public function it_can_disable_the_caching()
    {
        $this->assertTrue(ApiHealth::isFailing(FailOnceChecker::class));
        $this->assertTrue(ApiHealth::isFailing(FailOnceChecker::class));

        $this->assertFalse(ApiHealth::withoutCache()->isFailing(FailOnceChecker::class));
    }
}
