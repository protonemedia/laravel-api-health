<?php

namespace Pbmedia\ApiHealth\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Mockery;
use Pbmedia\ApiHealth\Checkers\AbstractHttpGetChecker;
use Pbmedia\ApiHealth\Checkers\CheckWasUnsuccessful;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class HttpGetCheckerTest extends TestCase
{
    /** @test */
    public function it_returns_true_whenever_the_status_code_is_in_the_200_range()
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

        $checker = new class($http) extends AbstractHttpGetChecker
        {
            protected $url = 'https://pascalbaljetmedia.com';
        };

        $this->assertTrue($checker->isSuccessful());
    }

    /** @test */
    public function it_throws_an_exception_whenever_the_status_is_not_in_the_200_range()
    {
        $httpResponse = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getStatusCode')
            ->andReturn(404)
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

        $checker = new class($http) extends AbstractHttpGetChecker
        {
            protected $url = 'https://pascalbaljetmedia.com/invalid-url';
        };

        try {
            $checker->isSuccessful();
            $this->fail("Checker did not throw an exception");
        } catch (CheckWasUnsuccessful $e) {}

        dd($checker);
    }

    /** @test */
    public function it_throws_an_exception_whenever_the_url_cannot_be_resolved()
    {
        $http = Mockery::mock(Client::class)
            ->shouldReceive('get')
            ->with('https://pascalbaljetmedia.be', [])
            ->andThrow(Mockery::mock(ConnectException::class))
            ->getMock();

        $checker = new class($http) extends AbstractHttpGetChecker
        {
            protected $url = 'https://pascalbaljetmedia.be';
        };

        $this->expectException(CheckWasUnsuccessful::class);
        $checker->isSuccessful();
    }
}
