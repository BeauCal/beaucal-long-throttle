<?php

namespace BeaucalLongThrottle\Factory;

use BeaucalLongThrottle\Adapter\Apc as ApcAdapter;
use BeaucalLongThrottle\Apc\Apc as ApcWrapper;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class ApcAdapterFactory implements FactoryInterface {

    const CONFIG_KEY = 'beaucallongthrottle';
    const CLASS_KEY = 'BeaucalLongThrottle\Adapter\Apc';

    protected $configBootstrap = [
        'options_class' => 'BeaucalLongThrottle\Options\ApcAdapter'
    ];

    public function createService(ServiceLocatorInterface $sm) {
        $config = $sm->has('Config') ? $sm->get('Config') : [];
        if (isset($config[self::CONFIG_KEY][self::CLASS_KEY])) {
            $config = $config[self::CONFIG_KEY][self::CLASS_KEY];
        } else {
            $config = $this->configBootstrap;
        }

        $optionsClass = $config['options_class'];
        unset($config['options_class']);

        $options = new $optionsClass($config);
        return new ApcAdapter(
        new ApcWrapper, $options, new LockHandleFactory
        );
    }

}
