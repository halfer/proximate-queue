#!/usr/bin/env php
<?php

/**
 * Runs the queue for 100 seconds (I use Docker Composer to restart it)
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

echo sprintf(
    "Starting queue watcher (path=%s, proxying to %s)\n",
    $queuePath,
    $proxyAddress
);

$queue = new QueueReader($queuePath, new File());
$queue->
    setFetcher(new SiteFetcherService($proxyAddress))->
    process();
