<?php
/**
 * Criado com PhpStorm.
 * Autor: Ello Oliveira
 * Data: 23/11/2017
 * Hora: 17:27
 */

defined('MOODLE_INTERNAL') || die();
$observers = array(
    array(
        'eventname'   => '\core\event\user_enrolment_created',
        'callback'    => 'enrol_dbextended_observer::user_enrolment_created',
    ),
);
