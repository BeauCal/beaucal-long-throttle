<?php

namespace BeaucalLongThrottle\Factory;

use BeaucalLongThrottle\Lock;

class LockHandleFactory {

    public function createHandle() {
        return new Lock\Handle;
    }

}
