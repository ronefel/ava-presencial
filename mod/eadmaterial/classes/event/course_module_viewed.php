<?php
/**
 * Criado com PhpStorm.
 * Autor: Ello Oliveira
 * Data: 15/01/2018
 * Hora: 10:34
 */
namespace mod_eadmaterial\event;
defined('MOODLE_INTERNAL') || die();
class course_module_viewed extends \core\event\course_module_viewed {
    protected function init() {
        $this->data['objecttable'] = 'eadmaterial';
        parent::init();
    }
    // You might need to override get_url() and get_legacy_log_data() if view mode needs to be stored as well.
}