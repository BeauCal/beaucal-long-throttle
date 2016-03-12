<?php

namespace BeaucalLongThrottle\Options;

use Zend\Stdlib\AbstractOptions;

class AbstractAdapter extends AbstractOptions {

    protected $clearAllIsCheap = true;

    /**
     * @return bool
     */
    public function getClearAllIsCheap() {
        return $this->clearAllIsCheap;
    }

    /**
     * @param bool $clearAllIsCheap
     * @return DbAdapter
     */
    public function setClearAllIsCheap($clearAllIsCheap) {
        $this->clearAllIsCheap = $clearAllIsCheap;
        return $this;
    }

}
