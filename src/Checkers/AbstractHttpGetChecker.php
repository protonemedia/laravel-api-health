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

    public static function create()
    {
        return new static(new Client);
    }

    public function isSuccessful(): bool
    {
        try {
            $response = $this->httpClient->get($this->url, $this->guzzleOptions);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
        } catch (ConnectException $exception) {
            throw new CheckWasUnsuccessful($exception->getMessage());
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 200 && $statusCode < 300) {
            return true;
        }

        throw new CheckWasUnsuccessful($statusCode);
    }
}
