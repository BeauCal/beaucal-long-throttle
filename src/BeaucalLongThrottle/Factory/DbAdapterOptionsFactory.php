<?php

namespace BeaucalLongThrottle\Factory;

use BeaucalLongThrottle\Options\DbAdapter as DbAdapterOptions;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class DbAdapterOptionsFactory implements FactoryInterface {

    public function createService(ServiceLocatorInterface $serviceLocator) {
        $config = $serviceLocator->get('Config');
        $key1 = 'beaucallongthrottle';
        $key2 = 'BeaucalLongThrottle\Adapter\Db';
        return new DbAdapterOptions(
        isset($config[$key1][$key2]) ? $config[$key1][$key2] : []
        );
    }

}
