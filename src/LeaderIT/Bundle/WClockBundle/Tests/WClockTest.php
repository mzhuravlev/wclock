<?php
/**
 * Created by PhpStorm.
 * User: pavlyak
 * Date: 29.09.2014
 * Time: 13:54
 */

namespace LeaderIT\Bundle\WClockBundle\Tests;


use LeaderIT\Bundle\WClockBundle\Entity\Event;
use LeaderIT\Bundle\WClockBundle\WClock;
use DateTime, DateInterval;

class WClockTest extends \PHPUnit_Framework_TestCase{

    public function testSecondsToTime() {
        $calc = new WClock();

        $sec = 60*60+60*30;
        $interval = new DateInterval("PT1H30M");
        $result = $calc->secondsToTime($sec);

        $this->assertEquals($interval->h, $result->h);
        $this->assertEquals($interval->m, $result->m);
    }

    public function testCalcTimeToNow() {
        $calc = new WClock();
        $interval = new DateInterval("PT2H20M");
        $time = new DateTime();
        $time->sub($interval);

        $result = $calc->calcTimeToNow($time);

        $this->assertEquals($interval->h, $result->h);
        $this->assertEquals($interval->m, $result->m);
    }

    public function testCalcWorkTime() {
        $calc = new WClock();

        // --- MOCK DATA
        $event1 = new Event();
        $event1->load(Event::ACTION_WORK, DateTime::createFromFormat("H:i", "10:00"));

        $event2 = new Event();
        $event2->load(Event::ACTION_BREAK, DateTime::createFromFormat("H:i", "12:00"));

        $event3 = new Event();
        $event3->load(Event::ACTION_WORK, DateTime::createFromFormat("H:i", "13:00"));

        $event4 = new Event();
        $event4->load(Event::ACTION_LEAVE, DateTime::createFromFormat("H:i", "19:10"));

        // --- TEST DATA
        $events1 = array($event1, $event2, $event3, $event4);
        $expect1 = new DateInterval("PT8H10M");
        $result1 = $calc->calcDayWorkTime($events1);

        $events2 = array($event1, $event4);
        $expect2 = new DateInterval("PT9H10M");
        $result2 = $calc->calcDayWorkTime($events2);


        // --- RUN TESTS
        $this->assertEquals(DateTime::createFromFormat("H:i", "10:00"), $event1->getTime(), "parameter not matches");
        $this->assertEquals($expect1->h, $result1->h);
        $this->assertEquals($expect1->m, $result1->m);
        $this->assertEquals($expect2->h, $result2->h);
        $this->assertEquals($expect2->m, $result2->m);
    }
} 