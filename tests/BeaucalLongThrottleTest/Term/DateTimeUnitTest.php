<?php

namespace BeaucalLongThrottleTest\Term;

use BeaucalLongThrottle\Term\DateTimeUnit;

/**
 * @group beaucal_throttle
 */
class DateTimeUnitTest extends \PHPUnit_Framework_TestCase {

    public function testSmoke() {
        $units = ['secs', 'minutes', 'hours', 'days', 'weeks', 'months', 'years'];
        foreach ($units as $unit) {
            $dateTimeUnit = new DateTimeUnit(1, $unit);
            $dateTimeUnit->getEndDate();
        }
    }

}
