#!/usr/bin/env bash

if [ "$TRAVIS_PHP_VERSION" == "5.4"  ]; then
    exit 0
fi

# this is helpful to compile extension
sudo apt-get install autoconf

pecl install apcu

# compile manually, because `pecl install apcu-beta` keep asking questions
# APCU=4.0.2
# get http://pecl.php.net/get/apcu-$APCU.tgz
# tar zxvf apcu-$APCU.tgz
# cd "apcu-${APCU}"
# phpize && ./configure && make install && echo "Installed ext/apcu-${APCU}"