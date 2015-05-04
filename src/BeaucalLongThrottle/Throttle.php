<?php

namespace BeaucalLongThrottle;

use BeaucalLongThrottle\Term\AbstractTerm;
use BeaucalLongThrottle\Adapter\AdapterInterface as ThrottleAdapterInterface;
use BeaucalLongThrottle\Exception\SetLockException;
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
        $this->adapter = $adapter;
        $this->options = $options;
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
     * @throws SetLockException
     * @return boolean
     */
    public function takeLock($key, AbstractTerm $term) {
        $this->adapter->beginTransaction();

        try {
            $this->adapter->clearExpiredLock($key);

            /**
             * Failure may very well throw exceptions.
             */
            $result = $this->adapter->setLock($key, $term->getEndDate());

            if ($this->options->getVerifyLock() && !$this->adapter->verifyLock($key)) {
                throw new SetLockException;
            }
            if ($result) {
                $this->adapter->commit();
                return true;
            }
        } catch (SetLockException $e) {
            /**
             * Lock-setting reported success but actually failed;
             * this is VERY BAD and needs to be addressed by sysadmin.
             */
            $this->adapter->rollback();
            throw $e;
        } catch (\Exception $e) {
            /**
             * Most likely from setLock; standard.
             */
        }

        $this->adapter->rollback();
        return false;
    }

}
