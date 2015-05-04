<?php

namespace BeaucalLongThrottle\Exception;

/**
 * When 'verifyLock' option is on and setLock() fails,
 * this will be thrown to client since THIS IS A VERY BAD THING.
 */
class SetLockException extends \Exception {

}
