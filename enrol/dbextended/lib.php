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
 * DBExtended enrolment plugin.
 *
 * This plugin synchronises enrolment and roles with external database table.
 *
 * @package    enrol
 * @subpackage dbextended
 * @copyright  2012 Luis Alcantara, based on code /enrol/database from Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/auth/ldap/auth.php'); // Rone Santos - 21/10/2019
require_once($CFG->dirroot.'/user/lib.php'); // Rone Santos - 21/10/2019
require_once($CFG->dirroot.'/group/lib.php'); // Rone Santos

/**
 * DBExtended enrolment plugin implementation.
 * @author  Petr Skoda - based on code by Martin Dougiamas, Martin Langhoff and others
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_dbextended_plugin extends enrol_plugin {
    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param object $instance
     * @return bool
     */
    public function instance_deleteable($instance) {
        if (!enrol_is_enabled('dbextended')) {
            return true;
        }
        if (!$this->get_config('dbtype') or !$this->get_config('dbhost') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            return true;
        }

        //TODO: connect to external system and make sure no users are to be enrolled in this course
        return false;
    }

    /**
     * Forces synchronisation of user enrolments with external database,
     * does not create new courses.
     *
     * @param object $user user record
     * @return void
     */
    public function sync_user_enrolments($user) {

        /**
         * Rone Santos
         * Desativando esta função!!
         * Sincroniza o usuário sem nescessidade
         */
        return;

        global $CFG, $DB;

        // we do not create all courses here intentionally because it requires full sync and is slow
        // alternativally, we create only the users' new courses. Use only under systems with little course enrolments instances number per user.
        if (!$this->get_config('dbtype') or !$this->get_config('dbhost') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            return;
        }

        $table            = $this->get_config('remoteenroltable');
        $coursefield      = strtolower($this->get_config('remotecoursefield'));
        $userfield        = strtolower($this->get_config('remoteuserfield'));
        $rolefield        = strtolower($this->get_config('remoterolefield'));
        $rafield          = strtolower($this->get_config('remoterafield'));
        $turmadisciplinafield = strtolower($this->get_config('remoteturmadiscfield'));

        $localrolefield   = $this->get_config('localrolefield');
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');

        $unenrolaction    = $this->get_config('unenrolaction');
        $defaultrole      = $this->get_config('defaultrole');

        $ignorehidden     = $this->get_config('ignorehiddencourses');

        if (!is_object($user) or !property_exists($user, 'id')) {
            throw new coding_exception('Invalid $user parameter in sync_user_enrolments()');
        }

        if (!property_exists($user, $localuserfield)) {
            debugging('Invalid $user parameter in sync_user_enrolments(), missing '.$localuserfield);
            $user = $DB->get_record('user', array('id'=>$user->id));
        }

        // create roles mapping
        $allroles = get_all_roles();
        if (!isset($allroles[$defaultrole])) {
            $defaultrole = 0;
        }
        $roles = array();
        foreach ($allroles as $role) {
            $roles[$role->$localrolefield] = $role->id;
        }

        // if new courses must be created (setup into this plugin configuration)
        if ($this->get_config('createcourseonloginuserenrolment') && strlen($this->get_config('newcourseidnumber'))>0) {
            $new_courses_list = '';
            $new_courses_number = 0;

            // open db connection
            $connectionOptions = array(
                "Database" => $this->get_config('dbname'),
                "UID" => $this->get_config('dbuser'),
                "PWD" => $this->get_config('dbpass'),
                "CharacterSet" => "UTF-8"
            );

            $link = sqlsrv_connect($this->get_config('dbhost'), $connectionOptions);
            if(!$link){
                mtrace('Error while communicating with external enrolment database');
                var_dump(sqlsrv_errors());
                return;
            }

            // read remote enrols and add new courses list as required
            $sql_pre = $this->db_get_sql($table, array($userfield=>$user->$localuserfield), array(), false);

            if ($rs_pre = sqlsrv_query($link, $sql_pre)) {
                if (!$rs_pre->EOF) {
                    while ($fields_pre  = sqlsrv_fetch_array( $rs_pre, SQLSRV_FETCH_ASSOC)) {
                        $fields_pre = array_change_key_case($fields_pre, CASE_LOWER);

                        if (empty($fields_pre[$coursefield])) {
                            // missing course info
                            continue;
                        }
                        if (!$course = $DB->get_record('course', array($localcoursefield=>$fields_pre[$coursefield]), 'id,visible')) {
                            // if new courses must be created
                            if ($this->get_config('createcourseonloginuserenrolment') && strlen($this->get_config('newcourseidnumber'))>0) {
                                $new_courses_number++;

                                // Check if course creation limit reached. (up to 50 maximum)
                                if ($new_courses_number > $this->get_config('maxcreatecourseonlogin')) {
                                    // Skip other courses creation for now.
                                    continue;
                                }

                                // add them to the filtering list
                                if (strlen($new_courses_list)==0)
                                    $new_courses_list = "'".$fields_pre[$coursefield]."'";
                                else
                                    $new_courses_list .= ", '".$fields_pre[$coursefield]."'";
                            } else {
                                continue;
                            }
                        }
                    }
                }
                unset($fields_pre, $sql_pre);   // free some memory
                sqlsrv_free_stmt($rs_pre);
                sqlsrv_close($link);

            } else {
                // bad luck, something is wrong with the db connection
                sqlsrv_close($link);
                return;
            }
            // create required remote courses locally
            $newcourses_status = $this->sync_user_courses($new_courses_list);

            unset($new_courses_list, $newcourses_status);   // free some memory
        }

        $enrols = array();
        $instances = array();
        $ra_user_course = array();
        $turmadisc_user_course = array();

        $connectionOptions = array(
            "Database" => $this->get_config('dbname'),
            "UID" => $this->get_config('dbuser'),
            "PWD" => $this->get_config('dbpass'),
            "CharacterSet" => "UTF-8"
        );

        $link = sqlsrv_connect($this->get_config('dbhost'), $connectionOptions);
        if(!$link){
            mtrace('Error while communicating with external enrolment database');
            var_dump(sqlsrv_errors());
            return;
        }

        // read remote enrols and create instances
        $sql = $this->db_get_sql($table, array($userfield=>$user->$localuserfield), array(), false);

        if ($rs = sqlsrv_query($link, $sql)) {
            if (!$rs->EOF) {
                while ($fields  = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
                    $fields = array_change_key_case($fields, CASE_LOWER);

                    if (empty($fields[$coursefield])) {
                        // missing course info
                        continue;
                    }
                    if (!$course = $DB->get_record('course', array($localcoursefield=>$fields[$coursefield]), 'id,visible')) {
                        continue;
                    }
                    if (!$course->visible and $ignorehidden) {
                        continue;
                    }

                    if (empty($fields[$rolefield]) or !isset($roles[$fields[$rolefield]])) {
                        if (!$defaultrole) {
                            // role is mandatory
                            continue;
                        }
                        $roleid = $defaultrole;
                    } else {
                        $roleid = $roles[$fields[$rolefield]];
                    }
                    
                    if($rafield){
                        $ra_user_course[$course->id] = $fields[$rafield];
                    }

                    if($turmadisciplinafield){
                        $turmadisc_user_course[$course->id] = $fields[$turmadisciplinafield];
                    }

                    if (empty($enrols[$course->id])) {
                        $enrols[$course->id] = array();
                    }
                    $enrols[$course->id][] = $roleid;

                    if ($instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'dbextended'), '*', IGNORE_MULTIPLE)) {
                        $instances[$course->id] = $instance;
                        continue;
                    }

                    $enrolid = $this->add_instance($course);
                    $instances[$course->id] = $DB->get_record('enrol', array('id'=>$enrolid));
                }
            }
            sqlsrv_free_stmt($rs);
            sqlsrv_close($link);

        } else {
            // bad luck, something is wrong with the db connection
            sqlsrv_close($link);
            return;
        }

        // enrol user into courses and sync roles
        foreach ($enrols as $courseid => $roles) {
            if (!isset($instances[$courseid])) {
                // ignored
                continue;
            }
            $instance = $instances[$courseid];

            if ($e = $DB->get_record('user_enrolments', array('userid'=>$user->id, 'enrolid'=>$instance->id))) {
                // reenable enrolment when previously disable enrolment refreshed
                if ($e->status == ENROL_USER_SUSPENDED) {
                    $this->update_user_enrol($instance, $user->id, ENROL_USER_ACTIVE);
                }
            } else {
                $roleid = reset($roles);
                $ra_usuario = null;    
                $turma_disc_usuario = null;
                if( array_key_exists($courseid, $ra_user_course)){
                    $ra_usuario = $ra_user_course[$courseid];
                }
                if( array_key_exists($courseid, $turmadisc_user_course)){
                    $turma_disc_usuario = $turmadisc_user_course[$courseid];
                }
                $this->enrol_user($instance, $user->id, $roleid, 0, 0, ENROL_USER_ACTIVE, null, $ra_usuario, $turma_disc_usuario);
            }

            if (!$context = context_course::instance($instance->courseid)) {
                //weird
                continue;
            }
            $current = $DB->get_records('role_assignments', array('contextid'=>$context->id, 'userid'=>$user->id, 'component'=>'enrol_dbextended', 'itemid'=>$instance->id), '', 'id, roleid');

            $existing = array();
            foreach ($current as $r) {
                if (in_array($r->roleid, $roles)) {
                    $existing[$r->roleid] = $r->roleid;
                } else {
                    role_unassign($r->roleid, $user->id, $context->id, 'enrol_dbextended', $instance->id);
                }
            }
            foreach ($roles as $rid) {
                if (!isset($existing[$rid])) {
                    role_assign($rid, $user->id, $context->id, 'enrol_dbextended', $instance->id);
                }
            }
        }

        // unenrol as necessary
        $sql = "SELECT e.*, c.visible AS cvisible, ue.status AS ustatus
                  FROM {enrol} e
                  JOIN {user_enrolments} ue ON ue.enrolid = e.id
                  JOIN {course} c ON c.id = e.courseid
                 WHERE ue.userid = :userid AND e.enrol = 'dbextended'";
        $rs = $DB->get_recordset_sql($sql, array('userid'=>$user->id));
        foreach ($rs as $instance) {
            if (!$instance->cvisible and $ignorehidden) {
                continue;
            }

            if (!$context = context_course::instance($instance->courseid)) {
                //weird
                continue;
            }

            if (!empty($enrols[$instance->courseid])) {
                // we want this user enrolled
                continue;
            }

            // deal with enrolments removed from external table
            if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                // unenrol
                $this->unenrol_user($instance, $user->id);

            } else if ($unenrolaction == ENROL_EXT_REMOVED_KEEP) {
                // keep - only adding enrolments

            } else if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND or $unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                // disable
                if ($instance->ustatus != ENROL_USER_SUSPENDED) {
                    $this->update_user_enrol($instance, $user->id, ENROL_USER_SUSPENDED);
                }
                if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                    role_unassign_all(array('contextid'=>$context->id, 'userid'=>$user->id, 'component'=>'enrol_dbextended', 'itemid'=>$instance->id));
                }
            }
        }
        $rs->close();
    }

    /**
     * Forces synchronisation of all enrolments with external database.
     *
     * @param bool $verbose
     * @return int 0 means success, 1 db connect failure, 2 db read failure
     */
    public function sync_enrolments($verbose = false) {
        global $CFG, $DB;

        $ldap = new auth_plugin_ldap(); // Rone Santos - 21/10/2019
        
        // we do not create courses here intentionally because it requires full sync and is slow
        if (!$this->get_config('dbtype') or !$this->get_config('dbhost') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            if ($verbose) {
                mtrace('A sincronização de inscrição foi ignorada.');
            }
            return 0;
        }

        if ($verbose) {
            mtrace('Iniciando a sincronização de inscrição ...');
        }

        $connectionOptions = array(
            "Database" => $this->get_config('dbname'),
            "UID" => $this->get_config('dbuser'),
            "PWD" => $this->get_config('dbpass'),
            "CharacterSet" => "UTF-8"
        );

        $link = sqlsrv_connect($this->get_config('dbhost'), $connectionOptions);
        if(!$link){
            mtrace('Erro ao se comunicar com o banco de dados de inscrição externo');
            var_dump(sqlsrv_errors());
            return 1;
        }
        $messageprocessores = get_message_processors(true);
        $emailavailable = array_key_exists('email', $messageprocessores);
        $welcomeemailenabled = $this->get_config('enablewelcomeemail') == true;

        // we may need a lot of memory here
        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);

        // second step is to sync instances and users
        $table            = $this->get_config('remoteenroltable');
        $coursefield      = strtolower($this->get_config('remotecoursefield'));
        $userfield        = strtolower($this->get_config('remoteuserfield'));
        $rolefield        = strtolower($this->get_config('remoterolefield'));
        $idgroup          = 'idgrupocurso';
        $groupfield1      = strtolower($this->get_config('remotegroupfield'));
        $rafield          = strtolower($this->get_config('remoterafield'));
        $turmadisciplinafield = strtolower($this->get_config('remoteturmadiscfield'));

        $localrolefield   = $this->get_config('localrolefield');
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');

        $unenrolaction    = $this->get_config('unenrolaction');
        $defaultrole      = $this->get_config('defaultrole');

        // create roles mapping
        $allroles = get_all_roles();
        if (!isset($allroles[$defaultrole])) {
            $defaultrole = 0;
        }
        $roles = array();
        foreach ($allroles as $role) {
            $roles[$role->$localrolefield] = $role->id;
        }

        if ($verbose) {
            mtrace('  carregando cursos do banco de dados externo...');
        }
        // get a list of courses to be synced that are in external table
        $externalcourses = array();
        $sql = $this->db_get_sql($table, array(), array($coursefield), true);

        if ($rs = sqlsrv_query($link, $sql)) {
            if (!$rs->EOF) {
                while ($mapping = sqlsrv_fetch_array( $rs, SQLSRV_FETCH_ASSOC)) {
                    $mapping = reset($mapping);
                    
                    if (empty($mapping)) {
                        // invalid mapping
                        continue;
                    }
                    $externalcourses[$mapping] = true;
                }
            }
            sqlsrv_free_stmt($rs);
        } else {
            mtrace('Erro ao ler dados da tabela externa ' .$table);
            var_dump(sqlsrv_errors());
            sqlsrv_close($link);
            return 2;
        }
        if ($verbose) {
            mtrace('  ...carregado com sucesso');
        }

        $preventfullunenrol = empty($externalcourses);
        if ($preventfullunenrol and $unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
            if ($verbose) {
                mtrace('  Evitando a anulação da inscrição de todos os usuários atuais, porque isso pode resultar em grande perda de dados, deve haver pelo menos um registro na tabela de inscrição externa, desculpe.');
            }
        }

        // primeiro encontre todos os cursos existentes com instância enroll
        $existing = array();
        $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, e.id AS enrolid, c.shortname, c.fullname
                  FROM {course} c
                  JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'dbextended')";
        $rs = $DB->get_recordset_sql($sql); // watch out for idnumber duplicates
        foreach ($rs as $course) {
            if (empty($course->mapping)) {
                continue;
            }
            $existing[$course->mapping] = $course;
        }
        $rs->close();

        // adicione instâncias de registro necessárias que ainda não estão presentes
        $params = array();
        $localnotempty = "";
        if ($localcoursefield !== 'id') {
            $localnotempty =  "AND c.$localcoursefield <> :lcfe";
            $params['lcfe'] = $DB->sql_empty();
        }
        $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, c.shortname, c.fullname
                  FROM {course} c
             LEFT JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'dbextended')
                 WHERE e.id IS NULL $localnotempty";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $course) {
            if (empty($course->mapping)) {
                continue;
            }
            if (!isset($externalcourses[$course->mapping])) {
                // course not synced
                continue;
            }
            if (isset($existing[$course->mapping])) {
                // some duplicate, sorry
                continue;
            }
            $course->enrolid = $this->add_instance($course);
            $existing[$course->mapping] = $course;
        }
        $rs->close();

        // free memory
        unset($externalcourses);

        // sync enrolments
        $ignorehidden = $this->get_config('ignorehiddencourses');
        $sqlfields = array($userfield);

        if($coursefield) {
            $sqlfields[] = $coursefield;
        }
        if ($rolefield) {
            $sqlfields[] = $rolefield;
        }
        if($idgroup) {
            $sqlfields[] = $idgroup;
        }
        if ($groupfield1) {
            $sqlfields[] = $groupfield1;
        }
        if($rafield){
            $sqlfields[] = $rafield;
        }
        if($turmadisciplinafield){
            $sqlfields[] = $turmadisciplinafield;
        }

        if ($verbose) {
            mtrace('  carregando matriculas do banco de dados externo...');
        }
        $courses = [];
        $sql = $this->db_get_sql($table, [], $sqlfields);
        if ($rs = sqlsrv_query($link, $sql)) {
            if (!$rs->EOF) {
                while ($mapping = sqlsrv_fetch_array( $rs, SQLSRV_FETCH_ASSOC)) {
                    
                    if (empty($mapping)) {
                        // invalid mapping
                        continue;
                    }
                    $courses[$mapping[$coursefield]][] = $mapping;
                }
            }
            sqlsrv_free_stmt($rs);
        } else {
            mtrace('Erro ao ler dados da tabela externa ' .$table);
            var_dump(sqlsrv_errors());
            sqlsrv_close($link);
            return 2;
        }
        if ($verbose) {
            mtrace('  ...carregado com sucesso');
        }

        foreach ($existing as $course) {
            if ($ignorehidden and !$course->visible) {
                continue;
            }
            if (!$instance = $DB->get_record('enrol', array('id'=>$course->enrolid))) {
                continue; //weird
            }
            $context = context_course::instance($course->id);

            // get current list of enrolled users with their roles
            $current_roles  = array();
            $current_status = array();
            $user_mapping   = array();

            // obter grupos deste curso
            $grupos = $DB->get_records('groups', array('courseid' => $course->id));

            //membros de grupos atuais
            $groupmembers = array();
            $extgroups = array();

            $current_ra =  array();
            $current_turmadisc = array();
            $sql = "SELECT DISTINCT u.$localuserfield AS mapping, u.id, ue.status, ue.userid, ra.roleid, gp.id AS groupid, gp.name AS groupname,  u.firstname, u.lastname, gp.idnumber, ue.ra, ue.turma_disciplina
                      FROM {user} u
                      JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = :enrolid)
                      JOIN {enrol} e ON (e.id = ue.enrolid)
                      JOIN {role_assignments} ra ON (ra.userid = u.id AND ra.itemid = ue.enrolid AND ra.component = 'enrol_dbextended')
                      LEFT JOIN {groups_members} gm ON (gm.userid = u.id )
                      LEFT JOIN {groups} gp ON (gp.id = gm.groupid AND gp.courseid = e.courseid) 
                     WHERE u.deleted = 0";
            $params = array('enrolid'=>$instance->id);
            if ($localuserfield === 'username') {
                $sql .= " AND u.mnethostid = :mnethostid";
                $params['mnethostid'] = $CFG->mnet_localhost_id;
            }
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                $current_roles[$ue->userid][$ue->roleid] = $ue->roleid;
                $current_status[$ue->userid] = $ue->status;
                $user_mapping[$ue->mapping] = $ue->userid;
                $current_ra[$ue->userid] = $ue->ra; //Obter RA atual
                $current_turmadisc[$ue->userid] = $ue->turma_disciplina; //Obter Turma/disciplina atual
                if(!empty($ue->groupid)) {
                    $groupmembers[$ue->userid] = array('groupid' => $ue->groupid, 'name' => $ue->groupname, 'fullname' => $ue->firstname.' '.$ue->lastname, 'idnumber' => $ue->idnumber);
                }
            }
            $rs->close();

            // get list of users that need to be enrolled and their roles
            $requested_roles = array();
            $user_ra        = array(); //RA dos usuários neste curso
            $user_turmadisc = array();
           
            if ($localuserfield === 'username') {
                $usersearch = array('mnethostid'=>$CFG->mnet_localhost_id, 'deleted' =>0);
            }

            if(isset($courses[$course->mapping])){
                foreach ($courses[$course->mapping] as $fields) {
                    //Variável que define se um e-mail de inscrição vai ser enviado para este usuário
                    $send_email = false;
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    if (empty($fields[$userfield])) {
                        //user identification is mandatory!
                        continue;
                    }

                    $mapping = $fields[$userfield]; //username
                    if (!isset($user_mapping[$mapping])) {
                        $usersearch[$localuserfield] = $mapping;
                        if (!$user = $DB->get_record('user', $usersearch, '*', IGNORE_MULTIPLE)) {

                            /**
                             * Rone Santos - 21/10/2019
                             * Cria usuário que possui matrícula no TOTVS e usuário no LDAP
                             */
                            if($verbose){
                                mtrace("  criando usuário '$mapping'");
                            }
                            $new_user = [];
                            $new_user = $ldap->get_userinfo($mapping);
                            if($new_user){
                                $new_user['confirmed'] = 1;
                                $new_user['mnethostid'] = 1;
                                $new_user['auth'] = 'ldap';
                                $userid = user_create_user($new_user);
                                $user = $DB->get_record('user', ['id' => $userid], '*', IGNORE_MULTIPLE);

                                if($verbose){
                                    mtrace("  &#10004; usuário '$user->id' criado com sucesso");
                                }
                            }else{
                                //usuário possui matrícula mas não existe no LDAP
                                if($verbose){
                                    mtrace("  &#9888; não criado: usuário '$mapping' possui matrícula mas não existe no LDAP ainda");
                                }
                                continue;
                            }
                        }

                        if($course->visible) {
                            $send_email = true;
                        }
                        $user_mapping[$mapping] = $user->id;
                        $userid = $user->id;
                    } else {
                        $userid = $user_mapping[$mapping];
                    }
                    if($rafield){
                        $user_ra[$userid] = $fields[$rafield];
                    }
                    if($turmadisciplinafield){
                        $user_turmadisc[$userid] = $fields[$turmadisciplinafield];
                    }
                    if (empty($fields[$rolefield]) or !isset($roles[$fields[$rolefield]])) {
                        if (!$defaultrole) {
                            // role is mandatory
                            continue;
                        }
                        $roleid = $defaultrole;
                    } else {
                        $roleid = $roles[$fields[$rolefield]];
                    }
                    //Ello Oliveira (24/11/2017)
                    //Enviar email de inscrição no curso
                    $send_email = false;
                    $ok = false;

                    if($emailavailable && $welcomeemailenabled && $send_email && empty($current_roles[$userid]) && $roleid == 5){
                            $a = new stdClass();
                            $a->username = $user->firstname;
                            $a->coursename = $course->shortname;
                            $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id";

                            $body = $this->get_config('welcometocoursehtml', get_string('welcometocoursehtml', 'enrol_dbextended', $a));
                            $body = str_replace(array('{$profileurl}', '{$coursename}', '{$username}'), array($a->profileurl, $a->coursename, $a->username), $body);

                            $ok = email_to_user($user, $CFG->noreplyaddress, 'Notificação de Inscrição', $body, $body);
                        if($verbose){
                            if($ok){
                                mtrace("E-mail de notificação de inscrição enviado para ".$userid);
                            }
                            else{
                                mtrace("E-mail de inscrição não pôde ser enviado ".$userid);
                            }
                        }
                    }

                    $requested_roles[$userid][$roleid] = $roleid;
                    // mtrace('group 1 '.$fields[$groupfield1]);
                    // mtrace('group 2 '.$fields[$groupfield2]);
                    if( !empty($fields[$groupfield1])){ // Se o usuário está em um grupo no curso
                        if(!array_key_exists($userid,$extgroups)){
                            $extgroups[$userid] = array('group1' => null, 'group2' => null);
                        }

                        $extgroups[$userid]['group1'] = ['id' => $fields[$idgroup],'name' => $fields[$groupfield1]];
                    }
                }
            }
            unset($user_mapping);

            // enrol all users and sync roles
            foreach ($requested_roles as $userid=>$userroles) {
                foreach ($userroles as $roleid) {
                    if (empty($current_roles[$userid])) {
                        $ra_usuario = null;
                        $turmadisc_usuario = null;
                        if( array_key_exists($userid, $user_ra)){
                            $ra_usuario = $user_ra[$userid];
                        }
                        if( array_key_exists($userid, $user_turmadisc)){
                            $turmadisc_usuario = $user_turmadisc[$userid];
                        }

                        if ($verbose) {
                            mtrace("  inscrevendo: $userid ==> $course->shortname como ".$allroles[$roleid]->shortname. " RA: ".$ra_usuario. ' TURMA/DISC: '. $turmadisc_usuario);
                        }
                        $this->enrol_user($instance, $userid, $roleid, 0, 0, ENROL_USER_ACTIVE, null, $ra_usuario, $turmadisc_usuario);
                        $current_roles[$userid][$roleid] = $roleid;
                        $current_status[$userid] = ENROL_USER_ACTIVE;
                        if ($verbose) {
                            mtrace("  &#10004; inscrição feita com sucesso!");
                        }
                    }
                }

                // assign extra roles
                foreach ($userroles as $roleid) {
                    if (empty($current_roles[$userid][$roleid])) {
                        role_assign($roleid, $userid, $context->id, 'enrol_dbextended', $instance->id);
                        $current_roles[$userid][$roleid] = $roleid;
                        if ($verbose) {
                            mtrace("  atribuindo papéis: $userid ==> $course->shortname como ".$allroles[$roleid]->shortname);
                        }
                    }
                }

                // unassign removed roles
                foreach($current_roles[$userid] as $cr) {
                    if (empty($userroles[$cr])) {
                        role_unassign($cr, $userid, $context->id, 'enrol_dbextended', $instance->id);
                        unset($current_roles[$userid][$cr]);
                        if ($verbose) {
                            mtrace("  cancelando atribuição de papéis: $userid ==> $course->shortname");
                        }
                    }
                }

                // reenable enrolment when previously disable enrolment refreshed
                if ($current_status[$userid] == ENROL_USER_SUSPENDED) {
                    if ($this->update_user_enrol($instance, $userid, ENROL_USER_ACTIVE) && $verbose) {
                        mtrace("  reativando: $userid ==> $course->shortname");
                    }
                }

                // Inserindo RA e Turma/disciplina das inscrições existentes
                $ra_usuario = null;
                $turmadisc_usuario = null;
                if( array_key_exists($userid, $user_ra)){
                    $ra_usuario = $user_ra[$userid];
                }
                if( array_key_exists($userid, $user_turmadisc)){
                    $turmadisc_usuario = $user_turmadisc[$userid];
                }
                if(!empty($ra_usuario) || !empty($turmadisc_usuario)){
                    if($this->update_user_enrol($instance, $userid, ENROL_USER_ACTIVE, NULL, NULL,  $ra_usuario, $turmadisc_usuario ) && $verbose){
                        mtrace ("  inscrição atualizada para o usuário $userid no curso $course->id ");
                    }
                }
                
            }

            $groupscreate = array();

            foreach($extgroups as $userid => $grouptype){
                
                foreach($grouptype as $grouptypestr => $group){
                    if($group == null){
                        //mtrace("Grupo Externo: Usuário $userid do grupo $grouptypestr é:  $group");
                        continue;
                    }

                    //mtrace("Grupo Externo: Usuário $userid do grupo $grouptypestr é:  $group");
                    $grupoexistente = null;
                    foreach ($grupos as $g){
                        if($g->idnumber == $group['id']){
                            $grupoexistente = $g;
                            break;
                        }
                    }
                    
                    $groupidnumber = $group['id'];
                    $idnovogrupo = null;
                    $idgrupoexistente = null;
    
    
                    //groupidnumber e group são a mesma coisa
                    if(empty($grupoexistente)  && !array_key_exists($groupidnumber, $groupscreate) ){ //O grupo externo ainda não existe local e ainda não foi criado
                        $novogrupo = new stdClass();
                        $novogrupo->name = $group['name'];
                        $novogrupo->courseid = $course->id;
                        $novogrupo->idnumber = $groupidnumber;
                        $novogrupo->description = '';
                        $groupscreate[$groupidnumber] = $novogrupo ;
                        if($verbose) {
                            mtrace('Criado grupo com nome '.$group['name']. ' e idnumber '.$groupidnumber);
                        }
                        $idnovogrupo = groups_create_group($novogrupo);
    
                        $grupos[$idnovogrupo] = $DB->get_record('groups', array('id'=>$idnovogrupo));
                    }
                    else if(!empty($grupoexistente)){
                        $idgrupoexistente = $grupoexistente->id;
                    }
     
                    if(array_key_exists( $userid, $groupmembers)){ //Se o usuário existe em algum grupo local atual
                        if(  $groupmembers[$userid]['idnumber'] == $group['id'] ){ //Se o grupo local é igual ao externo
                        }
                        else{
                            if($idgrupoexistente){
                                groups_add_member($idgrupoexistente, $userid);
                            }
                            else if($idnovogrupo) {
                                groups_add_member($idnovogrupo, $userid);
                            }
                        }
                    }else{    
                        if($idgrupoexistente){
                            groups_add_member($idgrupoexistente, $userid);
                        }
                        else if($idnovogrupo) {
                            groups_add_member($idnovogrupo, $userid);
                        }
                        if($verbose) {
                            mtrace('Usuario ' . $userid . ' Não está em nenhum grupo ainda, increver no grupo ' . $group['name'] . ' do curso ' . $course->id);
                        }
                    }
                }
            }

            // deal with enrolments removed from external table
            if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                if (!$preventfullunenrol) {
                    // unenrol
                    foreach ($current_status as $userid=>$status) {
                        if (isset($requested_roles[$userid])) {
                            continue;
                        }
                        $this->unenrol_user($instance, $userid);
                        if ($verbose) {
                            mtrace("  inscrição cancelada: $userid ==> $course->shortname");
                        }
                    }
                }

            } else if ($unenrolaction == ENROL_EXT_REMOVED_KEEP) {
                // keep - only adding enrolments

            } else if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND or $unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                // disable
                foreach ($current_status as $userid=>$status) {
                    if (isset($requested_roles[$userid])) {
                        continue;
                    }
                    if ($status != ENROL_USER_SUSPENDED) {
                        if ($this->update_user_enrol($instance, $userid, ENROL_USER_SUSPENDED) && $verbose) {
                            mtrace("  suspendendo: $userid ==> $course->shortname");
                        }
                    }
                    if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                        role_unassign_all(array('contextid'=>$context->id, 'userid'=>$userid, 'component'=>'enrol_dbextended', 'itemid'=>$instance->id));
                        if ($verbose) {
                            mtrace("  cancelando a atribuição de todos os papéis: $userid ==> $course->shortname");
                        }
                    }
                }
            }
        }

        // close db connection
        sqlsrv_close($link);

        if ($verbose) {
            mtrace('... sincronização de inscrição concluída.');
        }

        return 0;
    }

    /**
     * Performs a course sync with external database for the current login user.
     *
     * It creates new courses if necessary.
     * (based on sync_courses defaul method)
     *
     * @param string $new_courses_list
     * @param bool $verbose
     * @return int 0 means success, 1 db connect failure, 4 db read failure
     */
    public function sync_user_courses($new_courses_list = "", $verbose = false) {
        global $CFG, $DB;

        // make sure we sync either enrolments or courses
        if (!$this->get_config('dbtype') or !$this->get_config('dbhost') or !$this->get_config('newcoursetable') or !$this->get_config('newcoursefullname') or !$this->get_config('newcourseshortname') or strlen($new_courses_list)==0) {
            if ($verbose) {
                mtrace('Course synchronisation skipped.');
            }
            return 0;
        }

        if ($verbose) {
            mtrace('Starting course synchronisation ...');
        }

        // we may need a lot of memory here
        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);

        $connectionOptions = array(
            "Database" => $this->get_config('dbname'),
            "UID" => $this->get_config('dbuser'),
            "PWD" => $this->get_config('dbpass'),
            "CharacterSet" => "UTF-8"
        );

        $link = sqlsrv_connect($this->get_config('dbhost'), $connectionOptions);

        if(!$link){
            mtrace('Error while communicating with external enrolment database');
            var_dump(sqlsrv_errors());
            return 1;
        }

        // first check if course categories creation enabled
        $table                      = $this->get_config('newcoursetable');
        $fullname                   = strtolower($this->get_config('newcoursefullname'));
        $shortname                  = strtolower($this->get_config('newcourseshortname'));
        $idnumber                   = strtolower($this->get_config('newcourseidnumber'));
        $category                   = strtolower($this->get_config('newcoursecategory'));
        $categoryname               = strtolower($this->get_config('newcoursecategoryname'));
        $localcoursecategoryfield   = strtolower($this->get_config('localcoursecategoryfield'));
        $hierachyseparator          = strtolower($this->get_config('newcoursecategoryhierachyseparator'));

        $sqlfields = array($fullname, $shortname);
        if ($category) {
            $sqlfields[] = $category;
        }
        if ($idnumber) {
            $sqlfields[] = $idnumber;
        }
        if ($categoryname) {
            $sqlfields[] = $categoryname;
        }
        $sql = $this->db_get_sql_in($table, $new_courses_list, $sqlfields, false);

        $createcourses = array();
        if ($rs = sqlsrv_query($link, $sql)) {
            if (!$rs->EOF) {
                while ($fields  = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
                    $fields = array_change_key_case($fields, CASE_LOWER);

                    if (empty($fields[$shortname]) or empty($fields[$fullname])) {
                        if ($verbose) {
                            mtrace('  error: invalid external course record, shortname and fullname are mandatory: ' . json_encode($fields)); // hopefully every geek can read JS, right?
                        }
                        continue;
                    }
                    if ($DB->record_exists('course', array('shortname'=>$fields[$shortname]))) {
                        // already exists
                        continue;
                    }
                    // allow empty idnumber but not duplicates
                    if ($idnumber and $fields[$idnumber] !== '' and $fields[$idnumber] !== null and $DB->record_exists('course', array('idnumber'=>$fields[$idnumber]))) {
                        if ($verbose) {
                            mtrace('  error: duplicate idnumber, can not create course: '.$fields[$shortname].' ['.$fields[$idnumber].']');
                        }
                        continue;
                    }

                    // if we have all required information for course categories creation
                    if ($category and $fields[$category] !== '' and $fields[$category] !== null
                        and $categoryname and $fields[$categoryname] !== '' and $fields[$categoryname] !== null
                        and strlen($hierachyseparator)==1) {
                        $target_category = $this->sync_course_categories($fields[$category], $fields[$categoryname]);
                    } else {
                        // allow empty category, but locate local course category if one category is set
                        if ($category and $fields[$category] !== '' and $fields[$category] !== null) {
                            if (!$target_category = $DB->get_record('course_categories', array($localcoursecategoryfield=>$fields[$category]))) {
                                if ($verbose) {
                                    mtrace('  error: invalid category identification, can not create course '.$fields[$shortname].', as the local category field ['.$localcoursecategoryfield.'] with value ['.$fields[$category].'] could not be found');
                                }
                            }
                        }
                    }
                    $course = new stdClass();
                    $course->fullname  = $fields[$fullname];
                    $course->shortname = $fields[$shortname];
                    $course->idnumber  = $idnumber ? $fields[$idnumber] : NULL;
                    $course->category  = isset($target_category->id) ? $target_category->id : NULL;
                    $createcourses[] = $course;
                }
            }
            sqlsrv_free_stmt($rs);
        } else {
            mtrace('Error reading data from the external course table');
            var_dump(sqlsrv_errors());
            sqlsrv_close($link);
            return 4;
        }
        if ($createcourses) {
            require_once("$CFG->dirroot/course/lib.php");

            $templatecourse = $this->get_config('templatecourse');
            $defaultcategory = $this->get_config('defaultcategory');

            $template = false;
            if ($templatecourse) {
                if ($template = $DB->get_record('course', array('shortname'=>$templatecourse))) {
                    $templatecourse_id = $template->id;
                    unset($template->id);
                    unset($template->fullname);
                    unset($template->shortname);
                    unset($template->idnumber);
                } else {
                    if ($verbose) {
                        mtrace("  can not find template for new course!");
                    }
                }
            }
            if (!$template) {
                $courseconfig = get_config('moodlecourse');
                $template = new stdClass();
                $template->summary        = '';
                $template->summaryformat  = FORMAT_HTML;
                $template->format         = $courseconfig->format;
                $template->numsections    = $courseconfig->numsections;
                $template->hiddensections = $courseconfig->hiddensections;
                $template->newsitems      = $courseconfig->newsitems;
                $template->showgrades     = $courseconfig->showgrades;
                $template->showreports    = $courseconfig->showreports;
                $template->maxbytes       = $courseconfig->maxbytes;
                $template->groupmode      = $courseconfig->groupmode;
                $template->groupmodeforce = $courseconfig->groupmodeforce;
                $template->visible        = $courseconfig->visible;
                $template->lang           = $courseconfig->lang;
                $template->groupmodeforce = $courseconfig->groupmodeforce;
            }
            if (!$DB->record_exists('course_categories', array('id'=>$defaultcategory))) {
                if ($verbose) {
                    mtrace("  default course category does not exist!");
                }
                $categories = $DB->get_records('course_categories', array(), 'sortorder', 'id', 0, 1);
                $first = reset($categories);
                $defaultcategory = $first->id;
            }

            $new_courses_forced_hidden = $this->get_config('createdcourseforcehidden', 0);

            foreach ($createcourses as $fields) {
                $newcourse = clone($template);
                $newcourse->fullname  = $fields->fullname;
                $newcourse->shortname = $fields->shortname;
                $newcourse->idnumber  = $fields->idnumber;
                $newcourse->category  = $fields->category ? $fields->category : $defaultcategory;
                if ($new_courses_forced_hidden)
                    $newcourse->visible  = 0;

                // Detect duplicate data once again, above we can not find duplicates
                // in external data using DB collation rules...
                if ($DB->record_exists('course', array('shortname' => $newcourse->shortname))) {
                    if ($verbose) {
                        mtrace("  can not insert new course, duplicate shortname detected: ".$newcourse->shortname);
                    }
                    continue;
                } else if (!empty($newcourse->idnumber) and $DB->record_exists('course', array('idnumber' => $newcourse->idnumber))) {
                    if ($verbose) {
                        mtrace("  can not insert new course, duplicate idnumber detected: ".$newcourse->idnumber);
                    }
                    continue;
                }
                $c = create_course($newcourse);
                if ($verbose) {
                    mtrace("  creating course: $c->id, $c->fullname, $c->shortname, $c->idnumber, $c->category");
                }
                // OLD CODE DIGITAL SK: create sections based on template course
                // if ($c->id and $templatecourse_id) {
                //     $createsections = $DB->get_records('course_sections', array('course' => $templatecourse_id), 'section ASC', 'id, section, name, summary, summaryformat, sequence, visible');
                //     foreach ($createsections as $sectionfields) {
                //         // skip the first section, as it is created under create_course();
                //         if ($sectionfields->section == 0) {
                //             // The news forum will be automatically be created when the course is first edited
                //             continue;
                //         }
                //         $newsection = new stdClass();
                //         $newsection->course         = $c->id;
                //         $newsection->section        = $sectionfields->section;
                //         $newsection->name           = $sectionfields->name;
                //         $newsection->summary        = $sectionfields->summary;
                //         $newsection->summaryformat  = $sectionfields->summaryformat;
                //         $newsection->visible        = $sectionfields->visible;

                //         $DB->insert_record('course_sections', $newsection, true, true);
                //     }
                // }

                // NEW CODE FUJIDEIA: import sections/activities from template course
                // if ($c->id and $templatecourse_id) { //new course created id AND template course id
                //     require_once($CFG->dirroot . '/course/externallib.php');
                //     core_course_external::import_course($templatecourse_id, $c->id, 0);
                // }

            }

            unset($createcourses);
            unset($template);
        }

        // close db connection
        sqlsrv_close($link);


        if ($verbose) {
            mtrace('... course synchronisation finished.');
        }

        return 0;
    }

    /**
     * Performs course categories hierarchy creation sync with external database, if available.
     *
     * First it checks if all required data is present,
     *  then it checks if the last level category already exists,
     *  then creates the new course categories, as required
     *
     * @param string $categories_idnumbers  - the categories idnumbers list
     * @param string $categories_names      - the categories names list
     * @return object The course category that will receive the new course,
     *          or null if configuration or external data error
     */
    function sync_course_categories($categories_idnumbers = '', $categories_names = '') {
        global $CFG, $DB;

        $hierachyseparator = strtolower($this->get_config('newcoursecategoryhierachyseparator'));
        $parent_category_id = $this->get_config('defaultcategory', 0); // get the starting parent category definition
        $localcoursecategoryfield = strtolower($this->get_config('localcoursecategoryfield'));
        $target_category = null;

        $categories_list = explode($hierachyseparator, $categories_idnumbers);
        $categories_names_list = explode($hierachyseparator, $categories_names);

        $categories_count = count($categories_list);

        // if every desired category has a target name
        if ($categories_count == count($categories_names_list) and $categories_count > 0) {

            // first we check if the last level category exists (should help speeding up in most cases)
            if ($target_category_found = $DB->get_record('course_categories', array($localcoursecategoryfield=>$categories_list[$categories_count-1]))) {
                return $target_category_found;
            }

            // create all course categories hierarchy
            for ($x = 0; $x<count($categories_list) ; $x++) {
                // search for this category by idnumber
                if (!$category_found = $DB->get_record('course_categories', array($localcoursecategoryfield=>$categories_list[$x]))) {
                    $newcategory = new stdClass();
                    $newcategory->name = $categories_names_list[$x];
                    $newcategory->idnumber = $categories_list[$x];
                    $newcategory->parent = $parent_category_id; // if $data->parent = 0, the new category will be a top-level category
                    $newcategory->description = get_string('created_from_dbextended', 'enrol_dbextended');
                    $newcategory->sortorder = 999;
                    $newcategory->visible = 1;
                    $newcategory->timemodified = time();
                    $newcategory->id = $DB->insert_record('course_categories', $newcategory);

                    // update the parent category for the next course category to create
                    $parent_category_id = $newcategory->id;
                    $newcategory_context = context_coursecat::instance($newcategory->id);
                    $newcategory_context->mark_dirty();
                    $target_category = $newcategory;
                } else {
                    $parent_category_id = $category_found->id;
                    $target_category = $category_found;
                }
            }
        }
        return $target_category;    // the course category to create courses to
    }

    /**
     * Performs a full sync with external database.
     *
     * First it creates new courses if necessary, then
     * enrols and unenrols users.
     *
     * @param bool $verbose
     * @return int 0 means success, 1 db connect failure, 4 db read failure
     */
    public function sync_courses($verbose = false) {
        global $CFG, $DB;

        // make sure we sync either enrolments or courses
        if (!$this->get_config('dbtype') or !$this->get_config('dbhost') or !$this->get_config('newcoursetable') or !$this->get_config('newcoursefullname') or !$this->get_config('newcourseshortname')) {
            if ($verbose) {
                mtrace('Sincronização de curso ignorada.');
            }
            return 0;
        }

        if ($verbose) {
            mtrace('Iniciando a sincronização de curso ...');
        }

        // we may need a lot of memory here
        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);

        if (!$extdb = $this->db_init()) {
            mtrace('Erro ao se comunicar com o banco de dados de inscrição externo');
            var_dump(sqlsrv_errors());
            return 1;
        }

        // first create new courses
        $table                      = $this->get_config('newcoursetable');
        $fullname                   = strtolower($this->get_config('newcoursefullname'));
        $shortname                  = strtolower($this->get_config('newcourseshortname'));
        $idnumber                   = strtolower($this->get_config('newcourseidnumber'));
        $category                   = strtolower($this->get_config('newcoursecategory'));
        $categoryname               = strtolower($this->get_config('newcoursecategoryname'));
        $localcoursecategoryfield   = strtolower($this->get_config('localcoursecategoryfield'));
        $hierachyseparator          = strtolower($this->get_config('newcoursecategoryhierachyseparator'));

        $sqlfields = array($fullname, $shortname);
        if ($category) {
            $sqlfields[] = $category;
        }
        if ($idnumber) {
            $sqlfields[] = $idnumber;
        }
        if ($categoryname) {
            $sqlfields[] = $categoryname;
        }
        $sql = $this->db_get_sql($table, array(), $sqlfields);
        $createcourses = array();
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);
                    if (empty($fields[$shortname]) or empty($fields[$fullname])) {
                        if ($verbose) {
                            mtrace('  erro: registro de curso externo inválido, nome abreviado e nome completo são obrigatórios: ' . json_encode($fields)); // hopefully every geek can read JS, right?
                        }
                        continue;
                    }
                    if ($DB->record_exists('course', array('shortname'=>$fields[$shortname]))) {
                        // already exists
                        continue;
                    }
                    // allow empty idnumber but not duplicates
                    if ($idnumber and $fields[$idnumber] !== '' and $fields[$idnumber] !== null and $DB->record_exists('course', array('idnumber'=>$fields[$idnumber]))) {
                        // if ($verbose) {
                        //     mtrace('  error: duplicate idnumber, can not create course: '.$fields[$shortname].' ['.$fields[$idnumber].']');
                        // }
                        continue;
                    }
                    // if we have all required information for course categories creation
                    if ($category and $fields[$category] !== '' and $fields[$category] !== null
                        and $categoryname and $fields[$categoryname] !== '' and $fields[$categoryname] !== null
                        and strlen($hierachyseparator)==1) {
                        $target_category = $this->sync_course_categories($fields[$category], $fields[$categoryname]);
                    } else {
                        // allow empty category, but locate local course category if one category is set
                        if ($category and $fields[$category] !== '' and $fields[$category] !== null) {
                            if (!$target_category = $DB->get_record('course_categories', array($localcoursecategoryfield=>$fields[$category]))) {
                                if ($verbose) {
                                    mtrace('  erro: identificação de categoria inválida, não é possível criar o curso '.$fields[$shortname].', pois o campo da categoria local ['.$localcoursecategoryfield.'] com o valor ['.$fields[$category].'] não foi encontrado');
                                }
                            }
                        }
                    }
                    $course = new stdClass();
                    $course->fullname  = $fields[$fullname];
                    $course->shortname = $fields[$shortname];
                    $course->idnumber  = $idnumber ? $fields[$idnumber] : NULL;
                    $course->category  = isset($target_category->id) ? $target_category->id : NULL;
                    $createcourses[] = $course;
                }
            }
            $rs->Close();
        } else {
            mtrace('Erro ao ler dados da tabela externa ' .$table);
            var_dump(sqlsrv_errors());
            $extdb->Close();
            return 4;
        }
        if ($createcourses) {
            require_once("$CFG->dirroot/course/lib.php");
            // >>> @rodrigofujioka.com
            require_once($CFG->dirroot . '/course/externallib.php');
            // <<< @rodrigofujioka.com

            $templatecourse = $this->get_config('templatecourse');
            $defaultcategory = $this->get_config('defaultcategory');

            $template = false;
            if ($templatecourse) {
                if ($template = $DB->get_record('course', array('shortname'=>$templatecourse))) {
                    $templatecourse_id = $template->id;
                    unset($template->id);
                    unset($template->fullname);
                    unset($template->shortname);
                    unset($template->idnumber);
                } else {
                    if ($verbose) {
                        mtrace("  não foi possível encontrar o modelo para o novo curso!");
                    }
                }
            }
            if (!$template) {
                $courseconfig = get_config('moodlecourse');
                $template = new stdClass();
                $template->summary        = '';
                $template->summaryformat  = FORMAT_HTML;
                $template->format         = $courseconfig->format;
                $template->numsections    = $courseconfig->numsections;
                $template->hiddensections = $courseconfig->hiddensections;
                $template->newsitems      = $courseconfig->newsitems;
                $template->showgrades     = $courseconfig->showgrades;
                $template->showreports    = $courseconfig->showreports;
                $template->maxbytes       = $courseconfig->maxbytes;
                $template->groupmode      = $courseconfig->groupmode;
                $template->groupmodeforce = $courseconfig->groupmodeforce;
                $template->visible        = $courseconfig->visible;
                $template->lang           = $courseconfig->lang;
                $template->groupmodeforce = $courseconfig->groupmodeforce;
            }
            if (!$DB->record_exists('course_categories', array('id'=>$defaultcategory))) {
                if ($verbose) {
                    mtrace("  a categoria padrão do curso não existe!");
                }
                $categories = $DB->get_records('course_categories', array(), 'sortorder', 'id', 0, 1);
                $first = reset($categories);
                $defaultcategory = $first->id;
            }

            $new_courses_forced_hidden = $this->get_config('createdcourseforcehidden', 0);

            foreach ($createcourses as $fields) {
                $newcourse = clone($template);
                $newcourse->fullname  = $fields->fullname;
                $newcourse->shortname = $fields->shortname;
                $newcourse->idnumber  = $fields->idnumber;
                $newcourse->category  = $fields->category ? $fields->category : $defaultcategory;
                if ($new_courses_forced_hidden)
                    $newcourse->visible  = 0;

                // Detect duplicate data once again, above we can not find duplicates
                // in external data using DB collation rules...
                if ($DB->record_exists('course', array('shortname' => $newcourse->shortname))) {
                    if ($verbose) {
                        mtrace("  não é possível inserir novo curso, detectado nome abreviado duplicado: ".$newcourse->shortname);
                    }
                    continue;
                } else if (!empty($newcourse->idnumber) and $DB->record_exists('course', array('idnumber' => $newcourse->idnumber))) {
                    if ($verbose) {
                        mtrace("  não é possível inserir novo curso, detectado idnumber duplicado: ".$newcourse->idnumber);
                    }
                    continue;
                }
                $c = create_course($newcourse);
                if ($verbose) {
                    mtrace("  criando curso: $c->id, $c->fullname, $c->shortname, $c->idnumber, $c->category");
                }
                // OLD CODE DIGITAL SK: create sections based on template course
                // if ($c->id and $templatecourse_id) {
                //     $createsections = $DB->get_records('course_sections', array('course' => $templatecourse_id), 'section ASC', 'id, section, name, summary, summaryformat, sequence, visible');
                //     foreach ($createsections as $sectionfields) {
                //         // skip the first section, as it is created under create_course();
                //         if ($sectionfields->section == 0) {
                //             // The news forum will be automatically be created when the course is first edited
                //             continue;
                //         }
                //         $newsection = new stdClass();
                //         $newsection->course         = $c->id;
                //         $newsection->section        = $sectionfields->section;
                //         $newsection->name           = $sectionfields->name;
                //         $newsection->summary        = $sectionfields->summary;
                //         $newsection->summaryformat  = $sectionfields->summaryformat;
                //         $newsection->visible        = $sectionfields->visible;

                //         $DB->insert_record('course_sections', $newsection, true, true);
                //     }
                // }

                // NEW CODE FUJIDEIA: import sections/activities from template course
                if ($c->id && $templatecourse_id) { //new course created id AND template course id
                    static::import_course($templatecourse_id, $c->id, 0);
                    // mtrace(">>> Duplicated Course: $c->id, $c->fullname, $c->shortname, $c->idnumber, $c->category");
                }
            }

            unset($createcourses);
            unset($template);
        }

        // close db connection
        $extdb->Close();

        if ($verbose) {
            mtrace('... sincronização de curso concluída.');
        }

        return 0;
    }




    protected function db_get_sql($table, array $conditions, array $fields, $distinct = false, $sort = "") {
        $fields = $this->db_quote_fields($fields);
        $where = array();
        if ($conditions) {
            foreach ($conditions as $key=>$value) {
                $value = $this->db_encode($this->db_addslashes($value));

                $where[] = $key." = '$value'";
            }
        }
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        $sort = $sort ? "ORDER BY $sort" : "";
        $distinct = $distinct ? "DISTINCT" : "";
        $sql = "SELECT $distinct $fields
                  FROM $table
                 $where
                  $sort";

        return $sql;
    }

    /**
     * Searches for a list of courses to be created, used under user login, who is enroled to unexistent courses
     * @param string $table      - the external table name
     * @param string $conditions - the external filtered courses list (comma separated values)
     * @param array $fields      - the list of field to retrieve
     * @param boolean $distinct  - distinct search
     * @param string $sort       - sort order
     * @return string - the sql query built
     */
    protected function db_get_sql_in($table, $conditions = "", array $fields, $distinct = false, $sort = "") {
        $fields = $this->db_quote_fields($fields);
        $where = '';
        if (strlen($conditions)>0) {
            $where = "WHERE ".$this->get_config('newcourseidnumber') . " IN (".$conditions.")";
        }
        $sort = $sort ? "ORDER BY $sort" : "";
        $distinct = $distinct ? "DISTINCT" : "";
        $sql = "SELECT $distinct $fields
                  FROM $table
                 $where
                  $sort";

        return $sql;
    }

    /**
     * Tries to make connection to the external database.
     *
     * @return null|ADONewConnection
     */
    protected function db_init() {
        global $CFG;

        require_once($CFG->libdir.'/adodb/adodb.inc.php');
        // Connect to the external database (forcing new connection)
        $extdb = ADONewConnection($this->get_config('dbtype'));
        if ($this->get_config('debugdb')) {
            $extdb->debug = true;
            ob_start(); //start output buffer to allow later use of the page headers
        }

        $result = $extdb->Connect($this->get_config('dbhost'), $this->get_config('dbuser'), $this->get_config('dbpass'), $this->get_config('dbname'), true);
        if (!$result) {
            return null;
        }

        $extdb->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($this->get_config('dbsetupsql')) {
            $extdb->Execute($this->get_config('dbsetupsql'));
        }
        return $extdb;
    }

    protected function db_addslashes($text) {
        // using custom made function for now
        if ($this->get_config('dbsybasequoting')) {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(array('\'', '"', "\0"), array('\\\'', '\\"', '\\0'), $text);
        } else {
            $text = str_replace("'", "''", $text);
        }
        return $text;
    }

    protected function db_encode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_encode($value);
            }
            return $text;
        } else {
            return core_text::convert($text, 'utf-8', $dbenc);
        }
    }

    protected function db_decode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_decode($value);
            }
            return $text;
        } else {
            return core_text::convert($text, $dbenc, 'utf-8');
        }
    }

    /**
     * Translates an array of fields to a formated query field list for the external database
     * @param array $fields - the list of fields to format
     * @return string - A quoted fields csv list
     */
    protected function db_quote_fields($fields=array()) {
        if (!$dbtype = $this->get_config('dbtype')) {
            return $fields;
        }
        $fields = $fields ? implode(',', $fields) : "*";
        return $fields;
    }


    /**
     * ******************************************************
     * Ello Oliveira 30/10/2017
     * Os métodos a seguir (validate_parameters, import_course) foram copiados da classe core_course_external e alterados
     * Isso é para evitarmos chamar e alterar o método import_course original, que
     * tentava renderizar a página do curso na importação, o que resultava em erro
     */

    public static function validate_parameters(external_description $description, $params) {
        if ($description instanceof external_value) {
            if (is_array($params) or is_object($params)) {
                throw new invalid_parameter_exception('Scalar type expected, array or object received.');
            }

            if ($description->type == PARAM_BOOL) {
                // special case for PARAM_BOOL - we want true/false instead of the usual 1/0 - we can not be too strict here ;-)
                if (is_bool($params) or $params === 0 or $params === 1 or $params === '0' or $params === '1') {
                    return (bool)$params;
                }
            }
            $debuginfo = 'Invalid external api parameter: the value is "' . $params .
                '", the server was expecting "' . $description->type . '" type';
            return validate_param($params, $description->type, $description->allownull, $debuginfo);

        } else if ($description instanceof external_single_structure) {
            if (!is_array($params)) {
                throw new invalid_parameter_exception('Only arrays accepted. The bad value is: \''
                    . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($description->keys as $key=>$subdesc) {
                if (!array_key_exists($key, $params)) {
                    if ($subdesc->required == VALUE_REQUIRED) {
                        throw new invalid_parameter_exception('Missing required key in single structure: '. $key);
                    }
                    if ($subdesc->required == VALUE_DEFAULT) {
                        try {
                            $result[$key] = static::validate_parameters($subdesc, $subdesc->default);
                        } catch (invalid_parameter_exception $e) {
                            //we are only interested by exceptions returned by validate_param() and validate_parameters()
                            //(in order to build the path to the faulty attribut)
                            throw new invalid_parameter_exception($key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                        }
                    }
                } else {
                    try {
                        $result[$key] = static::validate_parameters($subdesc, $params[$key]);
                    } catch (invalid_parameter_exception $e) {
                        //we are only interested by exceptions returned by validate_param() and validate_parameters()
                        //(in order to build the path to the faulty attribut)
                        throw new invalid_parameter_exception($key." => ".$e->getMessage() . ': ' .$e->debuginfo);
                    }
                }
                unset($params[$key]);
            }
            if (!empty($params)) {
                throw new invalid_parameter_exception('Unexpected keys (' . implode(', ', array_keys($params)) . ') detected in parameter array.');
            }
            return $result;

        } else if ($description instanceof external_multiple_structure) {
            if (!is_array($params)) {
                throw new invalid_parameter_exception('Only arrays accepted. The bad value is: \''
                    . print_r($params, true) . '\'');
            }
            $result = array();
            foreach ($params as $param) {
                $result[] = static::validate_parameters($description->content, $param);
            }
            return $result;

        } else {
            throw new invalid_parameter_exception('Invalid external api description');
        }
    }

    public static function import_course($importfrom, $importto, $deletecontent = 0, $options = array()) {
        global $CFG, $USER, $DB;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Parameter validation.
        $params = self::validate_parameters(
            new external_function_parameters(
                array(
                    'importfrom' => new external_value(PARAM_INT, 'the id of the course we are importing from'),
                    'importto' => new external_value(PARAM_INT, 'the id of the course we are importing to'),
                    'deletecontent' => new external_value(PARAM_INT, 'whether to delete the course content where we are importing to (default to 0 = No)', VALUE_DEFAULT, 0),
                    'options' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'name' => new external_value(PARAM_ALPHA, 'The backup option name:
                                            "activities" (int) Include course activites (default to 1 that is equal to yes),
                                            "blocks" (int) Include course blocks (default to 1 that is equal to yes),
                                            "filters" (int) Include course filters  (default to 1 that is equal to yes)'
                                ),
                                'value' => new external_value(PARAM_RAW, 'the value for the option 1 (yes) or 0 (no)'
                                )
                            )
                        ), VALUE_DEFAULT, array()
                    ),
                )
            ),
            array(
                'importfrom' => $importfrom,
                'importto' => $importto,
                'deletecontent' => $deletecontent,
                'options' => $options
            )
        );

        if ($params['deletecontent'] !== 0 and $params['deletecontent'] !== 1) {
            throw new moodle_exception('invalidextparam', 'webservice', '', $params['deletecontent']);
        }

        // Context validation.

        if (! ($importfrom = $DB->get_record('course', array('id'=>$params['importfrom'])))) {
            throw new moodle_exception('invalidcourseid', 'error');
        }

        if (! ($importto = $DB->get_record('course', array('id'=>$params['importto'])))) {
            throw new moodle_exception('invalidcourseid', 'error');
        }

        $importfromcontext = context_course::instance($importfrom->id);

        $importtocontext = context_course::instance($importto->id);

        $backupdefaults = array(
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1
        );

        $backupsettings = array();

        // Check for backup and restore options.
        if (!empty($params['options'])) {
            foreach ($params['options'] as $option) {

                // Strict check for a correct value (allways 1 or 0, true or false).
                $value = clean_param($option['value'], PARAM_INT);

                if ($value !== 0 and $value !== 1) {
                    throw new moodle_exception('invalidextparam', 'webservice', '', $option['name']);
                }

                if (!isset($backupdefaults[$option['name']])) {
                    throw new moodle_exception('invalidextparam', 'webservice', '', $option['name']);
                }

                $backupsettings[$option['name']] = $value;
            }
        }

        // Capability checking.

        require_capability('moodle/backup:backuptargetimport', $importfromcontext);
        require_capability('moodle/restore:restoretargetimport', $importtocontext);

        $bc = new backup_controller(backup::TYPE_1COURSE, $importfrom->id, backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id);

        foreach ($backupsettings as $name => $value) {
            $bc->get_plan()->get_setting($name)->set_value($value);
        }

        $backupid       = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();

        $bc->execute_plan();
        $bc->destroy();

        // Restore the backup immediately.

        // Check if we must delete the contents of the destination course.
        if ($params['deletecontent']) {
            $restoretarget = backup::TARGET_EXISTING_DELETING;
        } else {
            $restoretarget = backup::TARGET_EXISTING_ADDING;
        }

        $rc = new restore_controller($backupid, $importto->id,
            backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id, $restoretarget);

        foreach ($backupsettings as $name => $value) {
            $rc->get_plan()->get_setting($name)->set_value($value);
        }

        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();
            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backupbasepath);
                }

                $errorinfo = '';

                foreach ($precheckresults['errors'] as $error) {
                    $errorinfo .= $error;
                }

                if (array_key_exists('warnings', $precheckresults)) {
                    foreach ($precheckresults['warnings'] as $warning) {
                        $errorinfo .= $warning;
                    }
                }

                throw new moodle_exception('backupprecheckerrors', 'webservice', '', $errorinfo);
            }
        } else {
            if ($restoretarget == backup::TARGET_EXISTING_DELETING) {
                restore_dbops::delete_course_content($importto->id);
            }
        }

        $rc->execute_plan();
        $rc->destroy();

        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        return null;
    }

    /** Ello Oliveira 31/10/2017
     * Método baseado em sync_courses, com algumas alterações para melhorar a criação de cursos
     * @param bool $verbose : Se mensagens de progresso serão exibidas
     * @return int : 0 para sucesso
     */
    public function sincronizar_cursos($verbose = false){
        global $CFG, $DB;

        // make sure we sync either enrolments or courses
        if (!$this->get_config('dbtype') or !$this->get_config('dbhost') or !$this->get_config('newcoursetable') or !$this->get_config('newcoursefullname') or !$this->get_config('newcourseshortname')) {
            if ($verbose) {
                mtrace('Sincronização de curso ignorada.');
            }
            return 0;
        }

        if ($verbose) {
            mtrace('Iniciando a sincronização de curso ...');
        }

        // we may need a lot of memory here
        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);


        // first create new courses
        $table                      = $this->get_config('newcoursetable');
        $fullname                   = strtolower($this->get_config('newcoursefullname'));
        $shortname                  = strtolower($this->get_config('newcourseshortname'));
        $idnumber                   = strtolower($this->get_config('newcourseidnumber'));
        $category                   = strtolower($this->get_config('newcoursecategory'));
        $categoryname               = strtolower($this->get_config('newcoursecategoryname'));
        $localcoursecategoryfield   = strtolower($this->get_config('localcoursecategoryfield'));
        $hierachyseparator          = strtolower($this->get_config('newcoursecategoryhierachyseparator'));
        $modalidade                 = strtolower($this->get_config('newcoursemodality'));
        $tipocurso                  = strtolower($this->get_config('newcoursetype'));
        $startdate                  = strtolower($this->get_config('newcoursestartdate'));
        $enddate                    = strtolower($this->get_config('newcourseenddate'));
        $limitdaten1                = strtolower($this->get_config('newcourselimitdaten1'));
        $limitdaten2                = strtolower($this->get_config('newcourselimitdaten2'));
        $limitdaten3                = strtolower($this->get_config('newcourselimitdaten3'));

        $connectionOptions = array(
            "Database" => $this->get_config('dbname'),
            "UID" => $this->get_config('dbuser'),
            "PWD" => $this->get_config('dbpass'),
            "CharacterSet" => "UTF-8"
        );
        $host = $this->get_config('dbhost');
        $link = sqlsrv_connect($host, $connectionOptions);
        if(!$link){
            mtrace('Erro ao se comunicar com o banco de dados de inscrição externo');
            var_dump(sqlsrv_errors());
            return 1;
        }

        /**
         * Rone Santos - 21/10/2019
         * Executa a procedure PCarregaAlunosPresencial antes de qualquer coisa
         */
        $sql = "EXEC PCarregaAlunosPresencial";
        // Usuário com permissão de executar procedure
        $opt = [
            "Database" => $this->get_config('dbname'),
            "UID" => 'rm',
            "PWD" => '2B2k4?rAZuA+Cf6dakfK3NhrWF',
            "CharacterSet" => "UTF-8"
        ];
        $conn = sqlsrv_connect($host, $opt);
        $stmt = sqlsrv_prepare($conn, $sql);
        if (!sqlsrv_execute($stmt)) {
            if($verbose){
                if( ($errors = sqlsrv_errors() ) != null) {
                    foreach( $errors as $error ) {
                        mtrace("  ...ERRO: SQLSTATE: ".$error[ 'SQLSTATE'] ."; code: ".$error[ 'code']."; message: ".$error[ 'message']);
                    }
                }
            }
        }


        $sqlfields = array($fullname, $shortname);
        if ($category) {
            $sqlfields[] = $category;
        }
        if ($idnumber) {
            $sqlfields[] = $idnumber;
        }
        if ($categoryname) {
            $sqlfields[] = $categoryname;
        }
        if ($modalidade) {
            $sqlfields[] = $modalidade;
        }
        if ($tipocurso) {
            $sqlfields[] = $tipocurso;
        }
        if ($startdate) {
            $sqlfields[] = $startdate;
        }
        if ($enddate) {
            $sqlfields[] = $enddate;
        }
        if ($limitdaten1) {
            $sqlfields[] = $limitdaten1;
        }
        if ($limitdaten2) {
            $sqlfields[] = $limitdaten2;
        }
        if ($limitdaten3) {
            $sqlfields[] = $limitdaten3;
        }

        if ($verbose) {
            mtrace('  carregando cursos...');
        }
        $sql = $this->db_get_sql($table, array(), $sqlfields);
        $createcourses = array();
        if ($rs = sqlsrv_query($link, $sql)) {
            if ($verbose) {
                mtrace('  ...cursos carregados');
            }
            if (!$rs->EOF) {
                while ($fields = sqlsrv_fetch_array( $rs, SQLSRV_FETCH_ASSOC)) {
                    //$fields = array_change_key_case($fields, CASE_LOWER);
                    
                    if (empty($fields[$shortname]) or empty($fields[$fullname]) or empty($fields[$idnumber])) {
                        if ($verbose) {
                            mtrace('  erro: registro de curso externo inválido, nome abreviado, nome completo e número de identificação são obrigatórios: ' . json_encode($fields));
                        }
                        continue;
                    }

                    // se o curso já está cadastrado
                    if ($DB->record_exists('course', array('idnumber'=>$fields[$idnumber]))) {
                        if ($verbose) {
                            mtrace('  atualizando curso: '.$fields[$shortname].' ['.$fields[$idnumber].']');
                        }
                        $courseBD = $DB->get_record('course', ['idnumber' => $fields[$idnumber]]);

                        // Atualiza data inicial e final do curso
                        if ($startdate && $fields[$startdate] !== NULL) {
                            $courseBD->startdate = $fields[$startdate]->getTimestamp();
                        }
                        if ($enddate && $fields[$enddate] !== NULL) {
                            $courseBD->enddate = $fields[$enddate]->getTimestamp();
                        }

                        $DB->update_record('course', $courseBD);

                        // Atualiza data de travamento das categorias de nota TOTVS-N1, TOTVS-N2 e TOTVS-N3
                        if ($limitdaten1 && $fields[$limitdaten1] !== NULL) {
                            if($gradeItem = $this->updateGradeItemLocktime('TOTVS-N1', $courseBD->id, $fields[$limitdaten1])){
                                $this->updateGradeItemsHiddentime($gradeItem); // atualiza a data 'Oculto até' dos itens de nota da categoria
                            }
                        }
                        if ($limitdaten2 && $fields[$limitdaten2] !== NULL) {
                            if($gradeItem = $this->updateGradeItemLocktime('TOTVS-N2', $courseBD->id, $fields[$limitdaten2])){
                                $this->updateGradeItemsHiddentime($gradeItem); // atualiza a data 'Oculto até' dos itens de nota da categoria
                            }
                            // Atualiza a data de 'Oculto até' na TOTVS-NF com a data limite da TOTVS-N2
                            $this->updateGradeItemLocktime('TOTVS-NF', $courseBD->id, $fields[$limitdaten2], true);
                        }
                        if ($limitdaten3 && $fields[$limitdaten3] !== NULL) {
                            if($gradeItem = $this->updateGradeItemLocktime('TOTVS-N3', $courseBD->id, $fields[$limitdaten3])){
                                $this->updateGradeItemsHiddentime($gradeItem); // atualiza a data 'Oculto até' dos itens de nota da categoria
                            }
                        }

                        if ($verbose) {
                            mtrace('  &#10004; curso atualizado com sucesso');
                        }
                        continue;
                    }

                    // if we have all required information for course categories creation
                    if ($category and $fields[$category] !== '' and $fields[$category] !== null
                        and $categoryname and $fields[$categoryname] !== '' and $fields[$categoryname] !== null
                        and strlen($hierachyseparator)==1) {
                        $target_category = $this->sync_course_categories($fields[$category], $fields[$categoryname]);
                    } else {
                        // allow empty category, but locate local course category if one category is set
                        if ($category and $fields[$category] !== '' and $fields[$category] !== null) {
                            if (!$target_category = $DB->get_record('course_categories', array($localcoursecategoryfield=>$fields[$category]))) {
                                if ($verbose) {
                                    mtrace('  erro: identificação de categoria inválida, não é possível criar o curso '.$fields[$shortname].', pois o campo da categoria local ['.$localcoursecategoryfield.'] com o valor ['.$fields[$category].'] não foi encontrado');
                                }
                                continue;
                            }
                        }
                    }
                    $course = new stdClass();
                    $course->fullname  = $fields[$fullname];
                    $course->shortname = $fields[$shortname];
                    $course->idnumber  = $idnumber ? $fields[$idnumber] : NULL;
                    $course->category  = isset($target_category->id) ? $target_category->id : NULL;
                    $course->categorytree = explode($hierachyseparator, $fields[$categoryname]);
                    $course->modalidade = isset($fields[$modalidade])? $fields[$modalidade] : NULL;
                    $course->tipocurso = isset($fields[$tipocurso])? $fields[$tipocurso] : NULL;

                    if($startdate && isset($fields[$startdate])){
                        $course->startdate = $fields[$startdate];
                    }
                    if($enddate && isset($fields[$enddate])){
                        $course->enddate = $fields[$enddate];
                    }
                    if($limitdaten1 && isset($fields[$limitdaten1])){
                        $course->limitdaten1 = $fields[$limitdaten1];
                    }
                    if($limitdaten2 && isset($fields[$limitdaten2])){
                        $course->limitdaten2 = $fields[$limitdaten2];
                    }
                    if($limitdaten3 && isset($fields[$limitdaten3])){
                        $course->limitdaten3 = $fields[$limitdaten3];
                    }
                    $createcourses[] = $course;
                }
            }
            sqlsrv_free_stmt( $rs);
        } else {
            mtrace('Erro ao ler dados da tabela externa ' .$table);
            var_dump(sqlsrv_errors());
            sqlsrv_close( $link);
            return 4;
        }
        if ($createcourses) {
            require_once("$CFG->dirroot/course/lib.php");
            // >>> @rodrigofujioka.com
            require_once($CFG->dirroot . '/course/externallib.php');
            // <<< @rodrigofujioka.com

            $templatecourse = $this->get_config('templatecourse');
            $templatecourseead = $this->get_config('templatecourseead');
            $templatecoursepos = $this->get_config('templatecoursepos');
            $defaultcategory = $this->get_config('defaultcategory');

            $template = false;
            if ($templatecourse) {
                if ($template = $DB->get_record('course', array('shortname'=>$templatecourse))) {
                    $templatecourse_id = $template->id;
                    unset($template->id);
                    unset($template->fullname);
                    unset($template->shortname);
                    unset($template->idnumber);
                    $template->visible = 1;
                } else {
                    if ($verbose) {
                        mtrace("  não foi possível encontrar o modelo para o novo curso!");
                    }
                }
            }
            if (!$template) {
                $courseconfig = get_config('moodlecourse');
                $template = new stdClass();
                $template->summary        = '';
                $template->summaryformat  = FORMAT_HTML;
                $template->format         = $courseconfig->format;
                $template->numsections    = $courseconfig->numsections;
                $template->hiddensections = $courseconfig->hiddensections;
                $template->newsitems      = $courseconfig->newsitems;
                $template->showgrades     = $courseconfig->showgrades;
                $template->showreports    = $courseconfig->showreports;
                $template->maxbytes       = $courseconfig->maxbytes;
                $template->groupmode      = $courseconfig->groupmode;
                $template->groupmodeforce = $courseconfig->groupmodeforce;
                $template->visible        = 1;
                $template->lang           = $courseconfig->lang;
                $template->groupmodeforce = $courseconfig->groupmodeforce;
            }

            // // Ello Oliveira [06/02/2018]: Template EAD

            $templateead = false;
            if ($templatecourseead) {
                if ($templateead = $DB->get_record('course', array('shortname'=>$templatecourseead))) {
                    $templatecourseead_id = $templateead->id;
                    unset($templateead->id);
                    unset($templateead->fullname);
                    unset($templateead->shortname);
                    unset($templateead->idnumber);
                    $templateead->visible = 0;
                } else {
                    if ($verbose) {
                        mtrace("  não foi possível encontrar o modelo para o novo curso EAD!");
                    }
                }
            }
            if (!$templateead) {
                $courseconfig = get_config('moodlecourse');
                $templateead = new stdClass();
                $templateead->summary        = '';
                $templateead->summaryformat  = FORMAT_HTML;
                $templateead->format         = $courseconfig->format;
                $templateead->numsections    = $courseconfig->numsections;
                $templateead->hiddensections = $courseconfig->hiddensections;
                $templateead->newsitems      = $courseconfig->newsitems;
                $templateead->showgrades     = $courseconfig->showgrades;
                $templateead->showreports    = $courseconfig->showreports;
                $templateead->maxbytes       = $courseconfig->maxbytes;
                $templateead->groupmode      = $courseconfig->groupmode;
                $templateead->groupmodeforce = $courseconfig->groupmodeforce;
                $templateead->visible        = 0;
                $templateead->lang           = $courseconfig->lang;
                $templateead->groupmodeforce = $courseconfig->groupmodeforce;
            }

            // Ello Oliveira [16/11/2018]: Template pós
            $templatepos = false;
            if ($templatecoursepos) {
                if ($templatepos = $DB->get_record('course', array('shortname'=>$templatecoursepos))) {
                    $templatecoursepos_id = $templatepos->id;
                    unset($templatepos->id);
                    unset($templatepos->fullname);
                    unset($templatepos->shortname);
                    unset($templatepos->idnumber);
                    $templatepos->visible = 1;
                } else {
                    if ($verbose) {
                        mtrace("  não foi possível encontrar o modelo para o novo curso!");
                    }
                }
            }
            if (!$templatepos) {
                $courseconfig = get_config('moodlecourse');
                $templatepos = new stdClass();
                $templatepos->summary        = '';
                $templatepos->summaryformat  = FORMAT_HTML;
                $templatepos->format         = $courseconfig->format;
                $templatepos->numsections    = $courseconfig->numsections;
                $templatepos->hiddensections = $courseconfig->hiddensections;
                $templatepos->newsitems      = $courseconfig->newsitems;
                $templatepos->showgrades     = $courseconfig->showgrades;
                $templatepos->showreports    = $courseconfig->showreports;
                $templatepos->maxbytes       = $courseconfig->maxbytes;
                $templatepos->groupmode      = $courseconfig->groupmode;
                $templatepos->groupmodeforce = $courseconfig->groupmodeforce;
                $templatepos->visible        = 1;
                $templatepos->lang           = $courseconfig->lang;
                $templatepos->groupmodeforce = $courseconfig->groupmodeforce;
            }

            // ---------------
            if (!$DB->record_exists('course_categories', array('id'=>$defaultcategory))) {
                if ($verbose) {
                    mtrace("  a categoria padrão do curso não existe!");
                }
                $categories = $DB->get_records('course_categories', array(), 'sortorder', 'id', 0, 1);
                $first = reset($categories);
                $defaultcategory = $first->id;
            }

            $new_courses_forced_hidden = $this->get_config('createdcourseforcehidden', 0);

            foreach ($createcourses as $fields) {

                if($fields->tipocurso == '2'){
                    $template = $templatepos; //Usar o teplate pos para cursos de pós-graduação
                }
                else if($fields->tipocurso == '1' && $fields->modalidade == 'D'){
                    $template = $templateead; // caso contrário, usar o o teMplate ead para cursos da modalidade 'd'
                }
                
                $newcourse = clone($template);
                $newcourse->fullname  = $fields->fullname;
                $newcourse->shortname = $fields->shortname;
                $newcourse->idnumber  = $fields->idnumber;
                $newcourse->category  = $fields->category ? $fields->category : $defaultcategory;
                $newcourse->categorytree = $fields->categorytree;
                $newcourse->modalidade = $fields->modalidade;
                $newcourse->tipocurso = $fields->tipocurso;
                if($fields->startdate){
                    $dt = $fields->startdate;
                    $newcourse->startdate = $dt->getTimestamp();
                    $datainicial = $dt->format('d/m/Y H:i:s');
                } else {
                    $datainicial = "(padrão)";
                }
                if($fields->enddate){
                    $dt = $fields->enddate;
                    $newcourse->enddate = $dt->getTimestamp();
                    $datafinal = $dt->format('d/m/Y H:i:s');
                } else {
                    $datafinal = "(padrão)";
                }
                if ($new_courses_forced_hidden)
                    $newcourse->visible  = 0;
                
                if ($verbose) {
                    $arvoreCategoria = '[' .implode(" > ", $newcourse->categorytree). ']';
                    mtrace("  criando curso: ID externo: $newcourse->idnumber; Nome: $newcourse->fullname; Categoria: $arvoreCategoria; Modalidade: $newcourse->modalidade; Tipo: $newcourse->tipocurso; Data inicial: $datainicial; Data final: $datafinal");
                }

                // Detect duplicate data once again, above we can not find duplicates
                // in external data using DB collation rules...
                if ($DB->record_exists('course', array('shortname' => $newcourse->shortname))) {
                    if ($verbose) {
                        mtrace("    &#9888; não é possível inserir novo curso, detectado nome abreviado duplicado: ".$newcourse->shortname);
                    }
                    continue;
                } else if (!empty($newcourse->idnumber) and $DB->record_exists('course', array('idnumber' => $newcourse->idnumber))) {
                    if ($verbose) {
                        mtrace("    &#9888; Não é possível inserir um novo curso, detectado idnumber duplicado: ".$newcourse->idnumber);
                    }
                    continue;
                }

                // Ello Oliveira [06/02/2018]: Define o número de seções baseado na modalidade
                

                if(strpos($newcourse->categorytree[1], 'EAD - PRESENCIAL') !== false){
                    $newcourse->numsections = 9;
                    // Cursos da categoria EAD - PRESENCIAL ,
                    $newcourse->format = $templateead->format;
                    
                } else if($newcourse->format === 'buttons'){
                    $newcourse->numsections = 11;
                    $newcourse->showdefaultsectionname = '1';
                }

                $c = static::criar_curso($newcourse);

                // Ello Oliveira [06/02/2018]: Importa de acordo com a modalidade do curso
                if($newcourse->tipocurso==2){
                    if ($c->id && $templatecoursepos_id) {
                        if ($verbose) {
                            mtrace("    importando template do curso padrão: $templatecoursepos_id");
                        }
                        static::importar_curso($templatecoursepos_id, $c->id, 0);
                        if ($verbose) {
                            mtrace('      &#10004; sucesso');
                        }
                    }
                } else if($newcourse->modalidade == 'D' || (strpos($newcourse->categorytree[1], 'EAD - PRESENCIAL') !== false)){
                    if ($c->id && $templatecourseead_id) {
                        if ($verbose) {
                            mtrace("    importando template do curso padrão: $templatecourseead_id");
                        }
                        static::importar_curso($templatecourseead_id, $c->id, 0);
                        if ($verbose) {
                            mtrace('      &#10004; sucesso');
                        }
                    }
                }
                else {
                    if ($c->id && $templatecourse_id) { //new course created id AND template course id
                        if ($verbose) {
                            mtrace("    importando template do curso padrão: $templatecourse_id");
                        }
                        static::importar_curso($templatecourse_id, $c->id, 0);
                        if ($verbose) {
                            mtrace('      &#10004; sucesso');
                        }
                    }
                }

                // Atualiza data de travamento das categorias de nota TOTVS-N1, TOTVS-N2 e TOTVS-N3
                if ($limitdaten1 && $fields->limitdaten1) {
                    if($gradeItem = $this->updateGradeItemLocktime('TOTVS-N1', $c->id, $fields->limitdaten1)){
                        $this->updateGradeItemsHiddentime($gradeItem); // atualiza a data 'Oculto até' dos itens de nota da categoria
                    }
                }
                if ($limitdaten2 && $fields->limitdaten2) {
                    if($gradeItem = $this->updateGradeItemLocktime('TOTVS-N2', $c->id, $fields->limitdaten2)){
                        $this->updateGradeItemsHiddentime($gradeItem); // atualiza a data 'Oculto até' dos itens de nota da categoria
                    }
                    // Atualiza a data de 'Oculto até' na TOTVS-NF com a data limite da TOTVS-N2
                    $this->updateGradeItemLocktime('TOTVS-NF', $c->id, $fields->limitdaten2, true);
                }
                if ($limitdaten3 && $fields->limitdaten3 !== NULL) {
                    if($gradeItem = $this->updateGradeItemLocktime('TOTVS-N3', $c->id, $fields->limitdaten3)){
                        $this->updateGradeItemsHiddentime($gradeItem); // atualiza a data 'Oculto até' dos itens de nota da categoria
                    }
                }

                if ($verbose) {
                    mtrace("  &#10004; curso criado com sucesso");
                }

                unset($newcourse);
            }

            unset($createcourses);
            unset($template);
        }

        // close db connection
        sqlsrv_close( $link);

        if ($verbose) {
            mtrace('... sincronização de curso concluída.');
        }

        return 0;
    }

    private static function criar_curso($data, $formatoptions = NULL, $editoroptions = NULL){
        global $DB, $CFG;

        //check the categoryid - must be given for all new courses
        $category = $DB->get_record('course_categories', array('id'=>$data->category), '*', MUST_EXIST);

        // Check if the shortname already exists.
        if (!empty($data->shortname)) {
            if ($DB->record_exists('course', array('shortname' => $data->shortname))) {
                throw new moodle_exception('shortnametaken', '', '', $data->shortname);
            }
        }

        // Check if the idnumber already exists.
        if (!empty($data->idnumber)) {
            if ($DB->record_exists('course', array('idnumber' => $data->idnumber))) {
                throw new moodle_exception('courseidnumbertaken', '', '', $data->idnumber);
            }
        }

        if ($errorcode = course_validate_dates((array)$data)) {
            throw new moodle_exception($errorcode);
        }

        // Check if timecreated is given.
        $data->timecreated  = time();
        $data->timemodified = $data->timecreated;

        // place at beginning of any category
        $data->sortorder = 0;

        if ($editoroptions) {
            // summary text is updated later, we need context to store the files first
            $data->summary = '';
            $data->summary_format = FORMAT_HTML;
        }

        if (!isset($data->visible)) {
            // data not from form, add missing visibility info
            $data->visible = $category->visible;
        }
        $data->visibleold = $data->visible;

        $newcourseid = $DB->insert_record('course', $data, true);
        //Inserir dados da tabela de formato
        if($formatoptions){
            foreach ($formatoptions as $formatoption){
                $formatoption->courseid= $newcourseid;
            }
            $DB->insert_records("course_format_options", $formatoptions);
        }
        $context = context_course::instance($newcourseid, MUST_EXIST);

        if ($editoroptions) {
            // Save the files used in the summary editor and store
            $data = file_postupdate_standard_editor($data, 'summary', $editoroptions, $context, 'course', 'summary', 0);
            $DB->set_field('course', 'summary', $data->summary, array('id'=>$newcourseid));
            $DB->set_field('course', 'summaryformat', $data->summary_format, array('id'=>$newcourseid));
        }
        if ($overviewfilesoptions = course_overviewfiles_options($newcourseid)) {
            // Save the course overviewfiles
            $data = file_postupdate_standard_filemanager($data, 'overviewfiles', $overviewfilesoptions, $context, 'course', 'overviewfiles', 0);
        }

        // update course format options
        course_get_format($newcourseid)->update_course_format_options($data);

        $course = course_get_format($newcourseid)->get_course();

        fix_course_sortorder();
        // purge appropriate caches in case fix_course_sortorder() did not change anything
        cache_helper::purge_by_event('changesincourse');

        // new context created - better mark it as dirty
        $context->mark_dirty();

        // Trigger a course created event.
        $event = \core\event\course_created::create(array(
            'objectid' => $course->id,
            'context' => context_course::instance($course->id),
            'other' => array('shortname' => $course->shortname,
                'fullname' => $course->fullname)
        ));

        $event->trigger();

        // Setup the blocks
        blocks_add_default_course_blocks($course);

        // Create default section and initial sections if specified (unless they've already been created earlier).
        // We do not want to call course_create_sections_if_missing() because to avoid creating course cache.
        $numsections = isset($data->numsections) ? $data->numsections : 0;
        $existingsections = $DB->get_fieldset_sql('SELECT section from {course_sections} WHERE course = ?', [$newcourseid]);
        $newsections = array_diff(range(0, $numsections), $existingsections);

        foreach ($newsections as $sectionnum) {
            course_create_section($newcourseid, $sectionnum, true);
        }

        // Save any custom role names.
        save_local_role_names($course->id, (array)$data);

        // set up enrolments
        enrol_course_updated(true, $course, $data);

        // Update course tags.
        if (isset($data->tags)) {
            core_tag_tag::set_item_tags('core', 'course', $course->id, context_course::instance($course->id), $data->tags);
        }

        return $course;
    }

    public static function importar_curso($importfrom, $importto, $deletecontent = 0, $options = array()) {
        global $CFG, $USER, $DB;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Parameter validation.
        $params = self::validate_parameters(
            new external_function_parameters(
                array(
                    'importfrom' => new external_value(PARAM_INT, 'the id of the course we are importing from'),
                    'importto' => new external_value(PARAM_INT, 'the id of the course we are importing to'),
                    'deletecontent' => new external_value(PARAM_INT, 'whether to delete the course content where we are importing to (default to 0 = No)', VALUE_DEFAULT, 0),
                    'options' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'name' => new external_value(PARAM_ALPHA, 'The backup option name:
                                            "activities" (int) Include course activites (default to 1 that is equal to yes),
                                            "blocks" (int) Include course blocks (default to 1 that is equal to yes),
                                            "filters" (int) Include course filters  (default to 1 that is equal to yes)'
                                ),
                                'value' => new external_value(PARAM_RAW, 'the value for the option 1 (yes) or 0 (no)'
                                )
                            )
                        ), VALUE_DEFAULT, array()
                    ),
                )
            ),
            array(
                'importfrom' => $importfrom,
                'importto' => $importto,
                'deletecontent' => $deletecontent,
                'options' => $options
            )
        );

        if ($params['deletecontent'] !== 0 and $params['deletecontent'] !== 1) {
            throw new moodle_exception('invalidextparam', 'webservice', '', $params['deletecontent']);
        }

        // Context validation.
        if (! ($importfrom = $DB->get_record('course', array('id'=>$params['importfrom'])))) {
            throw new moodle_exception('invalidcourseid', 'error');
        }
        if (! ($importto = $DB->get_record('course', array('id'=>$params['importto'])))) {
            throw new moodle_exception('invalidcourseid', 'error');
        }

        $importfromcontext = context_course::instance($importfrom->id);

        $importtocontext = context_course::instance($importto->id);

        $backupdefaults = array(
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1
        );

        $backupsettings = array();

        // Check for backup and restore options.
        if (!empty($params['options'])) {
            foreach ($params['options'] as $option) {

                // Strict check for a correct value (allways 1 or 0, true or false).
                $value = clean_param($option['value'], PARAM_INT);

                if ($value !== 0 and $value !== 1) {
                    throw new moodle_exception('invalidextparam', 'webservice', '', $option['name']);
                }

                if (!isset($backupdefaults[$option['name']])) {
                    throw new moodle_exception('invalidextparam', 'webservice', '', $option['name']);
                }

                $backupsettings[$option['name']] = $value;
            }
        }

        // Capability checking.

        require_capability('moodle/backup:backuptargetimport', $importfromcontext);
        require_capability('moodle/restore:restoretargetimport', $importtocontext);

        $bc = new backup_controller(backup::TYPE_1COURSE, $importfrom->id, backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id);

        foreach ($backupsettings as $name => $value) {
            $bc->get_plan()->get_setting($name)->set_value($value);
        }

        $backupid       = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();

        $bc->execute_plan();
        $bc->destroy();

        // Restore the backup immediately.

        // Check if we must delete the contents of the destination course.
        if ($params['deletecontent']) {
            $restoretarget = backup::TARGET_EXISTING_DELETING;
        } else {
            $restoretarget = backup::TARGET_EXISTING_ADDING;
        }

        $rc = new restore_controller($backupid, $importto->id,
            backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id, $restoretarget);

        foreach ($backupsettings as $name => $value) {
            $rc->get_plan()->get_setting($name)->set_value($value);
        }

        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();
            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backupbasepath);
                }

                $errorinfo = '';

                foreach ($precheckresults['errors'] as $error) {
                    $errorinfo .= $error;
                }

                if (array_key_exists('warnings', $precheckresults)) {
                    foreach ($precheckresults['warnings'] as $warning) {
                        $errorinfo .= $warning;
                    }
                }

                throw new moodle_exception('backupprecheckerrors', 'webservice', '', $errorinfo);
            }
        } else {
            if ($restoretarget == backup::TARGET_EXISTING_DELETING) {
                restore_dbops::delete_course_content($importto->id);
            }
        }

        $rc->execute_plan();
        $rc->destroy();

        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        return null;
    }

    /**
     * Adds a specified user to a group
     *
     * @param mixed $grouporid  The group id or group object
     * @param mixed $userorid   The user id or user object
     * @param string $component Optional component name e.g. 'enrol_imsenterprise'
     * @param int $itemid Optional itemid associated with component
     * @return bool True if user added successfully or the user is already a
     * member of the group, false otherwise.
     */
    // function groups_add_member($grouporid, $userorid, $component=null, $itemid=0) {
    //     global $DB;

    //     if (is_object($userorid)) {
    //         $userid = $userorid->id;
    //         $user   = $userorid;
    //         if (!isset($user->deleted)) {
    //             $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);
    //         }
    //     } else {
    //         $userid = $userorid;
    //         $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);
    //     }

    //     if ($user->deleted) {
    //         return false;
    //     }

    //     if (is_object($grouporid)) {
    //         $groupid = $grouporid->id;
    //         $group   = $grouporid;
    //     } else {
    //         $groupid = $grouporid;
    //         $group = $DB->get_record('groups', array('id'=>$groupid), '*', MUST_EXIST);
    //     }

    //     // Check if the user a participant of the group course.
    //     $context = context_course::instance($group->courseid);
    //     if (!is_enrolled($context, $userid)) {
    //         return false;
    //     }

    //     if (groups_is_member($groupid, $userid)) {
    //         return true;
    //     }

    //     $member = new stdClass();
    //     $member->groupid   = $groupid;
    //     $member->userid    = $userid;
    //     $member->timeadded = time();
    //     $member->component = '';
    //     $member->itemid = 0;

    //     // Check the component exists if specified
    //     if (!empty($component)) {
    //         $dir = core_component::get_component_directory($component);
    //         if ($dir && is_dir($dir)) {
    //             // Component exists and can be used
    //             $member->component = $component;
    //             $member->itemid = $itemid;
    //         } else {
    //             throw new coding_exception('Invalid call to groups_add_member(). An invalid component was specified');
    //         }
    //     }

    //     if ($itemid !== 0 && empty($member->component)) {
    //         // An itemid can only be specified if a valid component was found
    //         throw new coding_exception('Invalid call to groups_add_member(). A component must be specified if an itemid is given');
    //     }

    //     $DB->insert_record('groups_members', $member);

    //     // Update group info, and group object.
    //     $DB->set_field('groups', 'timemodified', $member->timeadded, array('id'=>$groupid));
    //     $group->timemodified = $member->timeadded;

    //     // Invalidate the group and grouping cache for users.
    //     cache_helper::invalidate_by_definition('core', 'user_group_groupings', array(), array($userid));

    //     // Trigger group event.
    //     $params = array(
    //         'context' => $context,
    //         'objectid' => $groupid,
    //         'relateduserid' => $userid,
    //         'other' => array(
    //             'component' => $member->component,
    //             'itemid' => $member->itemid
    //         )
    //     );
    //     $event = \core\event\group_member_added::create($params);
    //     $event->add_record_snapshot('groups', $group);
    //     $event->trigger();

    //     return true;
    // }

    /** 
     * Atualiza a data de travamento e a data 'Oculto até' da categoria de nota.
     * 
     * Rone Santos - 31/10/2019
     * 
     * @param string $etapa etapa de nota TOTVS-N1, TOTVS-N2, TOTVS-N3, TOTVS-NF
     * @param int $courseId id do curso
     * @param DateTime $lockTime data de travamento da nota
     * @param boolean $hiddenOnly se é para atualizar somente o campo 'Oculto até' e não travar
     * @return mixed retorna a categoria de acordo com a $etapa, se não encontrar retorna false
     */
    private function updateGradeItemLocktime($etapa, $courseId, $lockTime, $hiddenOnly = false){
        global $DB;

        $lockTimeOriginal = $lockTime->getTimestamp(); // salva a data de travamento original
        $lockTimeExtended = $lockTime->modify('+2 day')->getTimestamp(); // adiciona mais dois dias na data de travamento;

        $categoryItem = NULL;
        $categoryItem = $DB->get_record_sql("SELECT id, locktime, locked, hidden, iteminstance, courseid FROM {grade_items} WHERE courseid = $courseId AND itemtype IN ('course', 'category') AND idnumber = '$etapa'");
        if ($categoryItem) {
            $categoryItem->locktime = $hiddenOnly ? 0 : $lockTimeExtended;

            // se data de travamento estendido for maior que data atual
            // destrava a categoria de nota : trava a categoria de nota
            $categoryItem->locked = $lockTimeExtended > time() ? 0 : $categoryItem->locktime;

            // se data de travamento original for maior que data atual
            // oculta a categoria de nota : desoculta a categoria de nota
            $categoryItem->hidden = $lockTimeOriginal > time() ? $lockTimeOriginal : 0;
            
            $DB->update_record('grade_items', $categoryItem);
            return $categoryItem;
        }
        return false;
    }

    /** 
     * Atualiza a data de 'Oculto até' dos items de nota com a data de 'Oculto até' da categoria.
     * 
     * Rone Santos - 31/10/2019
     * 
     * @param $categoryItem objeto da categoria de nota
     */
    private function updateGradeItemsHiddentime($categoryItem){
        global $DB;

        $DB->execute("UPDATE mdl_grade_items SET hidden = $categoryItem->hidden WHERE categoryid = $categoryItem->iteminstance AND courseid = $categoryItem->courseid");
    }
}
