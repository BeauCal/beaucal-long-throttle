<?php

namespace BeaucalLongThrottle\Term;

use DateTime;
use BeaucalLongThrottle\Exception\RuntimeException;

/**
 * Term specified as \DateTime::modify("+{$term} {$modifyUnit}");
 */
class DateTimeUnit extends AbstractTerm {

    /**
     * @var string
     */
    protected $modifyUnit;

    /**
     * @var int
     */
    protected $term;

    /**
     * @param int $term
     * @param string $unit  Unit symbols from https://php.net/manual/en/datetime.formats.relative.php
     */
    public function __construct($term, $modifyUnit) {
        parent::__construct($term);
        $this->modifyUnit = $modifyUnit;
    }

    /**
     * @return DateTime
     */
    protected function getEndDateInternal() {
        return new DateTime("+{$this->term} {$this->modifyUnit}");
    }

}
