<?php

namespace BeaucalLongThrottleTest\Options;

use BeaucalLongThrottle\Options\Throttle as ThrottleOptions;

/**
 * @group beaucal_throttle
 */
class ThrottleTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var DbAdapterOptions
     */
    protected $options;

    public function setUp() {
        parent::setUp();

        $this->options = new ThrottleOptions;
    }

    public function testDefaults() {
        $defaults = [
            'adapter_class' => 'BeaucalLongThrottle\Adapter\Db',
            'verify_lock' => true,
        ];
        foreach ($defaults as $property => $expected) {
            $this->assertEquals($expected, $this->options->{$property});
        }
    }

    public function testSetters() {
        $overrides = [
            'adapter_class' => 'another',
            'verify_lock' => false,
        ];
        foreach ($overrides as $property => $override) {
            $this->options->{$property} = $override;
            $this->assertEquals($override, $this->options->{$property});
        }
    }

}
