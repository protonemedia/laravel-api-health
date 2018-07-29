<?php

namespace Pbmedia\ApiHealth\Tests;

use Illuminate\Support\Carbon;
use Mockery;
use Pbmedia\ApiHealth\Checkers\AbstractSslCertificateChecker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;
use PHPUnit\Framework\TestCase;
use Spatie\SslCertificate\Downloader;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;
use Spatie\SslCertificate\Exceptions\InvalidUrl;
use Spatie\SslCertificate\SslCertificate;

class SslCertificateCheckerTest extends TestCase
{
    /** @test */
    public function it_doesnt_throw_an_exception_whenever_the_certificate_is_valid()
    {
        $ssl = Mockery::mock(SslCertificate::class)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->getMock();

        $downloader = Mockery::mock(Downloader::class)
            ->shouldReceive('usingPort')
            ->with(4443)
            ->andReturnSelf()
            ->shouldReceive('forHost')
            ->with('pascalbaljetmedia.com')
            ->andReturn($ssl)
            ->getMock();

        $checker = new class($downloader) extends AbstractSslCertificateChecker
        {
            protected $hostname = 'pascalbaljetmedia.com';
            protected $port     = 4443;

            public static function create()
            {}
        };

        $this->assertNull($checker->run());
    }

    /** @test */
    public function it_throws_an_exception_whenever_the_certificate_is_invalid()
    {
        $ssl = Mockery::mock(SslCertificate::class)
            ->shouldReceive('isValid')
            ->andReturn(false)
            ->shouldReceive('expirationDate')
            ->andReturn(Carbon::parse('2018-07-01 20:00:00'))
            ->getMock();

        $downloader = Mockery::mock(Downloader::class)
            ->shouldReceive('usingPort')
            ->with(443)
            ->andReturnSelf()
            ->shouldReceive('forHost')
            ->with('pascalbaljetmedia.com')
            ->andReturn($ssl)
            ->getMock();

        $checker = new class($downloader) extends AbstractSslCertificateChecker
        {
            protected $hostname = 'pascalbaljetmedia.com';

            public static function create()
            {}
        };

        try {
            $checker->run();
            $this->fail("Checker did not throw an exception");
        } catch (CheckerHasFailed $e) {
            $this->assertEquals(
                "The Ssl Certificate for \"pascalbaljetmedia.com\" is not valid. The expiration date is 2018-07-01 20:00:00.",
                $e->getMessage()
            );
        }
    }

    /** @test */
    public function it_throws_an_exception_if_the_url_doesnt_exists()
    {
        $exception = InvalidUrl::couldNotValidate('pascalbaljetmedia.be');

        $downloader = Mockery::mock(Downloader::class)
            ->shouldReceive('usingPort')
            ->with(443)
            ->andReturnSelf()
            ->shouldReceive('forHost')
            ->with('pascalbaljetmedia.be')
            ->andThrow($exception)
            ->getMock();

        $checker = new class($downloader) extends AbstractSslCertificateChecker
        {
            protected $hostname = 'pascalbaljetmedia.be';

            public static function create()
            {}
        };

        try {
            $checker->run();
            $this->fail("Checker did not throw an exception");
        } catch (CheckerHasFailed $e) {
            $this->assertEquals(
                "Could not check the Ssl Certificate for \"pascalbaljetmedia.be\": String `pascalbaljetmedia.be` is not a valid url.",
                $e->getMessage()
            );
        }
    }

    /** @test */
    public function it_throws_an_exception_if_the_certificate_could_not_be_downloaded()
    {
        $exception = CouldNotDownloadCertificate::hostDoesNotExist('pascalbaljetmedia.be');

        $downloader = Mockery::mock(Downloader::class)
            ->shouldReceive('usingPort')
            ->with(443)
            ->andReturnSelf()
            ->shouldReceive('forHost')
            ->with('pascalbaljetmedia.be')
            ->andThrow($exception)
            ->getMock();

        $checker = new class($downloader) extends AbstractSslCertificateChecker
        {
            protected $hostname = 'pascalbaljetmedia.be';

            public static function create()
            {}
        };

        try {
            $checker->run();
            $this->fail("Checker did not throw an exception");
        } catch (CheckerHasFailed $e) {
            $this->assertEquals(
                "Could not download the Ssl Certificate for \"pascalbaljetmedia.be\": The host named `pascalbaljetmedia.be` does not exist.",
                $e->getMessage()
            );
        }
    }
}
