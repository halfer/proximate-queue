<?php

/** 
 * Unit tests for writing to the Queue
 */

namespace Proximate\Test;

use Proximate\Queue\Write as Queue;
use Proximate\Service\File as FileService;

class QueueWriteTest extends QueueTestBase
{
    /**
     * Checks that the folder is stored
     */
    public function testConstructorStoresDirectory()
    {
        $queue = $this->getQueueTestHarness();
        $queue->init($dir = self::DUMMY_DIR, $this->getFileServiceMockWithBasicExpectations());

        $this->assertEquals($dir, $queue->getQueueDir());
    }

    public function testConstructorAllowsGoodFolder()
    {
        $queue = $this->getQueueTestHarness();
        $queue->init(self::DUMMY_DIR, $this->getFileServiceMockWithBasicExpectations());

        $this->assertTrue(true);
    }

    /**
     * Emulates a folder not found error
     *
     * @expectedException \Exception
     */
    public function testConstructorRejectsBadFolder()
    {
        $queue = $this->getQueueTestHarness();
        $queue->init(self::DUMMY_DIR, $this->getFileServiceMockWithBasicExpectations(false));
    }

    public function testUrlStorage()
    {
        // Doesn't need full initialisation
        $queue = new QueueWriteTestHarness();
        $queue->setUrl($url = self::DUMMY_URL);

        $this->assertEquals($url, $queue->getUrl());
    }

    /**
     * Ensure that fetching a URL that is not set results in an error
     *
     * @expectedException \Proximate\Exception\RequiredParam
     */
    public function testGetUrlFailsWithNoUrl()
    {
        $queue = new QueueWriteTestHarness();

        $this->assertEquals(self::DUMMY_URL, $queue->getUrl());
    }

    public function testPathRegexStorage()
    {
        // Doesn't need full initialisation
        $queue = new QueueWriteTestHarness();

        // Test the empty condition first
        $this->assertNull($queue->getPathRegex());

        // Now try the setter
        $regex = ".*(/about/careers/.*)|(/job/.*)";
        $queue->setPathRegex($regex);
        $this->assertEquals($regex, $queue->getPathRegex());
    }

    /**
     * Checks that a new item is written to a mock FS
     */
    public function testNewQueueItemSucceeds()
    {
        $this->checkQueueWrite(false);
    }

    /**
     * Checks that a write permission failure results in an exception
     *
     * @expectedException \Proximate\Exception\QueueWrite
     */
    public function testNewQueueItemWritePermissionIssue()
    {
        $this->checkQueueWrite(true);
    }

    /**
     * Attempts to write to a mock FS
     *
     * @param boolean $writeFail
     */
    public function checkQueueWrite($writeFail)
    {
        $json = $this->getCacheEntry(self::DUMMY_URL);
        $this->initFileServiceMockWithFileExists(false);
        $this->
            getFileServiceMock()->
            shouldReceive('filePutContents')->
            with($this->getQueueEntryPath(), $json)->
            once()->
            andReturn($writeFail ? false : strlen($json));

        $this->createQueueWriteMock()->
            setUrl(self::DUMMY_URL)->
            queue();
    }

    /**
     * Checks that if a queue path exists already, it will fail to create
     *
     * @expectedException Proximate\Exception\AlreadyQueued
     */
    public function testExistingQueueItemFails()
    {
        // Will fail because a queue item exists already
        $this->initFileServiceMockWithFileExists(true);

        $queue = $this->createQueueWriteMock();
        $queue->
            shouldReceive('createQueueEntry')->
            never();

        $queue->
            setUrl(self::DUMMY_URL)->
            queue();
    }

    protected function getQueueTestHarness()
    {
        return new QueueWriteTestHarness();
    }

    /**
     * Gets a mock of the system under test
     *
     * @return \Mockery\Mock|QueueReadTestHarness
     */
    protected function createQueueWriteMock()
    {
        return parent::getQueueMock(QueueWriteTestHarness::class);
    }

    /**
     * Sets up the file service with a fileExists() expectation
     *
     * @param boolean $fileExists
     * @return FileService
     */
    protected function initFileServiceMockWithFileExists($fileExists)
    {
        $this->
            getFileServiceMockWithBasicExpectations()->
            shouldReceive('fileExists')->
            with(self::DUMMY_DIR . '/a6bf1757fff057f266b697df9cf176fd.ready')->
            once()->
            andReturn($fileExists);
    }
}

class QueueWriteTestHarness extends Queue
{
    // Remove the constructor
    public function __construct()
    {
    }

    // Make this public
    public function init($queueDir, FileService $fileService)
    {
        parent::init($queueDir, $fileService);
    }

    // Make this public
    public function getFileService()
    {
        return parent::getFileService();
    }

    /**
     * Overrides a method that produces untestable results
     */
    public function getTimestamp()
    {
        return QueueTestBase::DUMMY_TIMESTAMP;
    }
}
