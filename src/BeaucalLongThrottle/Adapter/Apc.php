<?php

namespace BeaucalLongThrottle\Adapter;

use BeaucalLongThrottle\Lock;
use BeaucalLongThrottle\Factory\LockHandleFactory;
use BeaucalLongThrottle\Options\ApcAdapter as ApcOptions;
use BeaucalLongThrottle\Exception\LockFactoryException;
use BeaucalLongThrottle\Apc\Apc as ApcWrapper;
use DateTime;

/**
 * APC is not dependable for long-term locking.
 * Use only to lock for seconds or hours.
 */
class Apc extends AbstractAdapter {

    /**
     * @var ApcOptions
     */
    protected $options;

    /**
     * @var ApcWrapper
     */
    protected $apc;

    public function __construct(
    ApcWrapper $apc, ApcOptions $options, LockHandleFactory $lockFactory
    ) {
        $this->apc = $apc;
        $this->options = $options;
        $this->lockFactory = $lockFactory;
    }

    /**
     * @return ApcOptions
     */
    public function getOptions() {
        return $this->options;
    }

    public function beginTransaction() {
        /**
         * APC handles for us.
         */
    }

    public function commit() {
        /**
         * APC handles for us.
         */
    }

    public function rollback() {
        /**
         * APC handles for us.
         */
    }

    public function clearExpiredLock($key = null) {
        /**
         * APC handles for us.
         */
    }

    /**
     * @param string $key
     * @param DateTime $endDate
     * @return mixed Lock\Handle or false
     */
    public function setLock($key, DateTime $endDate) {
        $key = $this->getOptions()->getApcNamespace() . $this->separator . $key;

        $now = new DateTime;
        $ttl = $endDate->getTimestamp() - $now->getTimestamp();
        if ($ttl <= 0) {
            return false;
        }

        if ($this->apc->add($key, true, $ttl)) {
            try {
                return $this->createLockHandle($key);
            } catch (LockFactoryException $e) {
                if (getenv('TRAVIS')) {
                    echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL . PHP_EOL;
                }
                return false;
            }
        }
        if (getenv('TRAVIS')) {
            echo __FUNCTION__ . ' APC could not add' . PHP_EOL . PHP_EOL;
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
        return (bool) $this->apc->fetch($key);
    }

    /**
     * @param Lock\Handle $handle
     */
    public function clearLock(Lock\Handle $handle) {
        if (!isset($this->locks[$handle->getToken()])) {
            return;
        }
        $key = $this->locks[$handle->getToken()];
        $this->apc->delete($key);
    }

}
