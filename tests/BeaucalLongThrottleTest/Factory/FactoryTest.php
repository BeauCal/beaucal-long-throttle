<?php

namespace BeaucalLongThrottleTest\Factory;

use Zend\Db\Adapter\Adapter as ZendDbAdapter;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config as ServiceManagerConfig;

/**
 * @group beaucal_throttle
 */
class FactoryTest extends \PHPUnit_Framework_TestCase {

    protected $serviceManager;

    /**
     * @var ZendDbAdapter
     */
    protected $adapter;
    protected $configMock = [
        'BeaucalLongThrottle\Adapter\Db' =>
        ['options_class' => 'BeaucalLongThrottle\Options\DbAdapter'],
        'BeaucalLongThrottle\Adapter\DbMultiple' =>
        ['options_class' => 'BeaucalLongThrottle\Options\DbMultipleAdapter'],
        'BeaucalLongThrottle\Adapter\Apc' =>
        ['options_class' => 'BeaucalLongThrottle\Options\ApcAdapter']
    ];

    public function setUp() {
        parent::setUp();
        $config = include __DIR__ . '/../../../config/module.config.php';
        $this->serviceManager = new ServiceManager(
        new ServiceManagerConfig($config['service_manager'])
        );

        $this->serviceManager->setFactory('Zend\Db\Adapter\Adapter',
        function($sm) {
            return $this->getAdapter();
        });

        $this->serviceManager->setFactory('Config',
        function($sm) {
            return [
                'beaucallongthrottle' => $this->configMock
            ];
        });
    }

    protected function getAdapter() {
        if ($this->adapter) {
            return $this->adapter;
        }
        $config = include __DIR__ . '/../../dbadapter.php';
        $config = $config['db'];
        $config['driver'] = 'PDO';
        $this->adapter = new ZendDbAdapter($config);
        return $this->adapter;
    }

    public function testConfigFactory() {
        $config = $this->serviceManager->get('beaucallongthrottle_config');
        $this->assertEquals($this->configMock, $config);
    }

    public function testThrottleFactory() {
        $class = 'BeaucalLongThrottle\Service\Throttle';
        $instance = $this->serviceManager->get($class);
        $this->assertInstanceOf($class, $instance);

        /**
         * Alias.
         */
        $this->assertSame(
        $instance, $this->serviceManager->get('BeaucalLongThrottle')
        );
    }

    public function testDbAdapterFactory() {
        $class = 'BeaucalLongThrottle\Adapter\Db';
        $instance = $this->serviceManager->get($class);
        $this->assertInstanceOf($class, $instance);
    }

    public function testDbMultipleAdapterFactory() {
        $class = 'BeaucalLongThrottle\Adapter\DbMultiple';
        $instance = $this->serviceManager->get($class);
        $this->assertInstanceOf($class, $instance);
    }

    public function testApcAdapterFactory() {
        $class = 'BeaucalLongThrottle\Adapter\Apc';
        $instance = $this->serviceManager->get($class);
        $this->assertInstanceOf($class, $instance);
    }

    public function testWithoutFactoryConfig() {
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setFactory('Config',
        function($sm) {
            return [];
        });

        $class = 'BeaucalLongThrottle\Service\Throttle';
        $instance = $this->serviceManager->get($class);
        $this->assertInstanceOf($class, $instance);
    }

    public function testWithoutFactoryConfigApc() {
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setFactory('Config',
        function($sm) {
            return [];
        });
        $optionsKey = 'BeaucalLongThrottle\Options\Throttle';
        $options = $this->serviceManager->get($optionsKey);
        $options->setAdapterClass('BeaucalLongThrottle\Adapter\Apc');
        $this->serviceManager->setService($optionsKey, $options);

        $class = 'BeaucalLongThrottle\Service\Throttle';
        $instance = $this->serviceManager->get($class);
        $this->assertInstanceOf($class, $instance);
    }

    public function testWithoutFactoryConfigApcThrottle() {
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setFactory('Config',
        function($sm) {
            return [];
        });
        $optionsKey = 'BeaucalLongThrottle\Options\Throttle';
        $options = $this->serviceManager->get($optionsKey);
        $this->serviceManager->setService($optionsKey, $options);

        $class = 'BeaucalLongThrottle_APC';
        $instance = $this->serviceManager->get($class);
        $this->assertEquals(
        'BeaucalLongThrottle\Adapter\Apc',
        $instance->getOptions()->getAdapterClass()
        );
    }

}
