<?php

namespace BeaucalLongThrottleTest\Term;

use BeaucalLongThrottle\Term\DateTimeEnd;
use DateTime;

/**
 * @group beaucal_throttle
 */
class DateTimeEndTest extends \PHPUnit_Framework_TestCase {

    public function testGood() {
        $endDate = new DateTime('+1 year');
        $termEndDate = (new DateTimeEnd($endDate))->getEndDate();
        $this->assertEquals(
        $termEndDate->getTimestamp(), $endDate->getTimestamp()
        );
    }

    public function testInputIsCloned() {
        $endDate = new DateTime('+1 year');
        $termEndDate = (new DateTimeEnd($endDate))->getEndDate();
        $this->assertNotSame($termEndDate, $endDate);

        $endDate->modify('+1 second');
        $this->assertNotEquals(
        $termEndDate->getTimestamp(), $endDate->getTimestamp()
        );
    }

    public function testOutputIsCloned() {
        $term = new DateTimeEnd(new DateTime('+1 year'));
        $output = $term->getEndDate();
        $this->assertNotSame($output, $term->getEndDate());

        $output->modify('+1 second');
        $this->assertNotEquals(
        $output->getTimestamp(), $term->getEndDate()->getTimestamp()
        );
    }

    /**
     * @expectedException BeaucalLongThrottle\Exception\RuntimeException
     * @expectedExceptionMessage returned date in the past
     */
    public function testEndDatePast() {
        $term = new DateTimeEnd(new DateTime('yesterday'));
        $term->getEndDate();
    }

}
