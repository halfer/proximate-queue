<?php

/**
 * Common base for all queue classes
 */

namespace Proximate\Queue;

use Proximate\Service\File as FileService;
use Proximate\Exception\DirectoryNotFound as DirectoryNotFoundException;

class Base
{
    const STATUS_READY = 'ready';
    const STATUS_DOING = 'doing';
    const STATUS_DONE = 'done';
    const STATUS_INVALID = 'invalid'; // Bad JSON
    const STATUS_ERROR = 'error'; // Fetch failed

    protected $queuePath;
    protected $fileService;

    /**
     * Constructor
     *
     * @param string $queuePath
     * @param FileService $fileService
     */
    public function __construct($queuePath, FileService $fileService)
    {
        $this->init($queuePath, $fileService);
    }

    /**
     * Mockable version of the c'tor
     *
     * @param string $queuePath
     * @param FileService $fileService
     * @throws DirectoryNotFoundException
     */
    protected function init($queuePath, FileService $fileService)
    {
        if (!$fileService->isDirectory($queuePath))
        {
            throw new DirectoryNotFoundException(
                "The supplied queue directory does not exist"
            );
        }

        $this->queuePath = $queuePath;
        $this->fileService = $fileService;
    }

    protected function getQueueEntryName($url, $pathRegex, $status = self::STATUS_READY)
    {
        return $this->calculateUrlHash($url . $pathRegex) . '.' . $status;
    }

    protected function calculateUrlHash($url)
    {
        return md5($url);
    }

    public function getQueuePath()
    {
        return $this->queuePath;
    }

    /**
     * Returns the current file service injected into the queue
     *
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->fileService;
    }
}
