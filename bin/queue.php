#!/usr/bin/env php
<?php

/**
 * Launches the queue, will be restarted by Supervisor when it finishes
 *
 * @todo Create an integration test for the queue
 */

use Proximate\Service\File;
use Proximate\Service\SiteFetcher as SiteFetcherService;
use Proximate\Queue\Read as QueueReader;

$root = realpath(__DIR__ . '/..');
require_once $root . '/vendor/autoload.php';

$actions = getopt('p:q:', ['proxy-address:', 'queue-path:']);

$queuePath = isset($actions['queue-path']) ? $actions['queue-path'] : (isset($actions['q']) ? $actions['q'] : null);
$proxyAddress = isset($actions['proxy-address']) ? $actions['proxy-address'] : (isset($actions['p']) ? $actions['p'] : null);

if (!$queuePath || !$proxyAddress)
{
    $command = __FILE__;
    die(
        sprintf("Syntax: %s --proxy-address <proxy:port> --queue-path <queue-path>\n", $command)
    );
}

if (!file_exists($queuePath))
{
    die(
        sprintf("Error: the supplied queue path `%s` does not exist\n", $queuePath)
    );
}

$queue = new QueueReader($queuePath, new File());
$queue->
    setFetcher(new SiteFetcherService($proxyAddress))->
    process();
