<?php

namespace Pbmedia\ApiHealth\Checkers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

abstract class AbstractHttpGetChecker implements Checker
{
    private $httpClient;
    private $guzzleOptions;
    protected $url;

    public function __construct(Client $httpClient, array $guzzleOptions = [])
    {
        $this->httpClient    = $httpClient;
        $this->guzzleOptions = $guzzleOptions;
    }

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

    private function throwConnectException($exception)
    {
        throw new CheckerHasFailed("GET request to \"{$this->url}\" failed, client message: {$exception->getMessage()}");
    }

    private function throwExceptionByResponse($response)
    {
        throw new CheckerHasFailed("GET request to \"{$this->url}\" failed, returned status code {$response->getStatusCode()} and reason phrase: \"{$response->getReasonPhrase()}\"");
    }
}
