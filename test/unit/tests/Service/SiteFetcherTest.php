<?php

/**
 * Tests for the wget service class
 */

namespace Proximate\Test;

use Proximate\SimpleCrawler;
use Proximate\Service\SiteFetcher;

class SiteFetcherTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_URL = 'http://example.com/';
    const DUMMY_PROXY = '127.0.0.1:8082';

    /**
     * Simple test to ensure fluent interface is working and called
     */
    public function testInitCrawler()
    {
        $fetcher = $this->getFetcherService();
        $fetcher->execute(self::DUMMY_URL, '#.*#');
    }

    public function testCrawlerFailsGracefully()
    {
        $this->markTestIncomplete();
    }

    /**
     * Gets class/mock instance
     *
     * @return SiteFetcher|\Mockery\Mock
     */
    protected function getFetcherService()
    {
        // Set up crawler first
        $crawler = \Mockery::mock(SimpleCrawler::class);
        $crawler->
            shouldReceive('init')->
            once()->
            andReturn($crawler)->
            shouldReceive('crawl')->
            once();

        $siteFetcher = \Mockery::mock(SiteFetcher::class)->
            makePartial()->
            shouldAllowMockingProtectedMethods();
        $siteFetcher->setProxyAddress(self::DUMMY_PROXY);
        $siteFetcher->
            shouldReceive('createSimpleCrawler')->
            once()->
            andReturn($crawler);

        return $siteFetcher;
    }

    public function tearDown()
    {
        \Mockery::close();
    }
}
