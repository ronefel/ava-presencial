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
 * @subpackage dbprocessoseletivo
 * @copyright  2012 Luis Alcantara, based on code /enrol/database from Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->dirroot/group/lib.php");
require_once("$CFG->dirroot/user/lib.php");
/**
 * DBExtended enrolment plugin implementation.
 * @author  Petr Skoda - based on code by Martin Dougiamas, Martin Langhoff and others
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_dbprocessoseletivo_plugin extends enrol_plugin {
    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param object $instance
     * @return bool
     */
    public function can_delete_instance($instance) {

        return true;
//        if (!enrol_is_enabled('dbprocessoseletivo')) {
//            return true;
//        }
//        if (!$this->get_config('dbtype') or !$this->get_config('dbhost') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
//            return true;
//        }

        //TODO: connect to external system and make sure no users are to be enrolled in this course
        //return false;
    }

    public function allow_unenrol(stdClass $instance) {
        return true;
    }

    /**
     * Does this plugin allow manual changes in user_enrolments table?
     *
     * All plugins allowing this must implement 'enrol/xxx:manage' capability
     *
     * @param stdClass $instance course enrol instance
     * @return bool - true means it is possible to change enrol period and status in user_enrolments table
     */
    public function allow_manage(stdClass $instance) {
        return true;
    }

    /**
     * Forces synchronisation of all enrolments with external database.
     *
     * @param bool $verbose
     * @return int 0 means success, 1 db connect failure, 2 db read failure
     */
    public function sync_enrolments($verbose = false) {
        global $CFG, $DB;
        $now = time();
        // we do not create courses here intentionally because it requires full sync and is slow
        if (!$this->get_config('dbtype') or !$this->get_config('dbhost') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            if ($verbose) {
                mtrace('User enrolment synchronisation skipped.');
            }
            return 0;
        }

        if ($verbose) {
            mtrace('Starting user enrolment synchronisation...');
        }

        if (!$extdb = $this->db_init()) {
            mtrace('Erro ao se comunicar com o banco de dados de inscrição externo');
            return 1;
        }
        $messageprocessores = get_message_processors(true);
        $emailavailable = array_key_exists('email', $messageprocessores);
        $welcomeemailenabled = $this->get_config('enablewelcomeemail') == true;

        // we may need a lot of memory hsere
        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);

        // second step is to sync instances and users
        $table            = $this->get_config('remoteenroltable');
        $coursefield      = strtolower($this->get_config('remotecoursefield'));
        $userfield        = strtolower($this->get_config('remoteuserfield'));
        $rolefield        = strtolower($this->get_config('remoterolefield'));
        $enroltimefield   = strtolower($this->get_config('remoteenroltimefield'));

        $firstnamefield     = strtolower($this->get_config('remotefirstnamefield'));
        $lastnamefield      = strtolower($this->get_config('remotelastnamefield'));
        $emailfield         = strtolower($this->get_config('remoteemailfield'));
        $passwordfield      = strtolower($this->get_config('remotepasswordfield'));

        $localrolefield   = $this->get_config('localrolefield');
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');

        $unenrolaction    = $this->get_config('unenrolaction');
        $defaultrole      = $this->get_config('defaultrole');
        //$groupfield = strtolower($this->get_config('remotegroupfield')); //'subturma';
        $groupfield =  $this->get_config('remotegroupfield'); //'subturma';

        // create roles mapping
        $allroles = get_all_roles();
        if (!isset($allroles[$defaultrole])) {
            $defaultrole = 0;
        }
        $roles = array();
        foreach ($allroles as $role) {
            $roles[$role->$localrolefield] = $role->id;
        }

        // get a list of courses to be synced that are in external table
        $externalcourses = array();
        $sql = $this->db_get_sql($table, array(), array($coursefield), true);

        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($mapping = $rs->FetchRow()) {
                    $mapping = reset($mapping);
                    $mapping = $this->db_decode($mapping);
                    if (empty($mapping)) {
                        // invalid mapping
                        continue;
                    }
                    $externalcourses[$mapping] = true;
                }
            }
            $rs->Close();
        } else {
            mtrace('Error reading data from the external enrolment table');
            $extdb->Close();
            return 2;
        }
        $preventfullunenrol = empty($externalcourses);
        if ($preventfullunenrol and $unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
            if ($verbose) {
                mtrace('  Preventing unenrolment of all current users, because it might result in major data loss, there has to be at least one record in external enrol table, sorry.');
            }
        }

        // first find all existing courses with enrol instance
        $existing = array();
        $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, e.id AS enrolid, c.shortname, c.fullname
                  FROM {course} c
                  JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'dbprocessoseletivo')";
        $rs = $DB->get_recordset_sql($sql); // watch out for idnumber duplicates
        foreach ($rs as $course) {
            if (empty($course->mapping)) {
                continue;
            }
            $existing[$course->mapping] = $course;
        }
        $rs->close();

        // add necessary enrol instances that are not present yet
        $params = array();
        $localnotempty = "";
        if ($localcoursefield !== 'id') {
            $localnotempty =  "AND c.$localcoursefield <> :lcfe";
            $params['lcfe'] = $DB->sql_empty();
        }
        $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, c.shortname, c.fullname
                  FROM {course} c
             LEFT JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'dbprocessoseletivo')
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
        if ($rolefield) {
            $sqlfields[] = $rolefield;
        }
        if($enroltimefield){
            $sqlfields[] = $enroltimefield;
        }

        if($firstnamefield){
            $sqlfields[] = $firstnamefield;
        }

        if($lastnamefield){
            $sqlfields[] = $lastnamefield;
        }

        if($passwordfield){
            $sqlfields[] = $passwordfield;
        }

        if($emailfield){
            $sqlfields[] = $emailfield;
        }
        if ($groupfield) {
            $sqlfields[] = $groupfield;
        }

        foreach ($existing as $course) {
            if ($ignorehidden and !$course->visible) {
                continue;
            }
            if (!$instance = $DB->get_record('enrol', array('id'=>$course->enrolid))) {
                continue; //weird
            }
            $context = get_context_instance(CONTEXT_COURSE, $course->id);

            // get current list of enrolled users with their roles
            $current_roles  = array();
            $current_status = array();
            $user_mapping   = array();
            // grupos atuais

            // obter grupos deste curso
            $grupos = $DB->get_records('groups', array('courseid' => $course->id));


            //membros de grupos atuais
            $groupmembers = array();
            $extgroups = array();
            //mtrace('enrolid'. $instance->id);
            $sql = "SELECT u.$localuserfield AS mapping, u.id, ue.status, ue.userid, ra.roleid, gp.id AS groupid, gp.name AS groupname,  u.firstname, u.lastname
                      FROM {user} u
                      JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = :enrolid)
                      JOIN {enrol} e ON (e.id = ue.enrolid)
                      JOIN {role_assignments} ra ON (ra.userid = u.id AND ra.itemid = ue.enrolid AND ra.component = 'enrol_dbprocessoseletivo')
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
                if(!empty($ue->groupid)) {
                    $groupmembers[$ue->userid] = array('groupid' => $ue->groupid, 'name' => $ue->groupname, 'fullname' => $ue->firstname.' '.$ue->lastname);
                }
            }
            $rs->close();

            // get list of users that need to be enrolled and their roles
            $requested_roles = array();
            $enroltimes = array();
            $sql = $this->db_get_sql($table, array($coursefield=>$course->mapping), $sqlfields);

            if ($rs = $extdb->Execute($sql)) {
                if (!$rs->EOF) {
                    if ($localuserfield === 'username') {
                        $usersearch = array('mnethostid'=>$CFG->mnet_localhost_id, 'deleted' =>0);
                    }
                    while ($fields = $rs->FetchRow()) {

                        $fields = $this->db_decode($fields);

                        $fields = array_change_key_case($fields, CASE_LOWER);
                        if (empty($fields[$userfield])) {
                            //user identification is mandatory!
                            continue;
                        }
                        $mapping = $fields[$userfield];

                        if (!isset($user_mapping[$mapping])) {
                            $usersearch[$localuserfield] = $mapping;
                            if (!$user = $DB->get_record('user', $usersearch, '*', IGNORE_MULTIPLE)) {
                                // TODO Se usuário nao existe, criar
                                $user = new stdClass();
                                $user->username = $fields[$userfield];
                                $user->email = $fields[$emailfield];
                                $user->password = hash_internal_user_password($fields[$passwordfield]);
                                $user->firstname = ($fields[$firstnamefield]);
                                $user->lastname = ($fields[$lastnamefield]);
                                $user->confirmed = 1;
                                $user->mnethostid = 1;
                                $user->timecreated = time();
                                $user->auth = 'manual';

                                if($verbose){
                                    mtrace('   Criando usuário: '.$user->username. ' '.$user->firstname. ' '.$user->lastname. ' '.$fields[$passwordfield]);
                                }
                                // Validate user data object.
                                $uservalidation = core_user::validate($user);
                                if ($uservalidation !== true) {
                                    mtrace('   &#9888; Erro ao criar usuário: '.$user->username. ' '.$user->firstname. ' '.$user->lastname. ' '.$fields[$passwordfield]);
                                    foreach ($uservalidation as $field => $message) {
                                        mtrace("      &#9888; O campo '$field' possui dados inválidos.");
                                    }
                                    die();
                                }
                                $user->id = user_create_user($user, false);
                                if($verbose){
                                    mtrace('   &#10004; Usuário criado com sucesso!');
                                }

                                //continue;
                            }
                            else{

                                if($user->username != $fields[$userfield] ||$user->email != $fields[$emailfield]|| $user->firstname != $fields[$firstnamefield]||$user->lastname != $fields[$lastnamefield] ) {
                                    $user->email = $fields[$emailfield];
                                    $user->password = hash_internal_user_password($fields[$passwordfield]);
                                    $user->firstname = ($fields[$firstnamefield]);
                                    $user->lastname = ($fields[$lastnamefield]);

                                    if ($verbose) {
                                        mtrace('   Atualizando usuário: ' . $user->username . ' ' . $user->firstname . ' ' . $user->lastname . ' ' . $fields[$passwordfield]);
                                    }

                                    // Validate user data object.
                                    $uservalidation = core_user::validate($user);
                                    if ($uservalidation !== true) {
                                        mtrace('   &#9888; Erro ao atualizar usuário: ' . $user->username . ' ' . $user->firstname . ' ' . $user->lastname . ' ' . $fields[$passwordfield]);
                                        foreach ($uservalidation as $field => $message) {
                                            mtrace("      &#9888; O campo '$field' possui dados inválidos.");
                                        }
                                        die();
                                    }
                                    
                                    user_update_user($user, false);
                                    if($verbose){
                                        mtrace('   &#10004; Usuário atualizado com sucesso!');
                                    }
                                }
                            }

                            $user_mapping[$mapping] = $user->id;
                            $userid = $user->id;
                        } else {
                            $usersearch[$localuserfield] = $mapping;
                            if ($user = $DB->get_record('user', $usersearch, '*', IGNORE_MULTIPLE)) {


                                if($user->username != $fields[$userfield] ||$user->email != $fields[$emailfield]|| $user->firstname != $fields[$firstnamefield]||$user->lastname != $fields[$lastnamefield] )
                                {
                                    mtrace($user->firstname);
                                    mtrace($fields[$firstnamefield]);

                                    $user->username = $fields[$userfield];
                                    $user->email = $fields[$emailfield];
                                    //$user->password = hash_internal_user_password($fields[$passwordfield]);
                                    $user->firstname = $fields[$firstnamefield];
                                    $user->lastname = $fields[$lastnamefield];
                                    $user->confirmed = 1;
                                    $user->mnethostid = 1;
                                    $user->timecreated = time();
                                    $user->auth = 'manual';

                                    if ($verbose) {
                                        mtrace('   Atualizando usuário: ' . $user->username . ' ' . $user->firstname . ' ' . $user->lastname);
                                    }

                                    // Validate user data object.
                                    $uservalidation = core_user::validate($user);
                                    if ($uservalidation !== true) {
                                        mtrace('   &#9888; Erro ao atualizando usuário: ' . $user->username . ' ' . $user->firstname . ' ' . $user->lastname);
                                        foreach ($uservalidation as $field => $message) {
                                            mtrace("   &#9888; O campo '$field' possui dados inválidos.");
                                        }
                                        die();
                                    }

                                    $user->id = user_update_user($user, false);
                                    if($verbose){
                                        mtrace('   &#10004; Usuário atualizado com sucesso!');
                                    }

                                }
                            }



                            $userid = $user_mapping[$mapping];
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
                        //Verificar tempo de inscrição
                        $enroltime = null;
                        if($enroltimefield) {
                            if (!empty($fields[$enroltimefield])){
                                $enroltime = $fields[$enroltimefield];
                            }
                        }


                        $requested_roles[$userid][$roleid] = $roleid;
                        if($enroltime){
                            $enroltimes[$userid] = $enroltime;
                        }

                        if( !empty($fields[$groupfield]) && !array_key_exists($userid,$extgroups)){ // Se o usuário está em um grupo no curso
                            $extgroups[$userid] = $fields[$groupfield];
                        }

                    }
                }
                $rs->Close();
            } else {
                mtrace('Erro ao se comunicar com o banco de dados de inscrição externo');
                $extdb->Close();
                return;
            }
            unset($user_mapping);

            // enrol all users and sync roles
            foreach ($requested_roles as $userid=>$userroles) {
                foreach ($userroles as $roleid) {
                    if (empty($current_roles[$userid])) {
                        $timeend = 0;
                        $timestart = 0;
                        if($enroltimes[$userid]){
                            $timestart = $now;
                            $timeend = strtotime('today', $now + $enroltimes[$userid]);
                            if($verbose){
                                mtrace('timestart: ' .$timestart);
                                mtrace('timeend: ' .$timeend);
                            }
                        }
                        $this->enrol_user($instance, $userid, $roleid, $timestart, $timeend, ENROL_USER_ACTIVE);
                        $current_roles[$userid][$roleid] = $roleid;
                        $current_status[$userid] = ENROL_USER_ACTIVE;
                        if ($verbose) {
                            mtrace("  inscrevendo: $userid ==> $course->shortname como ".$allroles[$roleid]->shortname);
                        }
                    }
                }

                // assign extra roles
                foreach ($userroles as $roleid) {
                    if (empty($current_roles[$userid][$roleid])) {
                        role_assign($roleid, $userid, $context->id, 'enrol_dbprocessoseletivo', $instance->id);
                        $current_roles[$userid][$roleid] = $roleid;
                        if ($verbose) {
                            mtrace("  atribuindo funções: $userid ==> $course->shortname como ".$allroles[$roleid]->shortname);
                        }
                    }
                }

                // unassign removed roles
                foreach($current_roles[$userid] as $cr) {
                    if (empty($userroles[$cr])) {
                        role_unassign($cr, $userid, $context->id, 'enrol_dbprocessoseletivo', $instance->id);
                        unset($current_roles[$userid][$cr]);
                        if ($verbose) {
                            mtrace("  revomendo funções: $userid ==> $course->shortname");
                        }
                    }
                }

                // reenable enrolment when previously disable enrolment refreshed
                if ($current_status[$userid] == ENROL_USER_SUSPENDED) {
                    $this->update_user_enrol($instance, $userid, ENROL_USER_ACTIVE);
                    if ($verbose) {
                        mtrace("  reativando: $userid ==> $course->shortname");
                    }
                }
            }


            $groupscreate = array();

            //Para cada grupo externo
            foreach($extgroups as $userid => $group){

                //mtrace("Grupo Externo: Usuário $userid do grupo $group");
                $grupoexistente = null;
                foreach ($grupos as $g){
                    if($g->name == $group){
                        $grupoexistente = $g;
                        break;
                    }
                }
                $groupidnumber = $course->mapping.'_'.$group;
                $idnovogrupo = null;
                $idgrupoexistente = null;

                // mtrace("Grupo existente que corresponde ao externo:");
                // var_dump($grupoexistente);
                if(empty($grupoexistente)  && !array_key_exists($groupidnumber, $groupscreate) ){ //O grupo externo ainda não existe local e ainda não foi criado
                    $novogrupo = new stdClass();
                    $novogrupo->name = $group;
                    $novogrupo->courseid = $course->id;
                    $novogrupo->idnumber = $groupidnumber;
                    $novogrupo->description = '';
                    $groupscreate[$groupidnumber] = $novogrupo ;
                    //mtrace('Criando grupo '. $idnovogrupo.' com nome '.$group);
                    $idnovogrupo = groups_create_group($novogrupo);
                    if($verbose) {
                        mtrace('   Criado grupo '. $idnovogrupo.' com nome '.$group);
                    }

                    $grupos[$idnovogrupo] = $DB->get_record('groups', array('id'=>$idnovogrupo));
                }
                else if(!empty($grupoexistente)){
                    $idgrupoexistente = $grupoexistente->id;
                }


//                mtrace('Usuário ' . $userid. ' Grupo externo ' . $group);
                if(array_key_exists( $userid, $groupmembers)){ //Se o usuário existe em algum grupo local atual
                    if($groupmembers[$userid]['name'] == $group){ //Se o grupo local é igual ao externo
                        if($verbose) {
                            mtrace('   Usuario ' . $groupmembers[$userid]['fullname'] . ' já está no grupo ' . $group);
                        }
                    }
                    else{
                        if($verbose){
                            mtrace('   Usuario ' . $groupmembers[$userid]['fullname'] . ' está no grupo ' . $groupmembers[$userid]['name'] . ', mudar para ' . $group);
                        }
                        groups_remove_member($groupmembers[$userid]['groupid'], $userid);

                        if($idgrupoexistente){
                            groups_add_member($idgrupoexistente,$userid);
                        }
                        else if($idnovogrupo) {
                            groups_add_member($idnovogrupo, $userid);
                        }
                    }
                }
                else{
                    if($verbose) {
                        mtrace('   Usuario ' . $userid . ' Não está em nenhum grupo ainda, increver no grupo ' . $group . ' do curso ' . $course->id);
                    }

                    if($idgrupoexistente){
                        groups_add_member($idgrupoexistente,$userid);
                    }
                    else if($idnovogrupo) {
                        groups_add_member($idnovogrupo, $userid);
                    }

                }

            }

            foreach($groupmembers as $groupmember => $groupinfo){ //para cada membro atual
                if(!array_key_exists($groupmember, $extgroups)){ //se nao ha grupo na tabela externa
                    if($verbose) {
                        mtrace('   remover usuário ' . $groupmember . ' do grupo ' . $groupinfo['name'] . ' do curso ' . $course->id);
                    }
                    //Excluí-lo do grupo atual
                    groups_remove_member($groupinfo['groupid'], $groupmember);
                }
            }


            // deal with enrolments removed from external table
            if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                if (!$preventfullunenrol) {
                    // unenrol

                    mtrace('   course id '. $course->id.' status');

                    foreach ($current_status as $userid=>$status) {
                        mtrace('   Course '. $course->id.' requested role ' . $userid. ':  ');
                        if (isset($requested_roles[$userid])) {
                            continue;
                        }
                        $this->unenrol_user($instance, $userid);
                        if ($verbose) {
                            mtrace("  unenrolling: $userid ==> $course->shortname");
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
                        $this->update_user_enrol($instance, $userid, ENROL_USER_SUSPENDED);
                        if ($verbose) {
                            mtrace("  suspending: $userid ==> $course->shortname");
                        }
                    }
                    if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                        role_unassign_all(array('contextid'=>$context->id, 'userid'=>$userid, 'component'=>'enrol_dbprocessoseletivo', 'itemid'=>$instance->id));
                        if ($verbose) {
                            mtrace("  unsassigning all roles: $userid ==> $course->shortname");
                        }
                    }
                }
            }
        }

        // close db connection
        $extdb->Close();

        if ($verbose) {
            mtrace('...user enrolment synchronisation finished.');
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
                    $newcategory->description = get_string('created_from_dbprocessoseletivo', 'enrol_dbprocessoseletivo');
                    $newcategory->sortorder = 999;
                    $newcategory->visible = 1;
                    $newcategory->timemodified = time();
                    $newcategory->id = $DB->insert_record('course_categories', $newcategory);

                    // update the parent category for the next course category to create
                    $parent_category_id = $newcategory->id;
                    $newcategory_context = get_context_instance(CONTEXT_COURSECAT, $newcategory->id);
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

        $extdb->setConnectionParameter('characterSet','UTF-8');

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
                mtrace('Course synchronisation skipped.');
            }
            return 0;
        }

        if ($verbose) {
            mtrace('Starting course synchronisation...');
        }

        // we may need a lot of memory here
        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);

        if (!$extdb = $this->db_init()) {
            mtrace('Erro ao se comunicar com o banco de dados de inscrição externo');
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
                    //$fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);
                    if (empty($fields[$shortname]) or empty($fields[$fullname])) {
                        if ($verbose) {
                            mtrace('  error: invalid external course record, shortname and fullname are mandatory: ' . json_encode($fields)); // hopefully every geek can read JS, right?
                        }
                        continue;
                    }
                    if ($DB->record_exists('course', array('shortname'=>$fields[$shortname]))) {
                        if ($verbose) {
                            mtrace('  error: duplicate shortname, can not create course: '.$fields[$shortname].' ['.$fields[$idnumber].']');
                        }
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
                    $course->categorytree = explode($hierachyseparator, $fields[$categoryname]);

                    $createcourses[] = $course;
                }
            }
            $rs->Close();
        } else {
            mtrace('Error reading data from the external course table');
            $extdb->Close();
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
            $templatecoursecoordcurso = $this->get_config('templatecoursecoordcurso'); // Template de curso de coordenação
            $courseconfig = get_config('moodlecourse');

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


            // ---------------
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


                // -------------------
                //DECIDIR TEMPLATE A SER USADO
                $templateusado = $template;
                $templateusadoid = $templatecourse_id;
                // ------------------

                $newcourse = clone($templateusado);
                $newcourse->fullname  = $fields->fullname;
                $newcourse->shortname = $fields->shortname;
                $newcourse->idnumber  = $fields->idnumber;
                $newcourse->category  = $fields->category ? $fields->category : $defaultcategory;
                $newcourse->categorytree = $fields->categorytree;
                $newcourse->modalidade = $fields->modalidade;
                $newcourse->tipocurso = $fields->tipocurso;
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

                // Ello Oliveira [06/02/2018]: Define o número de seções baseado em se um curso é da modalidade EaD, se for, importa usando o template EaD
                if($newcourse->modalidade == 'D'|| (strpos($newcourse->categorytree[1], 'EAD - PRESENCIAL') !== false || $newcourse->tipocurso==2 || $newcourse->tipocurso == 3)){
                    $newcourse->numsections = 11;
                }


                $c = static::criar_curso($newcourse);
                if ($verbose) {
                    mtrace('template usado: '.$templateusadoid);
                    mtrace("  creating course: $c->id, $c->fullname, $c->shortname, $c->idnumber, $c->category".' Modalidade ' . $newcourse->modalidade.' Tipo '.$newcourse->tipocurso);
                }


                static::importar_curso($templateusadoid, $c->id, 0);
            }

            unset($createcourses);
            unset($template);
        }

        // close db connection
        $extdb->Close();

        if ($verbose) {
            mtrace('...course synchronisation finished.');
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
        $data->timecreated  = !empty($data->timecreated) ? $data->timecreated : time();
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

        //>> Ello Oliveira 28/11/2017
        //Buscar as categogias de notas do curso template
        $gradecategories = $importfrom->gradecategories = $DB->get_records('grade_categories', array('courseid' => $params['importfrom'], 'depth' => 2), 'id ASC');

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
//        $section = $DB->get_record("course_sections", array("course" => $importto->id, "section" => "0"));
//        $section->name = $importto->shortname;
//        $DB->update_record("course_sections", $section);


        //>>Ello Oliveira 28/11/2017
        // Criar as categorias de notas
        foreach ($gradecategories as $gradecat){
            // Create new grading category in this course.
            $new = new grade_category(array('courseid' => $importto->id, 'fullname'=>$gradecat->fullname), false);
            $sucesso = $new->insert();
            }

        if (empty($CFG->keeptempdirectoriesonbackup)) {
            fulldelete($backupbasepath);
        }

        return null;
    }

    /**
     *******************************************************
     */
}
