<?php

namespace BeaucalLongThrottle\Adapter;

use DateTime;
use BeaucalLongThrottle\Lock;

interface AdapterInterface {

    public function beginTransaction();

    public function commit();

    public function rollback();

    /**
     * @param string $separator
     */
    public function setSeparator($separator);

    /**
     * @param string [$key]  specific key, or null to clear all
     */
    public function clearExpiredLock($key = null);

    /**
     * @param string $key
     * @param DateTime $endDate
     * @return mixed Lock\Handle or false
     */
    public function setLock($key, DateTime $endDate);

    /**
     * @param Lock\Handle $handle
     * @return bool
     */
    public function verifyLock(Lock\Handle $handle);

    /**
     * @param Lock\Handle $handle
     */
    public function clearLock(Lock\Handle $handle);
}
