<?php

define('DATE_FORMAT', "d.m");

define('ACTION_WORK', 100);
define('ACTION_BREAK', 200);
define('ACTION_LEAVE', 300);
define('ACTION_NONE', 0);


define('ACTION_WORK_TEXT', "начал работу");
define('ACTION_BREAK_TEXT', "перерыв");
define('ACTION_LEAVE_TEXT', "завершил день");
define('ACTION_NONE_TEXT', "ничего");





function getActionType($action) {
    // string -> int
    //

    switch($action) {
        case "action_work":
            return ACTION_WORK;
        case "action_break":
            return ACTION_BREAK;
        case "action_leave":
            return ACTION_LEAVE;
        default:
            return ACTION_NONE;
    }
}

function getActionText($action) {
    // int -> string
    //

    switch($action) {
        case ACTION_WORK:
            return ACTION_WORK_TEXT;
        case ACTION_BREAK:
            return ACTION_BREAK_TEXT;
        case ACTION_LEAVE:
            return ACTION_LEAVE_TEXT;
        default:
            return ACTION_NONE_TEXT;
    }
}

function getReadableEvents($events) {
    // Event[] -> [][]
    // получить читаемые данные из списка Events

    $result = array();

    foreach($events as $rec) {
        $result[] = array(
            'id' => $rec->getId(),
            'userId' => $rec->getUserId(),
            'type' => getActionText($rec->getType()),
            'date' => $rec->getDate(),
            'time' => $rec->getTime()
        );
    }

    return $result;
}

function calcDayWorkTime($events, $separate = false, $toCurrent = false) {
    // Event[] -> string
    // получить количество отработанных часов

    $state = ACTION_LEAVE;

    $start = array();
    $stop = array();

    foreach($events as $event) {
        $eventType = $event->getType();
        $eventTime = $event->getTime();

        switch($state) {
            case ACTION_LEAVE:
                if($eventType == ACTION_WORK) {
                    $state = $eventType;
                    $start[] = $eventTime;
                }
                break;
            case ACTION_WORK:
                if($eventType == ACTION_LEAVE or $eventType == ACTION_BREAK) {
                    $state = $eventType;
                    $stop[] = $eventTime;
                }
                break;
            case ACTION_BREAK:
                if($eventType == ACTION_WORK) {
                    $state = $eventType;
                    $start[] = $eventTime;
                }
                break;
        }
    }

    if(count($start) == count($stop)+1) {
        if($toCurrent) {
            $stop[] = new \DateTime(); // считаем время по настоящий момент
        } else {
            array_pop($start); // не будем считать время для незаконченного действия
        }
    }

    if(count($start) == count($stop)) {
        $result = 0;

        foreach($start as $key=>$value) {
            $result += $stop[$key]->getTimestamp() - $value->getTimestamp();
        }

        $result = secondsToTime($result);

        if($result['hours'] > 20)
            return 0;

        if($separate) {
            return $result;
        } else {
            return $result['hours'] . ":" . $result['minutes'];
        }
    }

    return 0;
}

function secondsToTime($secs)
{
    $dt = new DateTime('@' . $secs, new DateTimeZone('UTC'));
    return array('days'    => $dt->format('z'),
        'hours'   => $dt->format('G'),
        'minutes' => $dt->format('i'),
        'seconds' => $dt->format('s'));
}