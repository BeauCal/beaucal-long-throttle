<?php

namespace BeaucalLongThrottle\Term;

use BeaucalLongThrottle\Exception\RuntimeException;
use DateTime;

abstract class AbstractTerm {

    /**
     * @var int
     */
    protected $term;

    /**
     * @return DateTime
     */
    protected abstract function getEndDateInternal();

    /**
     * @param int $term
     */
    public function __construct($term) {
        $term = (int) $term;
        if ($term < 1) {
            throw new RuntimeException('term must be positive');
        }
        $this->term = $term;
    }

    /**
     * @return DateTime
     */
    public function getEndDate() {
        $date = $this->getEndDateInternal();
        if ($date < new DateTime) {
            throw new RuntimeException('getEndDateInternal returned date in the past');
        }
        return $date;
    }

}
