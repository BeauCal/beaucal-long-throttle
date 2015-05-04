<?php

namespace BeaucalLongThrottle\Factory;

use BeaucalLongThrottle\Service\Throttle;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class ThrottleFactory implements FactoryInterface {

    public function createService(ServiceLocatorInterface $serviceLocator) {
        $options = $serviceLocator->get('BeaucalLongThrottle\Options\Throttle');
        return new Throttle(
        $serviceLocator->get($options->getAdapterClass()), $options
        );
    }

}
