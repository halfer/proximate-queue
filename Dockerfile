# A Docker build file for "proximate-queue" crawler queue

FROM alpine:3.5

# Do a system update
RUN apk update

# Install PHP
RUN apk --update add php7

# Composer needs all of 'phpX-openssl phpX-json phpX-phar phpX-mbstring' and 'zlib' is
# recommended
RUN apk --update add openssl php7-openssl php7-json php7-phar php7-mbstring php7-zlib
# Pest needs 'php5-curl', clue/socket-raw requires sockets, Symfony crawler needs dom
RUN apk add php7-curl php7-sockets php7-dom

# Refresh the SSL certs, which seem to be missing
RUN wget -O /etc/ssl/cert.pem https://curl.haxx.se/ca/cacert.pem

# Install Composer
# See https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
COPY install/composer.sh /tmp/composer.sh
RUN chmod u+x /tmp/composer.sh

# Ooh, non-standard PHP binary name
RUN ln -s /usr/bin/php7 /usr/bin/php

# Install Composer
RUN cd /tmp && sh /tmp/composer.sh

# Install dependencies first
COPY composer.json /var/app/
COPY composer.lock /var/app/

# Install deps using Composer (ignore dev deps)
RUN cd /var/app && php /tmp/composer.phar install --no-dev

# Install main body of source code after other installations, since this will change more often
COPY src /var/app/src
COPY bin/ /var/app/bin

# Use Supervisor as the entry point
ENTRYPOINT ["sh", "/var/app/bin/container-start.sh"]
