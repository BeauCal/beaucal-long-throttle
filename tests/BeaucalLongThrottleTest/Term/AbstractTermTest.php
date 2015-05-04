<?php

namespace BeaucalLongThrottleTest\Term;

use BeaucalLongThrottle\Term\DateTimeUnit;
use DateTime;

/**
 * @group beaucal_throttle
 */
class AbstractTermTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException BeaucalLongThrottle\Exception\RuntimeException
     * @expectedExceptionMessage returned date in the past
     */
    public function testEndDatePast() {
        $termMock = $this->getMock(
        'BeaucalLongThrottle\Term\DateTimeUnit', ['getEndDateInternal'],
        [1, 'month']
        );
        $termMock->expects($this->once())
        ->method('getEndDateInternal')
        ->will($this->returnValue(new DateTime('yesterday')));

        $termMock->getEndDate();
    }

    /**
     * @expectedException BeaucalLongThrottle\Exception\RuntimeException
     * @expectedExceptionMessage term must be positive
     */
    public function testTermNonPositive() {
        new DateTimeUnit(0, 'month');
    }

}
