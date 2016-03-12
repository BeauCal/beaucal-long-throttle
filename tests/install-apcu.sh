#!/usr/bin/env bash

# this is helpful to compile extension
sudo apt-get install autoconf

printf "\n" | pecl install apcu-4.0.10

# compile manually, because `pecl install apcu-beta` keep asking questions
# APCU=4.0.2
# get http://pecl.php.net/get/apcu-$APCU.tgz
# tar zxvf apcu-$APCU.tgz
# cd "apcu-${APCU}"
# phpize && ./configure && make install && echo "Installed ext/apcu-${APCU}"