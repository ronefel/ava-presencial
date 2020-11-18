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

define('CLI_SCRIPT', FALSE);

$saida = 'INI: ' . date('d-m-Y H:i:s') . "\n";

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

require_once($CFG->libdir.'/clilib.php');

// now get cli options
list($options, $unrecognized) = cli_get_params(array('verbose'=>false, 'help'=>false), array('v'=>'verbose', 'h'=>'help'));

if ($unrecognized) {
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

@set_time_limit(0);
raise_memory_limit(MEMORY_HUGE);

//@rodrigofujioka.com>>> http://www.rodrigofujioka.com
/*
* InterfaceEad > v_dsk_usuario
* Campos: id_usuario, nome, sobrenome, e_mail, usuario, senha, cidade, pais, linguagem, descricao, instituicao, pagina_pessoal, departamento, fone1, fone2, endereco
* InterfaceEad > v_dsk_matricula
* Campos: id_curso, id_usuario, papel 
* OBS: o id_usuario(view) é o idnumber(Moodle)
*/
global $DB;
// we may need a lot of memory here
@set_time_limit(0);
raise_memory_limit(MEMORY_HUGE);

//Conexão com o banco de dados do RM
$hostDB = '200.10.185.40\TOTVS';
$userDB = 'InterfaceEAD';
$passDB = 'InterfaceEAD';

$link = mssql_connect($hostDB, $userDB, $passDB);

if(!$link) {
    echo 'Sem conexão.';
    die('Tente novamente mais tarde: ' . mssql_error());
}

mssql_select_db("CorporeRM");

$res = mssql_query("SELECT id_usuario, id_curso, papel FROM v_dsk_matricula");

$count = 0;

while($row = mssql_fetch_array($res))
{
    $user = $DB->get_record('user', array('idnumber' => $row['id_usuario']));

    if ($user)
    {
        $enrol->sync_user_enrolments($user);
        echo "Usuário: {$row['id_usuario']} - inscrito no Curso: {$row['id_curso']} - Papel: {$row['papel']} <br>";
        $count++;
    }
}
mssql_close($link);

echo $count;

$saida .= 'FIM: ' . date('d-m-Y H:i:s') . "\n";

file_put_contents('/tmp/sync-enrol--'.date("Y-m-d--H-i-s").'.log', $saida, FILE_APPEND);