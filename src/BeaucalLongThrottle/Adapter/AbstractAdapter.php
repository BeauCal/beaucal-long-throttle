<?php

namespace BeaucalLongThrottle\Adapter;

use DateTime;
use BeaucalLongThrottle\Lock;
use BeaucalLongThrottle\Exception\LockFactoryException;

abstract class AbstractAdapter {

    const LOCK_HANDLE_TRIES = 100;

    /**
     * @var array   Lock\Handle token => lock's real key
     */
    protected $locks = [];

    /**
     * @var LockHandleFactory
     */
    protected $lockFactory;

    /**
     * @var string
     */
    protected $separator;

    public abstract function beginTransaction();

    public abstract function commit();

    public abstract function rollback();

    /**
     * @param string $separator
     */
    public function setSeparator($separator) {
        $this->separator = (string) $separator;
    }

    /**
     * @param string [$key]  specific key, or null to clear all
     */
    public abstract function clearExpiredLock($key = null);

    /**
     * @param string $key
     * @return Lock\Handle
     */
    protected function createLockHandle($key) {

        /**
         * Choose a random slot; no concurrency to worry about here.
         */
        for ($i = 0; $i < self::LOCK_HANDLE_TRIES; $i++) {
            $lockHandle = $this->lockFactory->createHandle();
            if (isset($this->locks[$lockHandle->getToken()])) {
                continue;
            }
            $this->locks[$lockHandle->getToken()] = $key;
            return $lockHandle;
        }

        /**
         * Will never happen unless Lock\Handle constructor goes non-random.
         */
        throw new LockFactoryException;
    }

    /**
     * @param string $key
     * @param DateTime $endDate
     * @return mixed Lock\Handle or false
     */
    public abstract function setLock($key, DateTime $endDate);

    /**
     * @param Lock\Handle $handle
     * @return bool
     */
    public abstract function verifyLock(Lock\Handle $handle);

    /**
     * @param Lock\Handle $handle
     */
    public abstract function clearLock(Lock\Handle $handle);
}
