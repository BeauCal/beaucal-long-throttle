<?php

namespace BeaucalLongThrottle\Factory;

use BeaucalLongThrottle\Options\Throttle as ThrottleOptions;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class ThrottleOptionsFactory implements FactoryInterface {

    const CONFIG_KEY = 'throttle';

    public function createService(ServiceLocatorInterface $serviceLocator) {
        $config = $serviceLocator->get('beaucallongthrottle_config');
        return new ThrottleOptions(
        isset($config[self::CONFIG_KEY]) ? $config[self::CONFIG_KEY] : []
        );
    }

}
