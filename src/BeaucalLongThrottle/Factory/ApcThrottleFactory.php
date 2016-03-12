<?php

namespace BeaucalLongThrottle\Factory;

use BeaucalLongThrottle\Service\Throttle;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * A shortcut for APC adapter, rather than modifying throttle config.
 */
class ApcThrottleFactory implements FactoryInterface {

    public function createService(ServiceLocatorInterface $sm) {
        $adapterClass = 'BeaucalLongThrottle\Adapter\Apc';
        $options = clone $sm->get('BeaucalLongThrottle\Options\Throttle');
        $options->setAdapterClass($adapterClass);
        return new Throttle(
        $sm->get($options->getAdapterClass()), $options
        );
    }

}
