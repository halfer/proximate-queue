#!/bin/sh
#
# @todo The queue path and proxy address should probably be env vars

php \
	/var/app/bin/queue.php \
	--queue-path /var/proximate/queue \
	--proxy-address proximate-proxy:8081
