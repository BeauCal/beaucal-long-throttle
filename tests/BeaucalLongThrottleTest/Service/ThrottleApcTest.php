<?php

namespace BeaucalLongThrottleTest\Service;

use BeaucalLongThrottle\Service\Throttle;
use BeaucalLongThrottle\Adapter\Apc as ApcAdapter;
use BeaucalLongThrottle\Apc\Apc as ApcExt;
use BeaucalLongThrottle\Term\DateTimeUnit;
use BeaucalLongThrottle\Options\ApcAdapter as ApcAdapterOptions;
use BeaucalLongThrottle\Options\Throttle as ThrottleOptions;
use BeaucalLongThrottle\Lock;
use BeaucalLongThrottle\Factory\LockHandleFactory;

/**
 * @group beaucal_throttle
 */
class ThrottleApcTest extends \PHPUnit_Framework_TestCase {

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

        $apcOptions = new ApcAdapterOptions;
        $this->apcAdapter = new ApcAdapter(
        new ApcExt, $apcOptions, new LockHandleFactory
        );

        $throttleOptions = new ThrottleOptions;
        $this->throttle = new Throttle(
        $this->apcAdapter, $throttleOptions
        );
    }

    public function testGetLockSimulate() {
        $key = 'simulate';
        $ttl = new DateTimeUnit(1, 'second');
        $handle = $this->throttle->takeLock($key, $ttl);
        $this->assertInstanceOf('BeaucalLongThrottle\Lock\Handle', $handle);

        $keyApc = "beaucal_throttle::{$key}";
        $this->assertTrue(apc_fetch($keyApc));
        $this->assertFalse($this->throttle->takeLock($key, $ttl));

        /**
         * You'd think you can sleep(1 or 2) then apc_add should work again.
         * But for me apc_add and sleep don't play nice.  So, assume
         * apc_add works and just manually clear to get on with it.
         */
        apc_delete($keyApc);

        $handle = $this->throttle->takeLock($key, $ttl);
        $this->assertInstanceOf('BeaucalLongThrottle\Lock\Handle', $handle);
    }

    public function testTakeAndClearLock() {
        $key = 'take-and-clear';
        $handle = $this->throttle->takeLock(
        $key, new DateTimeUnit(88, 'years')
        );
        $this->assertInstanceOf('BeaucalLongThrottle\Lock\Handle', $handle);
        $keyApc = "beaucal_throttle::{$key}";
        $this->assertTrue(apc_fetch($keyApc));

        $this->throttle->clearLock($handle);
        $this->assertFalse(apc_fetch($keyApc));

        /**
         * And again.
         */
        $this->throttle->clearLock($handle);
        $this->assertFalse(apc_fetch($keyApc));
    }

    public function testClearLockInvalid() {
        $this->throttle->clearLock(new Lock\Handle);
    }

    public function testClearExpiredLocks() {
        $this->throttle->clearExpiredLocks();
    }

    /**
     * @expectedException BeaucalLongThrottle\Exception\PhantomLockException
     */
    public function testPhantomLock() {
        $adapterMock = $this->getMock(
        'BeaucalLongThrottle\Adapter\Apc', ['setLock'],
        [new ApcExt, $this->apcAdapter->getOptions(), new LockHandleFactory]
        );
        $handle = new Lock\Handle;
        $adapterMock->expects($this->any())
        ->method('setLock')->will($this->returnValue($handle));
        $adapterMock->expects($this->any())
        ->method('verifyLock')->will($this->returnValue(false));

        $throttle = new Throttle($adapterMock, new ThrottleOptions);
        $throttle->takeLock('phantom', new DateTimeUnit(10, 'years'));
    }

    public function testSetLockReturnsFalse() {
        $adapterMock = $this->getMock(
        'BeaucalLongThrottle\Adapter\Apc', ['setLock'],
        [new ApcExt, $this->apcAdapter->getOptions(), new LockHandleFactory]
        );
        $adapterMock->expects($this->any())
        ->method('setLock')->will($this->returnValue(false));

        $throttle = new Throttle(
        $adapterMock, new ThrottleOptions
        );
        $this->assertFalse($throttle->takeLock(
        'setLockReturnsFalse', new DateTimeUnit(3, 'minutes'))
        );
    }

    /**
     * @expectedException BeaucalLongThrottle\Exception\RuntimeException
     * @expectedExceptionMessage key contained reserved separator
     */
    public function testTakeLockWithSeparator() {
        $this->throttle->takeLock('bad::key', new DateTimeUnit(11, 'months'));
    }

}
