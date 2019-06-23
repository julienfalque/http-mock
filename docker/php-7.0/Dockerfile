FROM ubuntu:19.04

RUN apt update \
    && apt install --assume-yes curl git unzip \
    \
    && export DEBIAN_FRONTEND=noninteractive \
    && apt install --assume-yes software-properties-common python3-software-properties \
    && add-apt-repository ppa:ondrej/php \
    && apt update \
    && apt install --assume-yes php7.0-cli php7.0-mbstring php7.0-xml \
    \
    && rm -rf /var/lib/apt/lists/* \
    \
    && curl --output composer-setup.php https://getcomposer.org/installer \
    && php composer-setup.php --install-dir /usr/local/bin --filename composer \
    && rm composer-setup.php

WORKDIR /app
