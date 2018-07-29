<?php

namespace Pbmedia\ApiHealth\Checkers;

use Spatie\SslCertificate\Downloader;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;

abstract class AbstractSslCertificateChecker extends AbstractChecker
{
    /**
     * The hostname that must be checked.
     *
     * @var string
     */
    protected $hostname;

    /**
     * The port to request the certificate on.
     *
     * @var string
     */
    protected $port = 443;

    /**
     * Creates a new instance of this checker with a Ssl Certificate Downloader.
     *
     * @param \Spatie\SslCertificate\Downloader $downloader
     */
    public function __construct(Downloader $downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * Requests the URL and handles any thrown exceptions.
     *
     * @return null
     */
    public function run()
    {
        try {
            $certificate = $this->downloader->usingPort($this->port)->forHost($this->hostname);
        } catch (InvalidUrl $urlException) {
            throw new CheckerHasFailed("Could not check the Ssl Certificate for \"{$this->hostname}\": {$urlException->getMessage()}");
        } catch (CouldNotDownloadCertificate $downloadException) {
            throw new CheckerHasFailed("Could not download the Ssl Certificate for \"{$this->hostname}\": {$downloadException->getMessage()}");
        }

        if ($certificate->isValid()) {
            return;
        }

        throw new CheckerHasFailed("The Ssl Certificate for \"{$this->hostname}\" is not valid. The expiration date is {$certificate->expirationDate()}.");
    }
}
