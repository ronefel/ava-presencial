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
 * CLI sync for full external database synchronisation.
 *
 * Sample cron entry:
 * # 5 minutes past 4am
 * 5 4 * * * $sudo -u www-data /usr/bin/php /var/www/moodle/enrol/dbextended/cli/sync.php
 *
 * Notes:
 *   - it is required to use the web server account when executing PHP CLI scripts
 *   - you need to change the "www-data" to match the apache user account
 *   - use "su" if "sudo" not available
 *
 * @package    enrol
 * @subpackage dbextended
 * @copyright  2012 Luis Alcantara, based on code /enrol/database from Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

$saida = 'INI: ' . date('d-m-Y H:i:s') . "\n";

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/clilib.php');

// now get cli options
list($options, $unrecognized) = cli_get_params(array('verbose'=>false, 'help'=>false), array('v'=>'verbose', 'h'=>'help'));

if ( $unrecognized && !CLI_SCRIPT ) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Execute enrol sync with external database.
The enrol_dbextended plugin must be enabled and properly configured.

Options:
-v, --verbose         Print verbose progess information
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php enrol/dbextended/cli/sync.php

Sample cron entry:
# 5 minutes past 4am
5 4 * * * \$sudo -u www-data /usr/bin/php /var/www/moodle/enrol/dbextended/cli/sync.php
";

    echo $help;
    die;
}

if (!enrol_is_enabled('dbextended')) {
    echo('enrol_dbextended plugin is disabled, sync is disabled'."\n");
    exit(1);
}

$verbose = !empty($options['verbose']);
$enrol = enrol_get_plugin('dbextended');
$result = 0;


//Fujideia
$verbose = false;

global $CFG, $DB;

$user = 2;

$user = $DB->get_record('user', array('id'=>$user));

// \core\session\manager::set_user($user);
complete_user_login($user);

sesskey();

$result = $result | $enrol->sync_courses($verbose);
$result = $result | $enrol->sync_enrolments($verbose);

$saida .= "\n{$result}\n";

$saida .= 'FIM: ' . date('d-m-Y H:i:s') . "\n";

file_put_contents('/tmp/sync--'.date("Y-m-d--H-i-s").'.log', $saida, FILE_APPEND);

exit($result);