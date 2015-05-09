<?php

namespace BeaucalLongThrottle\Adapter;

use BeaucalLongThrottle\Adapter\AdapterInterface as ThrottleAdapterInterface;
use BeaucalLongThrottle\Lock;
use BeaucalLongThrottle\Exception;
use DateTime;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\AbstractOptions;

class Db implements ThrottleAdapterInterface {

    const LOCK_HANDLE_TRIES = 1000;

    /**
     * @var TableGateway
     */
    protected $gateway;

    /**
     * @var string
     */
    protected $separator;

    /**
     * @var AbstractOptions
     */
    protected $options;

    /**
     * @var array   Lock\Handle token => lock's real key
     */
    protected $locks = [];

    public function __construct(TableGateway $gateway, AbstractOptions $options) {
        $this->gateway = $gateway;
        $this->options = $options;
    }

    /**
     * @param string $separator
     */
    public function setSeparator($separator) {
        $this->separator = (string) $separator;
    }

    /**
     * @return AbstractOptions
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
     * @param string $key
     * @return Lock\Handle
     */
    protected function createLockHandle($key) {
        for ($i = 0; $i < self::LOCK_HANDLE_TRIES; $i++) {
            $lockHandle = new Lock\Handle;
            if (isset($this->locks[$lockHandle->getToken()])) {
                continue;
            }
            $this->locks[$lockHandle->getToken()] = $key;
            return $lockHandle;
        }

        /**
         * Will never happen unless Lock\Handle constructor goes non-random.
         */
        throw new Exception\RuntimeException('Could not create lock handle');
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
