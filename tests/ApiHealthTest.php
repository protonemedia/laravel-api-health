<?php

namespace Pbmedia\ApiHealth\Tests;

use Orchestra\Testbench\TestCase;
use Pbmedia\ApiHealth\Facades\ApiHealth;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingAtEvenTimesChecker;
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

        $this->assertFalse(ApiHealth::fresh()->isFailing(FailOnceChecker::class));
    }

    /** @test */
    public function it_restores_the_cache_setting()
    {
        // two times without cache, third with cache...

        $this->assertTrue(ApiHealth::isFailing(FailingAtEvenTimesChecker::class));
        $this->assertFalse(ApiHealth::fresh()->isFailing(FailingAtEvenTimesChecker::class));
        $this->assertFalse(ApiHealth::isFailing(FailingAtEvenTimesChecker::class));
    }
}
