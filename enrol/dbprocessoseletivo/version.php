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
 * DBExtended enrolment plugin version specification.
 *
 * @package    enrol
 * @subpackage dbprocessoseletivo
 * @copyright  2012 Luis Alcantara, based on code /enrol/database from Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2018112900;        // The current plugin version (Date: YYYYMMDDXX)0
$plugin->requires  = 2011112900;        // Requires this Moodle version
$plugin->maturity  = MATURITY_ALPHA;
$plugin->component = 'enrol_dbprocessoseletivo';  // Full name of the plugin (used for diagnostics)
//TODO: should we add cron sync?