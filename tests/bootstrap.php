<?php

if (file_exists('../vendor/autoload.php')) {
    include '../vendor/autoload.php';
} elseif (file_exists('../../../autoload.php')) {
    include '../../../autoload.php';
} else {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}
