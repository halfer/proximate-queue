<?php

/**
 * Class to write new entries to the queue
 */

namespace Proximate\Queue;

use Proximate\Exception\AlreadyQueued as AlreadyQueuedException;
use Proximate\Exception\RequiredParam as RequiredParamException;
use Proximate\Exception\QueueWrite as QueueWriteException;

class Write extends Base
{
    protected $url;
    protected $pathRegex;

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Gets the currently set URL string
     *
     * @return string
     * @throws \Exception
     */
    public function getUrl()
    {
        if (!$this->url)
        {
            throw new RequiredParamException("No URL set");
        }

        return $this->url;
    }

    public function setPathRegex($pathRegex)
    {
        $this->pathRegex = $pathRegex;

        return $this;
    }

    public function getPathRegex()
    {
        return $this->pathRegex;
    }


    /**
     * Creates a queue item for the current URL
     *
     * @throws AlreadyQueuedException
     */
    public function queue()
    {
        $this->checkEntryExists();
        $ok = $this->createQueueEntry();

        return $ok;
    }

    /**
     * Checks to see if the current URL is currently queued already
     *
     * @throws AlreadyQueuedException
     */
    protected function checkEntryExists()
    {
        if ($this->getFileService()->fileExists($this->getQueueEntryPath()))
        {
            throw new AlreadyQueuedException(
                "This URL is already queued"
            );
        }
    }

    /**
     * Attempts to create a queue entry, throws exception if it cannot
     */
    protected function createQueueEntry()
    {
        $ok = (bool) $this->getFileService()->filePutContents(
            $this->getQueueEntryPath(),
            json_encode($this->getQueueEntryDetails(), JSON_PRETTY_PRINT)
        );
        if (!$ok)
        {
            throw new QueueWriteException(
                "Writing a new queue entry failed"
            );
        }
    }

    /**
     * Gets the "ready" entry for current URL
     *
     * @return string
     */
    protected function getQueueEntryPath()
    {
        return $this->getQueuePath() . '/' . $this->getQueueEntryName($this->url, $this->pathRegex);
    }

    protected function getQueueEntryDetails()
    {
        return [
            'url' => $this->getUrl(),
            'path_regex' => $this->pathRegex,
            'timestamp_queued' => $this->getTimestamp(),
        ];
    }

    /**
     * Gets date/time in human-readable format
     *
     * @return string
     */
    protected function getTimestamp()
    {
        return date('r');
    }
}
