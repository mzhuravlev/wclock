<?php

define('ACTION_WORK', 100);
define('ACTION_BREAK', 200);
define('ACTION_LEAVE', 300);
define('ACTION_NONE', 0);



function getActionType($action) {
    // string -> int

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