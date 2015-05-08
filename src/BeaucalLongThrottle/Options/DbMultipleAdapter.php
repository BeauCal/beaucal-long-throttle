<?php

namespace BeaucalLongThrottle\Options;

class DbMultipleAdapter extends DbAdapter {

    protected $regexCounts = [];

    /**
     * @return array
     */
    public function getRegexCounts() {
        return $this->regexCounts;
    }

    /**
     * @param array $regexCounts
     * @return DbMultipleAdapter
     */
    public function setRegexCounts($regexCounts) {
        $this->regexCounts = $regexCounts;
        return $this;
    }

}
