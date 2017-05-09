<?php

/**
 * Class to read entries from the queue
 */

namespace Proximate\Queue;

use Proximate\Service\SiteFetcher as FetcherService;
use Proximate\Exception\RequiredDependency as RequiredDependencyException;

class Read extends Base
{
    protected $fetcherService;

    public function setFetcher(FetcherService $fetcherService)
    {
        $this->fetcherService = $fetcherService;

        return $this;
    }

    public function process($loop = 50)
    {
        for ($i = 0; $i < $loop; $i++)
        {
            $this->singleIteration();
        }
    }

    protected function singleIteration()
    {
        if ($itemData = $this->getNextQueueItem())
        {
            $this->processQueueItem($itemData);
        }
        else
        {
            $this->sleep();
        }
    }

    /**
     * Returns the data for the next ready item, if one is available
     *
     * Fails silently if an entry is invalid (renames it out of the way)
     *
     * @todo Validate the item contains the right keys
     *
     * @return string
     */
    protected function getNextQueueItem()
    {
        $fileService = $this->getFileService();
        $pattern = $this->getQueueDir() . '/*.' . self::STATUS_READY;
        $files = $fileService->glob($pattern);
        $data = false;

        if ($files) {
            $file = current($files);
            $json = $fileService->fileGetContents($file);
            $data = json_decode($json, true);

            // If the item does not contain JSON, rename it
            if (!$data)
            {
                // Derive the invalid name first
                $newName = preg_replace(
                    '#\.' . self::STATUS_READY . '$#',
                    '.' . self::STATUS_INVALID,
                    $file
                );
                $fileService->rename($file, $newName);
            }
        }

        return $data;
    }

    /**
     * @todo In the case of error it would be nice to send the error to changeItemStatus,
     * which would write it into the JSON queue item if possible
     * @param array $itemData
     */
    protected function processQueueItem(array $itemData)
    {
        $url = $itemData['url'];
        $pathRegex = $itemData['path_regex'];
        $this->changeItemStatus($url, $pathRegex, self::STATUS_READY, self::STATUS_DOING);

        try
        {
            $this->fetchSite($itemData);
            $status = self::STATUS_DONE;
        }
        catch (\Exception $e)
        {
            $jsonPath = $this->getQueueEntryPathForRequest($url, $pathRegex, self::STATUS_DOING);
            $this->setItemErrorMessage($jsonPath, $e->getMessage());
            $status = self::STATUS_ERROR;
        }

        $this->changeItemStatus($url, $pathRegex, self::STATUS_DOING, $status);
    }

    /**
     * Gets the base domain for the specified URL
     *
     * Made public so it is easier to test :)
     *
     * @param string $url
     * @return string
     */
    public function getDomainForUrl($url)
    {
        // Get the base domain from the URL
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        if ($scheme && $host)
        {
            $domain = $scheme . '://' . $host;
            if ($port = parse_url($url, PHP_URL_PORT))
            {
                $domain .= ':' . $port;
            }
        }

        return $domain;
    }

    protected function fetchSite(array $itemData)
    {
        // Call the site fetcher service here
        $this->getSiteFetcherService()->execute(
            $itemData['url'],
            $itemData['path_regex']
        );
    }

    protected function changeItemStatus($url, $pathRegex, $oldStatus, $newStatus)
    {
        $this->getFileService()->rename(
            $this->getQueueEntryPathForRequest($url, $pathRegex, $oldStatus),
            $this->getQueueEntryPathForRequest($url, $pathRegex, $newStatus)
        );
    }

    protected function setItemErrorMessage($itemPath, $message)
    {
        $jsonIn = $this->getFileService()->fileGetContents($itemPath);
        $data = json_decode($jsonIn, true);
        $data['error'] = $message;
        $jsonOut = json_encode($data);
        $this->getFileService()->filePutContents($itemPath, $jsonOut);
    }

    protected function sleep()
    {
        sleep(2);
    }

    /**
     * Gets the currently configured site fetcher
     *
     * @throws RequiredDependencyException
     * @return FetcherService
     */
    protected function getSiteFetcherService()
    {
        if (!$this->fetcherService)
        {
            throw new RequiredDependencyException(
                "The queue read module needs a site fetcher to operate"
            );
        }

        return $this->fetcherService;
    }

    /**
     * Gets an entry for the given URL and status
     *
     * @param string $url
     * @param string $pathRegex
     * @param string $status
     * @return string
     */
    protected function getQueueEntryPathForRequest($url, $pathRegex, $status)
    {
        return $this->getQueueDir() . '/' . $this->getQueueEntryName($url, $pathRegex, $status);
    }
}
