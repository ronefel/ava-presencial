<?php
// This file is NOT part of Moodle - http://moodle.org/
// This is a non-core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/**
 * DBExtended enrolment plugin installation.
 *
 * @package    enrol
 * @subpackage dbprocessoseletivo
 * @copyright  2012 Luis Alcantara, based on code /enrol/database from Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_dbprocessoseletivo_install() {
    global $CFG, $DB;

    // migrate old config settings first
    if (isset($CFG->enrol_dbtype)) {
        set_config('dbtype', $CFG->enrol_dbtype, 'enrol_dbprocessoseletivo');
        unset_config('enrol_dbtype');
    }
    if (isset($CFG->enrol_dbhost)) {
        set_config('dbhost', $CFG->enrol_dbhost, 'enrol_dbprocessoseletivo');
        unset_config('enrol_dbhost');
    }
    if (isset($CFG->enrol_dbuser)) {
        set_config('dbuser', $CFG->enrol_dbuser, 'enrol_dbprocessoseletivo');
        unset_config('enrol_dbuser');
    }
    if (isset($CFG->enrol_dbpass)) {
        set_config('dbpass', $CFG->enrol_dbpass, 'enrol_dbprocessoseletivo');
        unset_config('enrol_dbpass');
    }
    if (isset($CFG->enrol_dbname)) {
        set_config('dbname', $CFG->enrol_dbname, 'enrol_dbprocessoseletivo');
        unset_config('enrol_dbname');
    }
    if (isset($CFG->enrol_dbtable)) {
        set_config('remoteenroltable', $CFG->enrol_dbtable, 'enrol_dbprocessoseletivo');
        unset_config('enrol_dbtable');
    }
    if (isset($CFG->enrol_localcoursefield)) {
        set_config('localcoursefield', $CFG->enrol_localcoursefield, 'enrol_dbprocessoseletivo');
        unset_config('enrol_localcoursefield');
    }
    if (isset($CFG->enrol_localuserfield)) {
        set_config('localuserfield', $CFG->enrol_localuserfield, 'enrol_dbprocessoseletivo');
        unset_config('enrol_localuserfield');
    }
    if (isset($CFG->enrol_db_localrolefield)) {
        set_config('localrolefield', $CFG->enrol_db_localrolefield, 'enrol_dbprocessoseletivo');
        unset_config('enrol_db_localrolefield');
    }
    if (isset($CFG->enrol_remotecoursefield)) {
        set_config('remotecoursefield', $CFG->enrol_remotecoursefield, 'enrol_dbprocessoseletivo');
        unset_config('enrol_remotecoursefield');
    }
    if (isset($CFG->enrol_remoteuserfield)) {
        set_config('remoteuserfield', $CFG->enrol_remoteuserfield, 'enrol_dbprocessoseletivo');
        unset_config('enrol_remoteuserfield');
    }
    if (isset($CFG->enrol_db_remoterolefield)) {
        set_config('remoterolefield', $CFG->enrol_db_remoterolefield, 'enrol_dbprocessoseletivo');
        unset_config('enrol_db_remoterolefield');
    }
    if (isset($CFG->enrol_db_defaultcourseroleid)) {
        set_config('defaultrole', $CFG->enrol_db_defaultcourseroleid, 'enrol_dbprocessoseletivo');
        unset_config('enrol_db_defaultcourseroleid');
    }
    unset_config('enrol_db_autocreate'); // replaced by new coruse temple sync
    if (isset($CFG->enrol_db_category)) {
        set_config('defaultcategory', $CFG->enrol_db_category, 'enrol_dbprocessoseletivo');
        unset_config('enrol_db_category');
    }
    if (isset($CFG->enrol_db_template)) {
        set_config('templatecourse', $CFG->enrol_db_template, 'enrol_dbprocessoseletivo');
        unset_config('enrol_db_template');
    }
    if (isset($CFG->enrol_db_ignorehiddencourse)) {
        set_config('ignorehiddencourses', $CFG->enrol_db_ignorehiddencourse, 'enrol_dbprocessoseletivo');
        unset_config('enrol_db_ignorehiddencourse');
    }

    unset_config('enrol_db_disableunenrol');






    // just make sure there are no leftovers after disabled plugin
    if (!$DB->record_exists('enrol', array('enrol'=>'dbprocessoseletivo'))) {
        role_unassign_all(array('component'=>'enrol_dbprocessoseletivo'));
        return;
    }
}
