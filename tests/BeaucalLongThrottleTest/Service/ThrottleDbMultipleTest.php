<?php

namespace BeaucalLongThrottleTest\Service;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter as DbAdapter;
use BeaucalLongThrottle\Service\Throttle;
use BeaucalLongThrottle\Adapter\DbMultiple as ThrottleDbMultipleAdapter;
use BeaucalLongThrottle\Term\DateTimeUnit;
use BeaucalLongThrottle\Options\DbMultipleAdapter as ThrottleDbAdapterOptions;
use BeaucalLongThrottle\Options\Throttle as ThrottleOptions;

/**
 * @group beaucal_throttle
 */
class ThrottleDbMultipleTest extends \PHPUnit_Extensions_Database_TestCase {

    /**
     * @var DbAdapter
     */
    protected $dbAdapter;

    /**
     * @var TableGateway
     */
    protected $gateway;

    /**
     * @var ThrottleDbMultipleAdapter
     */
    protected $throttleDbAdapter;

    /**
     * @var Throttle
     */
    protected $throttle;

    public function setUp() {
        parent::setUp();

        $dbOptions = new ThrottleDbAdapterOptions;
        $dbOptions->setRegexCounts([
            '/^past$/' => 4,
            '/^simulate$/' => 3,
        ]);

        $this->gateway = new TableGateway(
        $dbOptions->getDbTable(), $this->getAdapter()
        );
        $this->throttleDbAdapter = new ThrottleDbMultipleAdapter($this->gateway,
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
        return $this->createFlatXMLDataSet(__DIR__ . '/data/beaucal_throttle-db-multiple-seed.xml');
    }

    public function testGetLockExisting() {
        $select = $this->gateway->getSql()->select();
        $select->where->like('key', '%::forever');
        $this->assertEquals(
        1, $this->gateway->selectWith($select)->count()
        );

        $this->assertFalse(
        $this->throttle->takeLock('forever', new DateTimeUnit(3, 'weeks'))
        );
    }

    public function testGetLockExistingExpired() {
        $select = $this->gateway->getSql()->select();
        $select->where->like('key', '%::past');
        $this->assertEquals(
        3, $this->gateway->selectWith($select)->count()
        );

        $handles = [];
        for ($i = 0; $i < 4; $i++) {
            $handle = $this->throttle->takeLock('past',
            new DateTimeUnit(17, 'days'));
            $this->assertTrue((bool) $handle);
            $handles[] = $handle;
        }
        $this->assertFalse(
        $this->throttle->takeLock('past', new DateTimeUnit(1, 'minute'))
        );

        foreach ($handles as $handle) {
            $this->throttle->clearLock($handle);
            $this->assertTrue((bool)
            $this->throttle->takeLock('past', new DateTimeUnit(1, 'minute'))
            );
        }
        $this->assertFalse(
        $this->throttle->takeLock('past', new DateTimeUnit(1, 'minute'))
        );
    }

    public function testGetLockNonExisting() {
        $select = $this->gateway->getSql()->select();
        $select->where->like('key', '%::nonexisting');
        $this->assertEmpty($this->gateway->selectWith($select)->count());

        $handle = $this->throttle->takeLock('nonexisting',
        new DateTimeUnit(1, 'week'));
        $this->assertTrue((bool) $handle);
        $this->assertInstanceOf('BeaucalLongThrottle\Lock\Handle', $handle);

        $this->assertFalse(
        $this->throttle->takeLock('nonexisting', new DateTimeUnit(1, 'week'))
        );
    }

    public function testGetLockSimulate() {
        $key = 'simulate';
        for ($i = 0; $i < 3; $i++) {
            $handle = $this->throttle->takeLock(
            $key, new DateTimeUnit(2, 'second')
            );
            $this->assertTrue((bool) $handle);
        }
        $this->assertFalse(
        $this->throttle->takeLock($key, new DateTimeUnit(1, 'second'))
        );
        sleep(2);

        $handle = $this->throttle->takeLock($key, new DateTimeUnit(1, 'second'));
        $this->assertTrue((bool) $handle);
        sleep(3);

        for ($i = 0; $i < 3; $i++) {
            $handle = $this->throttle->takeLock($key,
            new DateTimeUnit(1, 'minute'));
            $this->assertTrue((bool) $handle);
        }
        $this->assertFalse(
        $this->throttle->takeLock($key, new DateTimeUnit(1, 'second'))
        );
    }

    public function testClearExpiredLocks() {
        $this->throttle->clearExpiredLocks();
        $select = $this->gateway->getSql()->select();
        $select->where->like('key', '%::past');
        $this->assertEmpty($this->gateway->selectWith($select)->count());
    }

    public function testClearExpiredLockKey() {
        $dbOptions = new ThrottleDbAdapterOptions;
        $dbOptions->setRegexCounts([
            '/^whatever/' => 99
        ]);
        $dbOptions->setClearAllIsCheap(false);

        $gateway = new TableGateway(
        $dbOptions->getDbTable(), $this->getAdapter()
        );
        $throttleDbAdapter = new ThrottleDbMultipleAdapter($gateway, $dbOptions);

        $throttleOptions = new ThrottleOptions;
        $throttle = new Throttle($throttleDbAdapter, $throttleOptions);

        $throttle->takeLock('ok', new DateTimeUnit(6, 'days'));
    }

    /**
     * @expectedException BeaucalLongThrottle\Exception\OptionException
     * @expectedExceptionMessage Separator is not set
     */
    public function testSeparatorNotSet() {
        $dbOptions = new ThrottleDbAdapterOptions;
        $dbOptions->setRegexCounts([
            '/^whatever/' => 99
        ]);

        $gateway = new TableGateway(
        $dbOptions->getDbTable(), $this->getAdapter()
        );
        $throttleDbAdapter = new ThrottleDbMultipleAdapter($gateway, $dbOptions);

        $throttleOptions = new ThrottleOptions;
        $throttleOptions->setSeparator('');
        $throttle = new Throttle($throttleDbAdapter, $throttleOptions);

        $throttle->takeLock('ok', new DateTimeUnit(6, 'days'));
    }

    /**
     * @expectedException BeaucalLongThrottle\Exception\OptionException
     * @expectedExceptionMessage regex_counts not specified
     */
    public function testRegexCountsNotSet() {
        $dbOptions = new ThrottleDbAdapterOptions;

        $gateway = new TableGateway(
        $dbOptions->getDbTable(), $this->getAdapter()
        );
        $throttleDbAdapter = new ThrottleDbMultipleAdapter($gateway, $dbOptions);

        $throttleOptions = new ThrottleOptions;
        $throttle = new Throttle($throttleDbAdapter, $throttleOptions);

        $throttle->takeLock('ok', new DateTimeUnit(6, 'days'));
    }

    /**
     * @expectedException BeaucalLongThrottle\Exception\OptionException
     * @expectedExceptionMessage regex_counts must be positive
     */
    public function testRegexCountsNotPositive() {
        $dbOptions = new ThrottleDbAdapterOptions;
        $dbOptions->setRegexCounts([
            '/^whatever/' => 0
        ]);

        $gateway = new TableGateway(
        $dbOptions->getDbTable(), $this->getAdapter()
        );
        $throttleDbAdapter = new ThrottleDbMultipleAdapter($gateway, $dbOptions);

        $throttleOptions = new ThrottleOptions;
        $throttle = new Throttle($throttleDbAdapter, $throttleOptions);

        $throttle->takeLock('ok', new DateTimeUnit(6, 'days'));
    }

    /**
     * @expectedException BeaucalLongThrottle\Exception\OptionException
     * @expectedExceptionMessage regex_counts pattern BAD REGEX is invalid
     */
    public function testRegexCountsInvalidPattern() {
        $dbOptions = new ThrottleDbAdapterOptions;
        $dbOptions->setRegexCounts([
            'BAD REGEX' => 1
        ]);

        $gateway = new TableGateway(
        $dbOptions->getDbTable(), $this->getAdapter()
        );
        $throttleDbAdapter = new ThrottleDbMultipleAdapter($gateway, $dbOptions);

        $throttleOptions = new ThrottleOptions;
        $throttle = new Throttle($throttleDbAdapter, $throttleOptions);

        $throttle->takeLock('ok', new DateTimeUnit(6, 'days'));
    }

}
