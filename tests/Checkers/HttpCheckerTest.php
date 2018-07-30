<?php

namespace Pbmedia\ApiHealth\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Mockery;
use Pbmedia\ApiHealth\Checkers\AbstractHttpChecker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpCheckerTest extends TestCase
{
    /** @test */
    public function it_doesnt_throw_an_exception_whenever_the_status_code_is_in_the_200_range()
    {
        $httpResponse = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getStatusCode')
            ->andReturn(200)
            ->getMock();

        $http = Mockery::mock(Client::class)
            ->shouldReceive('get')
            ->with('https://pascalbaljetmedia.com', [])
            ->andReturn($httpResponse)
            ->getMock();

        $checker = new class($http) extends AbstractHttpChecker
        {
            protected $url = 'https://pascalbaljetmedia.com';

            public static function create()
            {}
        };

        $this->assertNull($checker->run());
    }

    /** @test */
    public function it_throws_an_exception_whenever_the_status_is_not_in_the_200_range()
    {
        $httpResponse = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getStatusCode')
            ->andReturn(404)
            ->getMock()
            ->shouldReceive('getReasonPhrase')
            ->andReturn('Not Found')
            ->getMock();

        $httpException = Mockery::mock(ClientException::class)
            ->shouldReceive('getResponse')
            ->andReturn($httpResponse)
            ->getMock();

        $http = Mockery::mock(Client::class)
            ->shouldReceive('get')
            ->with('https://pascalbaljetmedia.com/invalid-url', [])
            ->andThrow($httpException)
            ->getMock();

        $checker = new class($http) extends AbstractHttpChecker
        {
            protected $url = 'https://pascalbaljetmedia.com/invalid-url';

            public static function create()
            {}
        };

        try {
            $checker->run();
            $this->fail("Checker did not throw an exception");
        } catch (CheckerHasFailed $e) {
            $this->assertEquals(
                "GET request to \"https://pascalbaljetmedia.com/invalid-url\" failed, returned status code 404 and reason phrase: \"Not Found\"",
                $e->getMessage()
            );
        }
    }

    /** @test */
    public function it_throws_an_exception_whenever_the_url_cannot_be_resolved()
    {
        $httpException = new ConnectException(
            'cURL error 6: Could not resolve host: pascalbaljetmedia.be (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)',
            Mockery::mock(RequestInterface::class)
        );

        $http = Mockery::mock(Client::class)
            ->shouldReceive('get')
            ->with('https://pascalbaljetmedia.be', [])
            ->andThrow($httpException)
            ->getMock();

        $checker = new class($http) extends AbstractHttpChecker
        {
            protected $url = 'https://pascalbaljetmedia.be';

            public static function create()
            {}
        };

        try {
            $checker->run();
            $this->fail("Checker did not throw an exception");
        } catch (CheckerHasFailed $e) {
            $this->assertEquals(
                "GET request to \"https://pascalbaljetmedia.be\" failed, client message: cURL error 6: Could not resolve host: pascalbaljetmedia.be (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)",
                $e->getMessage()
            );
        }
    }
}
