<?php

namespace BeaucalLongThrottle\Exception;

/**
 * When setLock reports success but actually failed,
 * this will be thrown to client since THIS IS A VERY BAD THING.
 */
class PhantomLockException extends \Exception {

}
