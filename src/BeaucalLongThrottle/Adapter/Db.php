<?php

namespace BeaucalLongThrottle\Adapter;

use BeaucalLongThrottle\Lock;
use BeaucalLongThrottle\Factory\LockHandleFactory;
use BeaucalLongThrottle\Options\DbAdapter as DbOptions;
use DateTime;
use Zend\Db\TableGateway\TableGateway;

class Db extends AbstractAdapter {

    /**
     * @var TableGateway
     */
    protected $gateway;

    /**
     * @var DbOptions
     */
    protected $options;

    public function __construct(
    TableGateway $gateway, DbOptions $options, LockHandleFactory $lockFactory
    ) {
        $this->gateway = $gateway;
        $this->options = $options;
        $this->lockFactory = $lockFactory;
    }

    /**
     * @return DbOptions
     */
    public function getOptions() {
        return $this->options;
    }

    public function beginTransaction() {
        $this->options->getUseTransactions() and $this->getConnection()->beginTransaction();
    }

    public function commit() {
        $this->options->getUseTransactions() and $this->getConnection()->commit();
    }

    public function rollback() {
        $this->options->getUseTransactions() and $this->getConnection()->rollback();
    }

    protected function getConnection() {
        return $this->gateway->getAdapter()->getDriver()->getConnection();
    }

    /**
     * @param string $key
     */
    public function clearExpiredLock($key = null) {
        $currDate = date($this->options->getDbDateTimeFormat());
        $delete = $this->gateway->getSql()->delete();
        $pred = $delete->where->lessThanOrEqualTo('end_datetime', $currDate);
        if ($key) {
            $pred = $pred->and->equalTo('key', $key);
        }
        $this->gateway->deleteWith($delete);
    }

    /**
     * @param string $key
     * @param DateTime $endDate
     * @return mixed Lock\Handle or false
     */
    public function setLock($key, DateTime $endDate) {
        try {
            $result = $this->gateway->insert([
                'key' => $key,
                'end_datetime' => $endDate->format(
                $this->options->getDbDateTimeFormat()
                )
            ]);
            if ($result) {
                return $this->createLockHandle($key);
            }
        } catch (\Exception $e) {

        }
        return false;
    }

    /**
     * @param Lock\Handle $handle
     * @return bool
     */
    public function verifyLock(Lock\Handle $handle) {
        if (!isset($this->locks[$handle->getToken()])) {
            return false;
        }

        $key = $this->locks[$handle->getToken()];
        return (bool) $this->gateway->select(['key' => $key])->count();
    }

    /**
     * @param Lock\Handle $handle
     */
    public function clearLock(Lock\Handle $handle) {
        if (!isset($this->locks[$handle->getToken()])) {
            return;
        }
        $key = $this->locks[$handle->getToken()];
        $this->gateway->delete(['key' => $key]);
        unset($this->locks[$handle->getToken()]);
    }

}
