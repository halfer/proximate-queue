<?php

/**
 * Base for unit tests
 */

namespace Proximate\Test;

use Mockery;
use Proximate\Service\File as FileService;
use Proximate\Queue\Base as Queue;

abstract class QueueTestBase extends \PHPUnit_Framework_TestCase
{
    const DUMMY_DIR = '/any/dir';
    const DUMMY_URL = 'http://example.com/';
    const DUMMY_HASH = 'a6bf1757fff057f266b697df9cf176fd';
    const DUMMY_TIMESTAMP = 'Fri, 05 May 2017 09:07:19 +0100';

    protected $fileService;

    protected function setUp()
    {
        $this->fileService = \Mockery::mock(FileService::class);
    }

    /**
     * Checks that the folder is stored
     *
     * (Runs for both Read and Write queue tests)
     */
    public function testConstructorStoresDirectory()
    {
        $queue = $this->getQueueTestHarness();
        $queue->init($dir = self::DUMMY_DIR, $this->getFileServiceMockWithBasicExpectations());

        $this->assertEquals($dir, $queue->getQueueDir());
    }

    /**
     * Emulates a folder not found error
     *
     * (Runs for both Read and Write queue tests)
     *
     * @expectedException \Proximate\Exception\DirectoryNotFound
     */
    public function testConstructorRejectsBadFolder()
    {
        $queue = $this->getQueueTestHarness();
        $queue->init(self::DUMMY_DIR, $this->getFileServiceMockWithBasicExpectations(false));
    }

    /**
     * Checks that the file service is stored by the init method
     *
     * (Runs for both Read and Write queue tests)
     */
    public function testInitFileService()
    {
        $fileService = $this->getFileServiceMockWithBasicExpectations();
        $queue = $this->getQueueTestHarness($fileService);
        $queue->init(self::DUMMY_DIR, $fileService);
        $this->assertEquals($fileService, $queue->getFileService());
    }

    /**
     * @param string $queueClassName
     * @return Queue|\Mockery\Mock
     */
    protected function getQueueMock($queueClassName)
    {
        $queue = Mockery::mock($queueClassName)->
            shouldAllowMockingProtectedMethods()->
            makePartial();
        $queue->init(self::DUMMY_DIR, $this->getFileServiceMock());

        return $queue;
    }

    protected function getFileServiceMock()
    {
        if (!$this->fileService)
        {
            throw new \Exception(
                "This call needs a file service mock to have been set up"
            );
        }

        return $this->fileService;
    }

    /**
     * Gets a mock class for the file service
     *
     * @param boolean $isDirectory
     * @return \Mockery\Mock|FileService
     */
    protected function getFileServiceMockWithBasicExpectations($isDirectory = true)
    {
        $this->
            getFileServiceMock()->
            shouldReceive('isDirectory')->
            andReturn($isDirectory);

        return $this->getFileServiceMock();
    }

    abstract protected function getQueueTestHarness();

    /**
     * Creates a JSON string representing a cache entry
     *
     * @param string $url
     * @return string
     */
    protected function getCacheEntry($url)
    {
$json = '{
    "url": __URL__,
    "path_regex": null,
    "timestamp_queued": "__TIMESTAMP__"
}';
        $out1 = str_replace('__URL__', json_encode($url), $json);
        $out2 = str_replace('__TIMESTAMP__', self::DUMMY_TIMESTAMP, $out1);

        return $out2;
    }

    protected function getQueueEntryPath($status = Queue::STATUS_READY)
    {
        return self::DUMMY_DIR . '/' . self::DUMMY_HASH . '.' . $status;
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
