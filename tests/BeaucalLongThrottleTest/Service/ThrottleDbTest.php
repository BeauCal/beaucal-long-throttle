<?php

namespace BeaucalLongThrottleTest\Service;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter as DbAdapter;
use BeaucalLongThrottle\Service\Throttle;
use BeaucalLongThrottle\Adapter\Db as ThrottleDbAdapter;
use BeaucalLongThrottle\Term\DateTimeUnit;
use BeaucalLongThrottle\Options\DbAdapter as ThrottleDbAdapterOptions;
use BeaucalLongThrottle\Options\Throttle as ThrottleOptions;

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
        $this->throttleDbAdapter = new ThrottleDbAdapter($this->gateway,
        $dbOptions);

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

        $this->assertTrue(
        $this->throttle->takeLock('past', new DateTimeUnit(1, 'week'))
        );
    }

    public function testGetLockNonExisting() {
        $this->assertEquals(
        0, $this->gateway->select(['key' => 'nonexisting'])->count()
        );

        $this->assertTrue(
        $this->throttle->takeLock('nonexisting', new DateTimeUnit(1, 'week'))
        );
        $this->assertFalse(
        $this->throttle->takeLock('nonexisting', new DateTimeUnit(7, 'years'))
        );
    }

    public function testGetLockSimulate() {
        $key = 'simulate';
        $this->assertTrue(
        $this->throttle->takeLock($key, new DateTimeUnit(1, 'second'))
        );
        $this->assertFalse(
        $this->throttle->takeLock($key, new DateTimeUnit(1, 'second'))
        );
        sleep(1);
        $this->assertTrue(
        $this->throttle->takeLock($key, new DateTimeUnit(1, 'second'))
        );
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
        [$this->gateway, $this->throttleDbAdapter->getOptions()]
        );
        $adapterMock->expects($this->any())
        ->method('setLock')->will($this->returnValue(true));

        $throttle = new Throttle(
        $adapterMock, new ThrottleOptions
        );
        $throttle->takeLock('phantom', new DateTimeUnit(10, 'years'));
    }

    public function testSetLockReturnsFalse() {
        $adapterMock = $this->getMock(
        'BeaucalLongThrottle\Adapter\Db', ['setLock'],
        [$this->gateway, $this->throttleDbAdapter->getOptions()]
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
