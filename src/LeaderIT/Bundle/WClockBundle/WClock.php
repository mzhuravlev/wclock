<?php
/**
 * Created by PhpStorm.
 * User: M. Zhuravlev
 * Date: 29.09.2014
 * Time: 9:36
 */

namespace LeaderIT\Bundle\WClockBundle;

use DateTime, DateInterval;
use LeaderIT\Bundle\WClockBundle\Entity\Event;


class WClock
{

    function calcDayWorkTime($events, $separate = false, $toCurrent = false)
    {
        // Event[] -> DateInterval
        // получить количество отработанных часов
        $state = Event::ACTION_LEAVE;

        $start = array();
        $stop = array();

        foreach ($events as $event) {
            $eventType = $event->getType();
            $eventTime = $event->getTime();

            switch ($state) {
                case Event::ACTION_LEAVE:
                    if ($eventType == Event::ACTION_WORK) {
                        $state = $eventType;
                        $start[] = $eventTime;
                    }
                    break;
                case Event::ACTION_WORK:
                    if ($eventType == Event::ACTION_LEAVE or $eventType == Event::ACTION_BREAK) {
                        $state = $eventType;
                        $stop[] = $eventTime;
                    }
                    break;
                case Event::ACTION_BREAK:
                    if ($eventType == Event::ACTION_WORK) {
                        $state = $eventType;
                        $start[] = $eventTime;
                    }
                    break;
            }
        }

        if (count($start) == count($stop) + 1) {
            if ($toCurrent) {
                $stop[] = new DateTime(); // считаем время по настоящий момент
            } else {
                array_pop($start); // не будем считать время для незаконченного действия
            }
        }

        if (count($start) == count($stop)) {
            $result = 0;

            foreach ($start as $key => $value) {
                $result += $stop[$key]->getTimestamp() - $value->getTimestamp();
            }

           $result = $this->secondsToTime($result);

            return $result;
        }

        return new DateInterval("P0D");
    }

    public function calcTimeToNow($time)
    {
        // DateTime -> DateInterval
        // вычисляет время от предоставленного времени до текущего момента
        $now = new DateTime();
        return $now->diff($time);
    }

    public function secondsToTime($secs)
    {
        // int -> DateInterval
        // вычислить часы и минуты из секунд
        $time = new DateTime('@' . $secs);
        $zero = new DateTime('@0');
        return $time->diff($zero);
    }
} 