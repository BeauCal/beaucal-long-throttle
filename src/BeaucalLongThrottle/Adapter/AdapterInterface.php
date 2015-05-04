<?php

namespace BeaucalLongThrottle\Adapter;

use DateTime;

interface AdapterInterface {

    public function beginTransaction();

    public function commit();

    public function rollback();

    /**
     * @param string $key
     */
    public function clearExpiredLock($key);

    /**
     * @param string $key
     * @param DateTime $endDate
     * @return bool
     */
    public function setLock($key, DateTime $endDate);

    /**
     * @param string $key
     * @return bool
     */
    public function verifyLock($key);
}
