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
 * DBExtended enrolment plugin settings and presets.
 *
 * @package    enrol
 * @subpackage dbextended
 * @copyright  2012 Luis Alcantara, based on code /enrol/database from Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_dbextended_settings', '', get_string('pluginname_desc', 'enrol_dbextended')));

    $settings->add(new admin_setting_heading('enrol_dbextended_exdbheader', get_string('settingsheaderdb', 'enrol_dbextended'), ''));

    $options = array('', "access","ado_access", "ado", "ado_mssql", "borland_ibase", "csv", "db2", "fbsql", "firebird", "ibase", "informix72", "informix", "mssql", "mssql_n", "mssqlnative", "mysql", "mysqli", "mysqlt", "oci805", "oci8", "oci8po", "odbc", "odbc_mssql", "odbc_oracle", "oracle", "postgres64", "postgres7", "postgres", "proxy", "sqlanywhere", "sybase", "vfp");
    $options = array_combine($options, $options);
    $settings->add(new admin_setting_configselect('enrol_dbextended/dbtype', get_string('dbtype', 'enrol_dbextended'), get_string('dbtype_desc', 'enrol_dbextended'), '', $options));

    $settings->add(new admin_setting_configtext('enrol_dbextended/dbhost', get_string('dbhost', 'enrol_dbextended'), get_string('dbhost_desc', 'enrol_dbextended'), 'localhost'));

    $settings->add(new admin_setting_configtext('enrol_dbextended/dbuser', get_string('dbuser', 'enrol_dbextended'), '', ''));

    $settings->add(new admin_setting_configpasswordunmask('enrol_dbextended/dbpass', get_string('dbpass', 'enrol_dbextended'), '', ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/dbname', get_string('dbname', 'enrol_dbextended'), '', ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/dbencoding', get_string('dbencoding', 'enrol_dbextended'), '', 'utf-8'));

    $settings->add(new admin_setting_configtext('enrol_dbextended/dbsetupsql', get_string('dbsetupsql', 'enrol_dbextended'), get_string('dbsetupsql_desc', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_configcheckbox('enrol_dbextended/dbsybasequoting', get_string('dbsybasequoting', 'enrol_dbextended'), get_string('dbsybasequoting_desc', 'enrol_dbextended'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_dbextended/debugdb', get_string('debugdb', 'enrol_dbextended'), get_string('debugdb_desc', 'enrol_dbextended'), 0));



    $settings->add(new admin_setting_heading('enrol_dbextended_localheader', get_string('settingsheaderlocal', 'enrol_dbextended'), ''));

    $options = array('idnumber'=>'idnumber');
    $settings->add(new admin_setting_configselect('enrol_dbextended/localcoursecategoryfield', get_string('localcoursecategoryfield', 'enrol_dbextended'), '', 'idnumber', $options));

    $options = array('id'=>'id', 'idnumber'=>'idnumber', 'shortname'=>'shortname');
    $settings->add(new admin_setting_configselect('enrol_dbextended/localcoursefield', get_string('localcoursefield', 'enrol_dbextended'), '', 'idnumber', $options));

    $options = array('id'=>'id', 'idnumber'=>'idnumber', 'email'=>'email', 'username'=>'username'); // only local users if username selected, no mnet users!
    $settings->add(new admin_setting_configselect('enrol_dbextended/localuserfield', get_string('localuserfield', 'enrol_dbextended'), '', 'idnumber', $options));

    $options = array('id'=>'id', 'shortname'=>'shortname', 'fullname'=>'fullname');
    $settings->add(new admin_setting_configselect('enrol_dbextended/localrolefield', get_string('localrolefield', 'enrol_dbextended'), '', 'shortname', $options));



    $settings->add(new admin_setting_heading('enrol_dbextended_remoteheader', get_string('settingsheaderremote', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/remoteenroltable', get_string('remoteenroltable', 'enrol_dbextended'), get_string('remoteenroltable_desc', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/remotecoursefield', get_string('remotecoursefield', 'enrol_dbextended'), get_string('remotecoursefield_desc', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/remoteuserfield', get_string('remoteuserfield', 'enrol_dbextended'), get_string('remoteuserfield_desc', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/remoterolefield', get_string('remoterolefield', 'enrol_dbextended'), get_string('remoterolefield_desc', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/remoterafield', get_string('remoterafield', 'enrol_dbextended'), get_string('remoterafield_desc', 'enrol_dbextended'), ''));
    $settings->add(new admin_setting_configtext('enrol_dbextended/remoteturmadiscfield', get_string('remoteturmadiscfield', 'enrol_dbextended'), get_string('remoteturmadiscfield_desc', 'enrol_dbextended'), 'turma_disc_original'));
    $settings->add(new admin_setting_configtext('enrol_dbextended/remotegroupfield', get_string('remotegroupfield', 'enrol_dbextended'), get_string('remotegroupfield_desc', 'enrol_dbextended'), 'grupo1'));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_dbextended/defaultrole', get_string('defaultrole', 'enrol_dbextended'), get_string('defaultrole_desc', 'enrol_dbextended'), $student->id, $options));
    }

    $settings->add(new admin_setting_configcheckbox('enrol_dbextended/ignorehiddencourses', get_string('ignorehiddencourses', 'enrol_dbextended'), get_string('ignorehiddencourses_desc', 'enrol_dbextended'), 0));

    $options = array(ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
                     ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
                     ENROL_EXT_REMOVED_SUSPEND        => get_string('extremovedsuspend', 'enrol'),
                     ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'));
    $settings->add(new admin_setting_configselect('enrol_dbextended/unenrolaction', get_string('extremovedaction', 'enrol_dbextended'), get_string('extremovedaction_help', 'enrol_dbextended'), ENROL_EXT_REMOVED_UNENROL, $options));



    $settings->add(new admin_setting_heading('enrol_dbextended_newcoursesheader', get_string('settingsheadernewcourses', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcoursetable', get_string('newcoursetable', 'enrol_dbextended'), get_string('newcoursetable_desc', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcoursefullname', get_string('newcoursefullname', 'enrol_dbextended'), '', 'fullname'));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcourseshortname', get_string('newcourseshortname', 'enrol_dbextended'), '', 'shortname'));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcourseidnumber', get_string('newcourseidnumber', 'enrol_dbextended'), '', 'idnumber'));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcoursecategory', get_string('newcoursecategory', 'enrol_dbextended'), get_string('newcoursecategory_desc', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcoursemodality', get_string('newcoursemodality', 'enrol_dbextended'), get_string('newcoursemodality_desc', 'enrol_dbextended'), 'modalidade'));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcoursetype', get_string('newcoursetype', 'enrol_dbextended'), get_string('newcoursetype_desc', 'enrol_dbextended'), 'tipocurso'));
    
    $settings->add(new admin_setting_configtext('enrol_dbextended/newcoursestartdate', get_string('newcoursestartdate', 'enrol_dbextended'), get_string('newcoursestartdate_desc', 'enrol_dbextended'), 'data_inicial'));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcourseenddate', get_string('newcourseenddate', 'enrol_dbextended'), get_string('newcourseenddate_desc', 'enrol_dbextended'), 'data_final'));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcourselimitdaten1', get_string('newcourselimitdaten1', 'enrol_dbextended'), get_string('newcourselimitdaten1_desc', 'enrol_dbextended'), 'data_limite_n1'));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcourselimitdaten2', get_string('newcourselimitdaten2', 'enrol_dbextended'), get_string('newcourselimitdaten2_desc', 'enrol_dbextended'), 'data_limite_n2'));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcourselimitdaten3', get_string('newcourselimitdaten3', 'enrol_dbextended'), get_string('newcourselimitdaten3_desc', 'enrol_dbextended'), 'data_limite_n3'));

    $settings->add(new admin_setting_configcheckbox('enrol_dbextended/createdcourseforcehidden', get_string('createdcourseforcehidden', 'enrol_dbextended'), get_string('createdcourseforcehidden_desc', 'enrol_dbextended'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_dbextended/createcourseonloginuserenrolment', get_string('createcourseonloginuserenrolment', 'enrol_dbextended'), get_string('createcourseonloginuserenrolment_desc', 'enrol_dbextended'), 0));

    $options = array('10'=>'10', '20'=>'20', '30'=>'30', '40'=>'40', '50'=>'50');
    $settings->add(new admin_setting_configselect('enrol_dbextended/maxcreatecourseonlogin', get_string('maxcreatecourseonlogin', 'enrol_dbextended'), get_string('maxcreatecourseonlogin_desc', 'enrol_dbextended'), '10', $options));

    if (!during_initial_install()) {
        require_once($CFG->dirroot.'/course/lib.php');
        $options = array();
        $parentlist = array();
        $options = core_course_category::make_categories_list($options, $parentlist);
        $settings->add(new admin_setting_configselect('enrol_dbextended/defaultcategory', get_string('defaultcategory', 'enrol_dbextended'), get_string('defaultcategory_desc', 'enrol_dbextended'), 1, $options));
        unset($parentlist);
    }

    $settings->add(new admin_setting_configtext('enrol_dbextended/templatecourse', get_string('templatecourse', 'enrol_dbextended'), get_string('templatecourse_desc', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/templatecourseead', get_string('templatecourseead', 'enrol_dbextended'), get_string('templatecourse_desc', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbextended/templatecoursepos', get_string('templatecoursepos', 'enrol_dbextended'), get_string('templatecourse_desc', 'enrol_dbextended'), ''));

    $settings->add(new admin_setting_heading('enrol_dbextended_newcoursecategoriesheader', get_string('settingsheadernewcoursecategories', 'enrol_dbextended'), get_string('newcoursecategories_desc', 'enrol_dbextended')));

    $settings->add(new admin_setting_configtext('enrol_dbextended/newcoursecategoryname', get_string('newcoursecategoryname', 'enrol_dbextended'), get_string('newcoursecategoryname_desc', 'enrol_dbextended'), ''));

    $options = array(''=>'', '|'=>'|', '#'=>'#', '$'=>'$', '_'=>'_', '-'=>'-', '+'=>'+');
    $settings->add(new admin_setting_configselect('enrol_dbextended/newcoursecategoryhierachyseparator', get_string('newcoursecategoryhierachyseparator', 'enrol_dbextended'), get_string('newcoursecategoryhierachyseparator_desc', 'enrol_dbextended'), '', $options));
    $settings->add(new admin_setting_configcheckbox('enrol_dbextended/enablewelcomeemail', get_string('enablewelcomeemail', 'enrol_dbextended'), get_string('enablewelcomeemail', 'enrol_dbextended'), 0));
    $settings->add(new admin_setting_confightmleditor('enrol_dbextended/welcometocoursehtml', get_string('welcometocoursehtmltext', 'enrol_dbextended'), get_string('welcometocoursehtmldesc', 'enrol_dbextended'), get_string('welcometocoursehtml', 'enrol_dbextended')));
}
