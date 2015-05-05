<?php

namespace BeaucalLongThrottle\Term;

use DateTime;

/**
 * End date in, end date out.
 */
class DateTimeEnd extends AbstractTerm {

    /**
     * @var DateTime
     */
    protected $endDate;

    /**
     * @param DateTime $endDate
     */
    public function __construct($endDate) {
        $this->endDate = clone $endDate;
    }

    /**
     * @return DateTime
     */
    protected function getEndDateInternal() {
        return clone $this->endDate;
    }

}
