<?php

namespace BeaucalLongThrottle\Factory;

use BeaucalLongThrottle\Options\DbAdapter as DbAdapterOptions;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class DbAdapterOptionsFactory implements FactoryInterface {

    const CONFIG_KEY = 'BeaucalLongThrottle\Adapter\Db';

    public function createService(ServiceLocatorInterface $serviceLocator) {
        $config = $serviceLocator->get('beaucallongthrottle_config');
        return new DbAdapterOptions(
        isset($config[self::CONFIG_KEY]) ? $config[self::CONFIG_KEY] : []
        );
    }

}
