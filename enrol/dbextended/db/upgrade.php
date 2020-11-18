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
 * DBExtended enrolment plugin upgrade.
 *
 * @package    enrol
 * @subpackage dbextended
 * @copyright  2012 Luis Alcantara, based on code /enrol/database from Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
function xmldb_enrol_dbextended_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // fix leftovers after incorrect 2.x upgrade in install.php
    if ($oldversion < 2010073101) {
        unset_config('enrol_db_localrolefield');
        unset_config('enrol_db_remoterolefield');
        unset_config('enrol_db_disableunenrol');

        upgrade_plugin_savepoint(true, 2010073101, 'enrol', 'dbextended');
    }


    // Moodle v2.2.0 release upgrade line
    // Put any upgrade step following this

    return true;
}
