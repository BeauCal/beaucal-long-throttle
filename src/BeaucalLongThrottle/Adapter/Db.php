<?php

namespace BeaucalLongThrottle\Adapter;

use BeaucalLongThrottle\Adapter\AdapterInterface as ThrottleAdapterInterface;
use DateTime;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\AbstractOptions;

class Db implements ThrottleAdapterInterface {

    /**
     * @var TableGateway
     */
    protected $gateway;

    /**
     * @var AbstractOptions
     */
    protected $options;

    public function __construct(TableGateway $gateway, AbstractOptions $options) {
        $this->gateway = $gateway;
        $this->options = $options;
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
    public function clearExpiredLock($key) {
        $currDate = date($this->options->getDbDateTimeFormat());
        $delete = $this->gateway->getSql()->delete();
        $delete->where->equalTo('key', $key)
        ->and->lessThanOrEqualTo('end_datetime', $currDate);
        $this->gateway->deleteWith($delete);
    }

    /**
     * @param string $key
     * @param DateTime $endDate
     * @return bool
     */
    public function setLock($key, DateTime $endDate) {
        $insertResult = $this->gateway->insert([
            'key' => $key,
            'end_datetime' => $endDate->format(
            $this->options->getDbDateTimeFormat()
            )
        ]);
        return (bool) $insertResult;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function verifyLock($key) {
        return (bool) $this->gateway->select(['key' => $key])->count();
    }

}
