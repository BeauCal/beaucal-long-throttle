<?php

namespace BeaucalLongThrottle\Options;

class ApcAdapter extends AbstractAdapter {

    protected $apcNamespace = 'beaucal_throttle';

    /**
     * @return string
     */
    public function getApcNamespace() {
        return $this->apcNamespace;
    }

    /**
     * @param string $dbAdapterClass
     * @return ApcAdapter
     */
    public function setApcNamespace($apcNamespace) {
        $this->apcNamespace = $apcNamespace;
        return $this;
    }

}
