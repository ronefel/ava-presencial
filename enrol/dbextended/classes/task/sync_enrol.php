<?php
/**
 * Created by PhpStorm.
 * User: 08429611436
 * Date: 22/09/2017
 * Time: 08:38
 */
namespace enrol_dbextended\task;
defined('MOODLE_INTERNAL') || die();
//require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
global $CFG;
require_once($CFG->libdir.'/clilib.php');

/**
 * Class sync_enrol
 */
class sync_enrol extends \core\task\scheduled_task
{

    public function get_name(){
        return "Sincronizar Matrícula de Usuários";
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute()
    {
        global  $DB;
        // TODO: Implement execute() method.
        $verbose = !empty($options['verbose']);
        $enrol = enrol_get_plugin('dbextended');

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
    }
}