<?php

namespace BeaucalLongThrottleTest\Options;

use BeaucalLongThrottle\Options\ApcAdapter as ApcAdapterOptions;

/**
 * @group beaucal_throttle
 */
class ApcAdapterTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var ApcAdapterOptions
     */
    protected $options;

    public function setUp() {
        parent::setUp();

        $this->options = new ApcAdapterOptions;
    }

    public function testDefaults() {
        $defaults = [
            'apc_namespace' => 'beaucal_throttle',
        ];
        foreach ($defaults as $property => $expected) {
            $this->assertEquals($expected, $this->options->{$property});
        }
    }

    public function testSetters() {
        $overrides = [
            'apc_namespace' => 'another',
        ];
        foreach ($overrides as $property => $override) {
            $this->options->{$property} = $override;
            $this->assertEquals($override, $this->options->{$property});
        }
    }

    public function testConfigOverrides() {
        $config = require __DIR__ . '/data/beaucallongthrottle.local.php';
        $config = $config['beaucallongthrottle']['BeaucalLongThrottle\Adapter\Apc'];
        unset($config['options_class']);
        $options = new ApcAdapterOptions($config);
        $this->assertEquals(
        'apc_namespace_another', $options->getApcNamespace()
        );
    }

}
