<?php
/**
 * Criado com PhpStorm.
 * Autor: Ello Oliveira
 * Data: 23/11/2017
 * Hora: 17:30
 *
 * Classe que observa eventos de inscrição de usuários em cursos.
 * Pode ser usado para logs e mensagens
 */

defined('MOODLE_INTERNAL') || die();
//require_once($CFG->dirroot.'/enrol/dbextended/locallib.php');
class enrol_dbextended_observer{
    function user_enrolment_created(\core\event\user_enrolment_created $event){

    }
}