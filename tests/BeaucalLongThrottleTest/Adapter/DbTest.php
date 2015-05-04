<?php

namespace BeaucalLongThrottleTest\Adapter;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter as DbAdapter;
use BeaucalLongThrottle\Service\Throttle;
use BeaucalLongThrottle\Adapter\Db as ThrottleDbAdapter;
use BeaucalLongThrottle\Options\DbAdapter as ThrottleDbAdapterOptions;
use BeaucalLongThrottle\Options\Throttle as ThrottleOptions;

/**
 * @group beaucal_throttle
 */
class DbTest extends \PHPUnit_Extensions_Database_TestCase {

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
        return $this->createFlatXMLDataSet(__DIR__ . '/data/beaucal_throttle-seed.xml');
    }

    public function testClearExpiredLock() {
        $this->assertNotEmpty($this->gateway->select(['key' => 'past']));
        $this->assertNotEmpty($this->gateway->select(['key' => 'past2']));

        $this->throttleDbAdapter->clearExpiredLock('past');

        $this->assertEmpty($this->gateway->select(['key' => 'past']));
        $this->assertNotEmpty($this->gateway->select(['key' => 'past2']));

        $this->throttleDbAdapter->clearExpiredLock();

        $this->assertEmpty($this->gateway->select(['key' => 'past2']));
        $this->assertNotEmpty($this->gateway->select(['key' => 'forever']));
    }

}
