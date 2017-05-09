#!/bin/sh

php \
    /var/app/bin/queue.php \
    --queue-path /var/proximate/queue \
    --proxy-address ${PROXY_ADDRESS}
