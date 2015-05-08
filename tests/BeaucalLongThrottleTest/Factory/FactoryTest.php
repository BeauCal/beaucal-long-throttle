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

    const CONFIG_MOCK = [
        'BeaucalLongThrottle\Adapter\Db' =>
        ['options_class' => 'BeaucalLongThrottle\Options\DbAdapter'],
        'BeaucalLongThrottle\Adapter\DbMultiple' =>
        ['options_class' => 'BeaucalLongThrottle\Options\DbMultipleAdapter']
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
                'beaucallongthrottle' => self::CONFIG_MOCK
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
        $this->assertEquals(self::CONFIG_MOCK, $config);
    }

    public function testThrottleFactory() {
        $class = 'BeaucalLongThrottle\Service\Throttle';
        $options = $this->serviceManager->get($class);
        $this->assertInstanceOf($class, $options);

        /**
         * Alias.
         */
        $this->assertSame(
        $options, $this->serviceManager->get('BeaucalLongThrottle')
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

}
