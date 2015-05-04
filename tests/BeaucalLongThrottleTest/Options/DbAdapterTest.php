<?php

namespace BeaucalLongThrottleTest\Options;

use BeaucalLongThrottle\Options\DbAdapter as DbAdapterOptions;

/**
 * @group beaucal_throttle
 */
class DbAdapterTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var DbAdapterOptions
     */
    protected $options;

    public function setUp() {
        parent::setUp();

        $this->options = new DbAdapterOptions;
    }

    public function testDefaults() {
        $defaults = [
            'use_transactions' => true,
            'db_adapter_class' => 'Zend\Db\Adapter\Adapter',
            'db_date_time_format' => 'Y-m-d H:i:s',
            'db_table' => 'beaucal_throttle',
            'clear_all_is_cheap' => true,
        ];
        foreach ($defaults as $property => $expected) {
            $this->assertEquals($expected, $this->options->{$property});
        }
    }

    public function testSetters() {
        $overrides = [
            'use_transactions' => false,
            'db_adapter_class' => 'another',
            'db_date_time_format' => 'another',
            'db_table' => 'another',
            'clear_all_is_cheap' => false,
        ];
        foreach ($overrides as $property => $override) {
            $this->options->{$property} = $override;
            $this->assertEquals($override, $this->options->{$property});
        }
    }

}
