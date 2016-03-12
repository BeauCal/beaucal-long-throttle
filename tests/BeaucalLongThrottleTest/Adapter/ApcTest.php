<?php

namespace BeaucalLongThrottleTest\Adapter;

use BeaucalLongThrottle\Service\Throttle;
use BeaucalLongThrottle\Adapter\Apc as ApcAdapter;
use BeaucalLongThrottle\Apc\Apc as ApcWrapper;
use BeaucalLongThrottle\Options\ApcAdapter as ApcOptions;
use BeaucalLongThrottle\Options\Throttle as ThrottleOptions;
use BeaucalLongThrottle\Factory\LockHandleFactory;
use BeaucalLongThrottle\Lock;
use BeaucalLongThrottle\Term\DateTimeUnit;
use DateTime;
use Zend\Math\Rand;

/**
 * @group beaucal_throttle
 */
class ApcTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var ApcAdapter
     */
    protected $apcAdapter;

    /**
     * @var Throttle
     */
    protected $throttle;

    public function setUp() {
        parent::setUp();

        $this->apcAdapter = new ApcAdapter(
        new ApcWrapper, new ApcOptions, new LockHandleFactory
        );

        $this->throttle = new Throttle(
        $this->apcAdapter, new ThrottleOptions
        );
    }

    public function testVacuous() {
        $vacuous = ['beginTransaction', 'commit', 'rollback', 'clearExpiredLock'];
        foreach ($vacuous as $method) {
            $this->apcAdapter->$method();
        }
    }

    public function testCreateLockHandleLooped() {
        $factoryMock = $this->getMock('BeaucalLongThrottle\Factory\LockHandleFactory');
        $factoryMock->expects($this->any())
        ->method('createHandle')->will($this->returnValue(new Lock\Handle));

        $apcAdapter = new ApcAdapter(new ApcWrapper, new ApcOptions,
        $factoryMock);

        $throttle = new Throttle($apcAdapter, new ThrottleOptions);

        /**
         * First lock works, second tries to get a new handle but can't.
         */
        $ttl = new DateTimeUnit(80, 'seconds');
        $throttle->takeLock('handleOk', $ttl);
        $this->assertFalse($throttle->takeLock('handleRepeats', $ttl));
    }

    public function testCannotAdd() {
        $apcMock = $this->getMock('BeaucalLongThrottle\Apc\Apc');
        $apcMock->expects($this->any())
        ->method('add')->will($this->returnValue(false));
        $apcAdapter = new ApcAdapter(
        $apcMock, new ApcOptions, new LockHandleFactory
        );
        $throttle = new Throttle($apcAdapter, new ThrottleOptions);

        $ttl = new DateTimeUnit(80, 'seconds');
        for ($i = 0; $i < 4; $i++) {
            $this->assertFalse($throttle->takeLock(__FUNCTION__, $ttl));
        }
    }

    public function testSetLockPast() {
        $result = $this->apcAdapter->setLock('past', new DateTime('2000-01-01'));
    }

    public function testApcExtensionWorking() {
        if (extension_loaded('apc')) {
            echo 'APC EXTENSION: apc' . PHP_EOL;
        }
        if (extension_loaded('apcu')) {
            echo 'APC EXTENSION: apcu' . PHP_EOL;
        }
        $this->assertTrue(extension_loaded('apc') || extension_loaded('apcu'));
        $rand = Rand::getString(10, 'asdf');
        $addMethod = extension_loaded('apcu') ? 'apcu_add' : 'apc_add';
        $this->assertTrue($addMethod($rand, true, 100));
        $this->assertFalse($addMethod($rand, true, 100));
    }

}
