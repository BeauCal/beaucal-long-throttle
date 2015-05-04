<?php

namespace BeaucalLongThrottle\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class ConfigFactory implements FactoryInterface {

    const CONFIG_KEY = 'beaucallongthrottle';

    public function createService(ServiceLocatorInterface $serviceLocator) {
        $config = $serviceLocator->get('Config');
        return isset($config[self::CONFIG_KEY]) ? $config[self::CONFIG_KEY] : [];
    }

}
