<?php

namespace BeaucalLongThrottleTest\Service;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter as DbAdapter;
use BeaucalLongThrottle\Service\Throttle;
use BeaucalLongThrottle\Adapter\Db as ThrottleDbAdapter;
use BeaucalLongThrottle\Term\DateTimeUnit;
use BeaucalLongThrottle\Options\DbAdapter as ThrottleDbAdapterOptions;
use BeaucalLongThrottle\Options\Throttle as ThrottleOptions;
use BeaucalLongThrottle\Lock;
use BeaucalLongThrottle\Factory\LockHandleFactory;

/**
 * @group beaucal_throttle
 */
class ThrottleDbTest extends \PHPUnit_Extensions_Database_TestCase {

    /**
     * @var DbAdapter
     */
    protected $dbAdapter;

    /**
     * @var TableGateway
     */
    protected $gateway;

    /**
     * @var ThrottleDbAdapter
     */
    protected $throttleDbAdapter;

    /**
     * @var Throttle
     */
    protected $throttle;

    public function setUp() {
        parent::setUp();

        $dbOptions = new ThrottleDbAdapterOptions;
        $this->gateway = new TableGateway(
        $dbOptions->getDbTable(), $this->getAdapter()
        );
        $this->throttleDbAdapter = new ThrottleDbAdapter(
        $this->gateway, $dbOptions, new LockHandleFactory
        );

        $throttleOptions = new ThrottleOptions;
        $this->throttle = new Throttle(
        $this->throttleDbAdapter, $throttleOptions
        );
    }

    protected function getAdapter() {
        if ($this->dbAdapter) {
            return $this->dbAdapter;
        }
        $config = include __DIR__ . '/../../dbadapter.php';
        $config = $config['db'];
        $config['driver'] = 'PDO';
        $this->dbAdapter = new DbAdapter($config);
        return $this->dbAdapter;
    }

    protected function getConnection() {
        return $this->createDefaultDBConnection($this->getAdapter()->getDriver()->getConnection()->getResource());
    }

    protected function getDataSet() {
        return $this->createFlatXMLDataSet(__DIR__ . '/data/beaucal_throttle-db-seed.xml');
    }

    public function testGetLockExisting() {
        $this->assertEquals(
        1, $this->gateway->select(['key' => 'forever'])->count()
        );

        $this->assertFalse(
        $this->throttle->takeLock('forever', new DateTimeUnit(1, 'week'))
        );
    }

    public function testGetLockExistingExpired() {
        $this->assertEquals(
        1, $this->gateway->select(['key' => 'past'])->count()
        );

        $handle = $this->throttle->takeLock('past', new DateTimeUnit(1, 'week'));
        $this->assertTrue((bool) $handle);
        $this->assertInstanceOf('BeaucalLongThrottle\Lock\Handle', $handle);
    }

    public function testGetLockNonExisting() {
        $this->assertEquals(
        0, $this->gateway->select(['key' => 'nonexisting'])->count()
        );

        $handle = $this->throttle->takeLock('nonexisting',
        new DateTimeUnit(1, 'week'));
        $this->assertTrue((bool) $handle);
        $this->assertInstanceOf('BeaucalLongThrottle\Lock\Handle', $handle);

        $this->assertFalse(
        $this->throttle->takeLock('nonexisting', new DateTimeUnit(7, 'years'))
        );
    }

    public function testGetLockSimulate() {
        $key = 'simulate';
        $handle = $this->throttle->takeLock($key, new DateTimeUnit(1, 'second'));
        $this->assertTrue((bool) $handle);
        $this->assertInstanceOf('BeaucalLongThrottle\Lock\Handle', $handle);

        $this->assertFalse(
        $this->throttle->takeLock($key, new DateTimeUnit(1, 'second'))
        );
        sleep(1);

        $handle = $this->throttle->takeLock($key, new DateTimeUnit(1, 'second'));
        $this->assertTrue((bool) $handle);
        $this->assertInstanceOf('BeaucalLongThrottle\Lock\Handle', $handle);
    }

    public function testTakeAndClearLock() {
        $key = 'take-and-clear';
        $handle = $this->throttle->takeLock(
        $key, new DateTimeUnit(88, 'years')
        );
        $this->assertTrue((bool) $handle);
        $this->assertInstanceOf('BeaucalLongThrottle\Lock\Handle', $handle);
        $this->assertCount(
        1, $this->gateway->select(['key' => $key])
        );

        $this->throttle->clearLock($handle);
        $this->assertEmpty($this->gateway->select(['key' => $key]));

        /**
         * And again.
         */
        $this->throttle->clearLock($handle);
    }

    public function testClearLockInvalid() {
        $this->throttle->clearLock(new Lock\Handle);
    }

    public function testGetOptions() {
        $this->assertInstanceOf(
        '\BeaucalLongThrottle\Options\Throttle', $this->throttle->getOptions()
        );
    }

    public function testClearExpiredLocks() {
        $this->throttle->clearExpiredLocks();
        $this->assertEquals(
        0, $this->gateway->select(['key' => 'past'])->count()
        );
    }

    /**
     * @expectedException BeaucalLongThrottle\Exception\PhantomLockException
     */
    public function testPhantomLock() {
        $adapterMock = $this->getMock(
        'BeaucalLongThrottle\Adapter\Db', ['setLock'],
        [$this->gateway, $this->throttleDbAdapter->getOptions(), new LockHandleFactory]
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
        'BeaucalLongThrottle\Adapter\Db', ['setLock'],
        [$this->gateway, $this->throttleDbAdapter->getOptions(), new LockHandleFactory]
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
