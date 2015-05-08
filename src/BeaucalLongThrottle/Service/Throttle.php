<?php

namespace BeaucalLongThrottle\Service;

use BeaucalLongThrottle\Term\AbstractTerm;
use BeaucalLongThrottle\Adapter\AdapterInterface as ThrottleAdapterInterface;
use BeaucalLongThrottle\Exception;
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
     * @throws Exception\PhantomLockException
     * @throws Exception\RuntimeException       if key contains separator
     * @return boolean
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

            if ($result) {
                if ($this->options->getVerifyLock() && !$this->adapter->verifyLock($key)) {
                    throw new Exception\PhantomLockException;
                }

                $this->adapter->commit();
                return true;
            }
        } catch (Exception\PhantomLockException $e) {
            /**
             * Lock-setting reported success but actually failed;
             * this is VERY BAD and needs to be addressed by sysadmin.
             */
            $this->adapter->rollback();
            throw $e;
        } catch (Exception\OptionException $e) {
            $this->adapter->rollback();
            throw $e;
        } catch (\Exception $e) {
            echo $e->getMessage();
            /**
             * Most likely from setLock; standard.
             */
        }

        $this->adapter->rollback();
        return false;
    }

    public function clearExpiredLocks() {
        $this->adapter->clearExpiredLock();
    }

}
