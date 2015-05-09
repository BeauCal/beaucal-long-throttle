<?php

namespace BeaucalLongThrottle\Lock;

use Zend\Math\Rand;

/**
 * Client can do the following:
 *
 * $handle = Throttle::takeLock(...);
 * Throttle::clearLock($handle);
 */
class Handle {

    const MAX_TOKEN = 2147483647;

    /**
     * @var int
     */
    protected $token;

    public function __construct() {
        $this->token = Rand::getInteger(1, self::MAX_TOKEN);
    }

    public function getToken() {
        return $this->token;
    }

}
