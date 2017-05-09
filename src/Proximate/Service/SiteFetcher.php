<?php

/**
 * Site fetcher service
 */

namespace Proximate\Service;

use Proximate\Exception\SiteFetch as SiteFetchException;
use Proximate\SimpleCrawler;

class SiteFetcher
{
    protected $proxyAddress;
    protected $lastLog;

    public function __construct($proxyAddress)
    {
        $this->setProxyAddress($proxyAddress);
    }

    public function execute($startUrl, $pathRegex)
    {
        $crawler = $this->createSimpleCrawler();
        $crawler->
            init()->
            crawl($startUrl, $pathRegex);

        // @todo Add logic to work out whether it failed
        if (false)
        {
            throw new SiteFetchException(
                "There was a problem with the site fetch call"
            );
        }
    }

    /**
     * Creates an instance of the crawler wrapper
     *
     * @return SimpleCrawler
     */
    protected function createSimpleCrawler()
    {
        return new SimpleCrawler($this->proxyAddress);
    }

    /**
     * Useful for testing (when the constructor is not called)
     *
     * @param string $proxyAddress
     */
    public function setProxyAddress($proxyAddress)
    {
        $this->proxyAddress = $proxyAddress;
    }
}
