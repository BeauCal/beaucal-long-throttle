#!/usr/bin/env bash

# this is helpful to compile extension
sudo apt-get install autoconf

if [ "$TRAVIS_PHP_VERSION" == "7.0" ]; then
    printf "\n" | pecl install apcu
else
	printf "\n" | pecl install apcu-4.0.10
fi
