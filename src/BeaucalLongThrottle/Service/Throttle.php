<?php

namespace BeaucalLongThrottle\Service;

use BeaucalLongThrottle\Term\AbstractTerm;
use BeaucalLongThrottle\Adapter\AdapterInterface as ThrottleAdapterInterface;
use BeaucalLongThrottle\Exception;
use BeaucalLongThrottle\Lock;
use Zend\Stdlib\AbstractOptions;

class Throttle {

    /**
     * @var ThrottleAdapterInterface
     */
    protected $adapter;

    /**
     * @var AbstractOptions
     */
    protected $options;

    public function __construct(
    ThrottleAdapterInterface $adapter, AbstractOptions $options
    ) {
        $this->options = $options;
        $this->adapter = $adapter;
        if (!$this->options->getSeparator()) {
            throw new Exception\OptionException('Separator is not set');
        }
        $this->adapter->setSeparator($this->options->getSeparator());
    }

    /**
     * @return AbstractOptions
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * Asks, "is the key locked?" and if not, sets it.
     *
     * @param string $key
     * @param AbstractTerm $term
     * @throws \Exception
     * @return mixed Lock\Handle or false
     */
    public function takeLock($key, AbstractTerm $term) {
        if (strstr($key, $this->options->getSeparator())) {
            throw new Exception\RuntimeException('key contained reserved separator');
        }

        $this->adapter->beginTransaction();
        try {
            $clearKey = $this->adapter->getOptions()->getClearAllIsCheap() ?
            null : $key;
            $this->adapter->clearExpiredLock($clearKey);

            /**
             * Failure may very well throw exceptions.
             */
            $result = $this->adapter->setLock($key, $term->getEndDate());
            if ($result instanceof Lock\Handle) {
                $this->adapter->commit();

                if ($this->options->getVerifyLock() && !$this->adapter->verifyLock($result)) {
                    throw new Exception\PhantomLockException;
                }
                return $result;
            }
        } catch (Exception\PhantomLockException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->adapter->rollback();
            throw $e;
        }
        $this->adapter->rollback();
        return false;
    }

    /**
     * @param Lock\Handle $handle
     */
    public function clearLock(Lock\Handle $handle) {
        $this->adapter->clearLock($handle);
    }

    public function clearExpiredLocks() {
        $this->adapter->clearExpiredLock();
    }

}
