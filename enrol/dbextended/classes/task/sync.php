<?php
/**
 * Created by PhpStorm.
 * User: 08429611436
 * Date: 22/09/2017
 * Time: 15:09
 */

namespace enrol_dbextended\task;
defined('MOODLE_INTERNAL') || die();
//require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
global $CFG, $DB;
require_once($CFG->libdir.'/clilib.php');


class sync extends \core\task\scheduled_task
{

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name()
    {
        return "Sincronização de Cursos";
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute()
    {
        $saida = 'INI: ' . date('d-m-Y H:i:s') . "\n";
        if (!enrol_is_enabled('dbextended')) {
            echo('enrol_dbextended plugin is disabled, sync is disabled'."\n");
            exit(1);
        }

        $verbose = !empty($options['verbose']);
        $enrol = enrol_get_plugin('dbextended');
        $result = 0;

        $verbose =true;

// $user = 2;
// $user = $DB->get_record('user', array('id'=>$user));

// // \core\session\manager::set_user($user);
// complete_user_login($user);
// sesskey();

        //Ello Oliveira
        //A chamada anterior a sync_courses foi substituída por esta nova função
        $result = $result | $enrol->sincronizar_cursos($verbose);
        $result = $result | $enrol->sync_enrolments($verbose);

        $saida .= "\n{$result}\n";

        $saida .= 'FIM: ' . date('d-m-Y H:i:s') . "\n";

        file_put_contents('/tmp/sync--'.date("Y-m-d--H-i-s").'.log', $saida, FILE_APPEND);

        //exit($result);
    }
}