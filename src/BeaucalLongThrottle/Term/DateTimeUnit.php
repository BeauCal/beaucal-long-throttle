<?php

namespace BeaucalLongThrottle\Term;

use BeaucalLongThrottle\Exception\RuntimeException;
use DateTime;

/**
 * Term specified as \DateTime::modify("+{$term} {$modifyUnit}");
 */
class DateTimeUnit extends AbstractTerm {

    /**
     * @var int
     */
    protected $term;

    /**
     * @var string
     */
    protected $modifyUnit;

    /**
     * @param int $term
     * @param string $unit  Unit symbols from https://php.net/manual/en/datetime.formats.relative.php
     */
    public function __construct($term, $modifyUnit) {
        $term = (int) $term;
        if ($term < 1) {
            throw new RuntimeException('term must be positive');
        }
        $this->term = $term;
        $this->modifyUnit = $modifyUnit;
    }

    /**
     * @return DateTime
     */
    protected function getEndDateInternal() {
        return new DateTime("+{$this->term} {$this->modifyUnit}");
    }

}
