<?php

namespace Pbmedia\ApiHealth\Checkers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractHttpGetChecker extends AbstractChecker
{
    /**
     * The Guzzle HTTP client
     *
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * Guzzle options
     *
     * @var array
     */
    private $guzzleOptions;

    /**
     * The URL that must be requested.
     *
     * @var string
     */
    protected $url;

    /**
     * Creates a new instance of this checker with a Guzzle HTTP client and options.
     *
     * @param \GuzzleHttp\Client $httpClient
     * @param array  $guzzleOptions
     */
    public function __construct(Client $httpClient, array $guzzleOptions = [])
    {
        $this->httpClient    = $httpClient;
        $this->guzzleOptions = $guzzleOptions;
    }

    /**
     * Requests the URL and handles any thrown exceptions.
     *
     * @return null
     */
    public function run()
    {
        try {
            $response = $this->httpClient->get($this->url, $this->guzzleOptions);
        } catch (ClientException $exception) {
            $this->throwExceptionByResponse($exception->getResponse());
        } catch (ConnectException $exception) {
            $this->throwConnectException($exception);
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 200 && $statusCode < 300) {
            return;
        }

        $this->throwExceptionByResponse($response);
    }

    /**
     * Maps a ConnectException into a CheckerHasFailed exception and throws it.
     *
     * @param  \GuzzleHttp\Exception\ConnectException $exception
     *
     * @throws \Pbmedia\ApiHealth\Checkers\CheckerHasFailed
     * @return null
     */
    private function throwConnectException(ConnectException $exception)
    {
        throw new CheckerHasFailed("GET request to \"{$this->url}\" failed, client message: {$exception->getMessage()}");
    }

    /**
     * Maps a Response into a CheckerHasFailed exception and throws it.
     *
     * @param  \Psr\Http\Message\ResponseInterface $exception
     *
     * @throws \Pbmedia\ApiHealth\Checkers\CheckerHasFailed
     * @return null
     */
    private function throwExceptionByResponse(ResponseInterface $response)
    {
        throw new CheckerHasFailed("GET request to \"{$this->url}\" failed, returned status code {$response->getStatusCode()} and reason phrase: \"{$response->getReasonPhrase()}\"");
    }
}
