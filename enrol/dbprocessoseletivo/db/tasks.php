<?php
/**
 * Created by PhpStorm.
 * User: 08429611436
 * Date: 21/09/2017
 * Time: 17:38
 */



defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => '\enrol_dbprocessoseletivo\task\sync_enrol',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => 'R',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 1
    ),
    array(
        'classname' => '\enrol_dbprocessoseletivo\task\sync',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => 'R',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 1
    ),

);