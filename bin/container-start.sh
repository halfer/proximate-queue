#!/bin/sh

php \
    /var/app/bin/queue.php \
    --queue-path /remote/queue \
    --proxy-address ${PROXY_ADDRESS}
