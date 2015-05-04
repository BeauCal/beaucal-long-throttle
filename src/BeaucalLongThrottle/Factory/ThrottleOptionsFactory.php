<?php

namespace BeaucalLongThrottle\Factory;

use BeaucalLongThrottle\Options\Throttle as ThrottleOptions;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class ThrottleOptionsFactory implements FactoryInterface {

    public function createService(ServiceLocatorInterface $serviceLocator) {
        $config = $serviceLocator->get('Config');
        $key = 'beaucallongthrottle';
        return new ThrottleOptions(
        isset($config[$key]['throttle']) ? $config[$key]['throttle'] : []
        );
    }

}
