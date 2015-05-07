<?php

namespace BeaucalLongThrottleTest;

use BeaucalLongThrottle\Module;

/**
 * @group beaucal_throttle
 */
class ModuleTest extends \PHPUnit_Framework_TestCase {

    protected $module;

    public function setUp() {
        parent::setUp();

        $this->module = new Module;
    }

    public function testGetConfig() {
        $config = $this->module->getConfig();
        $this->assertTrue(isset($config['service_manager']));
    }

    public function testGetAutoloaderConfig() {
        $config = $this->module->getAutoloaderConfig();
        $namespace = $config['Zend\Loader\StandardAutoloader']['namespaces'];
        $this->assertEquals('BeaucalLongThrottle', key($namespace));
        $this->assertRegExp('#/BeaucalLongThrottle#', current($namespace));
    }

}
