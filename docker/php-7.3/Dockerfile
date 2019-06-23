FROM php:7.3-cli

RUN apt update \
    && apt install --assume-yes git unzip \
    && rm -rf /var/lib/apt/lists/* \
    \
    && curl --output composer-setup.php https://getcomposer.org/installer \
    && php composer-setup.php --install-dir /usr/local/bin --filename composer \
    && rm composer-setup.php

WORKDIR /app
