<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * The eadmaterial configuration variables.
 *
 * The values defined here are often used as defaults for all module instances.
 *
 * @package   mod_eadmaterial
 * @copyright 2010 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/mod/eadmaterial/locallib.php');
    
    // Introductory heading and explanation for all settings defaults
    $settings->add(new admin_setting_heading('eadmaterialintro', get_string('settingsheader', 'eadmaterial'),
                   get_string('settingsintro', 'eadmaterial')));

    // Add your settings elements here and remove this lines.
    // In case you want a date selector element, check MDL-24413
}
