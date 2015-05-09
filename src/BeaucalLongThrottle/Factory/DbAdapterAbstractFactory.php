<?php

namespace BeaucalLongThrottle\Factory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\TableGateway\TableGateway;
use BeaucalLongThrottle\Factory\LockHandleFactory;

class DbAdapterAbstractFactory implements AbstractFactoryInterface {

    const CONFIG_KEY = 'beaucallongthrottle';

    protected $configBootstrap = [
        'BeaucalLongThrottle\Adapter\Db' => [
            'options_class' => 'BeaucalLongThrottle\Options\DbAdapter'
        ],
        'BeaucalLongThrottle\Adapter\DbMultiple' => [
            'options_class' => 'BeaucalLongThrottle\Options\DbMultipleAdapter'
        ],
    ];

    /**
     * @var array
     */
    protected $config;

    /**
     * @param ServiceLocatorInterface $services
     * @param string $name
     * @param AdapterInterface $requestedName
     * @return boolean
     */
    public function canCreateServiceWithName(
    ServiceLocatorInterface $services, $name, $requestedName
    ) {
        $config = $this->getConfig($services);

        $optionsClass = isset($config[$requestedName]['options_class']) ?
        $config[$requestedName]['options_class'] : null;
        if (!is_subclass_of($optionsClass, 'Zend\Stdlib\AbstractOptions')) {
            // @codeCoverageIgnoreStart
            return false;
        }
        // @codeCoverageIgnoreEnd

        return is_subclass_of(
        $requestedName, 'BeaucalLongThrottle\Adapter\AdapterInterface'
        );
    }

    /**
     * @param  ServiceLocatorInterface              $services
     * @param  string                               $name
     * @param  string                               $requestedName
     * @return AdapterInterface
     */
    public function createServiceWithName(
    ServiceLocatorInterface $services, $name, $requestedName
    ) {
        $config = $this->getConfig($services)[$requestedName];
        $optionsClass = $config['options_class'];
        unset($config['options_class']);

        $options = new $optionsClass($config);
        $gateway = new TableGateway(
        $options->getDbTable(), $services->get($options->getDbAdapterClass())
        );
        return new $requestedName($gateway, $options, new LockHandleFactory);
    }

    /**
     * @param  ServiceLocatorInterface $services
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $services) {
        if (is_array($this->config)) {
            return $this->config;
        }

        $config = $services->has('Config') ? $services->get('Config') : [];
        if (isset($config[self::CONFIG_KEY])) {
            $this->config = $config[self::CONFIG_KEY];
        } else {
            $this->config = $this->configBootstrap;
        }
        return $this->config;
    }

}
