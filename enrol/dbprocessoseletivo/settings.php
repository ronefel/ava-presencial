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
 * @subpackage dbprocessoseletivo
 * @copyright  2012 Luis Alcantara, based on code /enrol/database from Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_dbprocessoseletivo_settings', '', get_string('pluginname_desc', 'enrol_dbprocessoseletivo')));

    $settings->add(new admin_setting_heading('enrol_dbprocessoseletivo_exdbheader', get_string('settingsheaderdb', 'enrol_dbprocessoseletivo'), ''));

    $options = array('', "access","ado_access", "ado", "ado_mssql", "borland_ibase", "csv", "db2", "fbsql", "firebird", "ibase", "informix72", "informix", "mssql", "mssql_n", "mssqlnative", "mysql", "mysqli", "mysqlt", "oci805", "oci8", "oci8po", "odbc", "odbc_mssql", "odbc_oracle", "oracle", "postgres64", "postgres7", "postgres", "proxy", "sqlanywhere", "sybase", "vfp");
    $options = array_combine($options, $options);
    $settings->add(new admin_setting_configselect('enrol_dbprocessoseletivo/dbtype', get_string('dbtype', 'enrol_dbprocessoseletivo'), get_string('dbtype_desc', 'enrol_dbprocessoseletivo'), '', $options));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/dbhost', get_string('dbhost', 'enrol_dbprocessoseletivo'), get_string('dbhost_desc', 'enrol_dbprocessoseletivo'), 'localhost'));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/dbuser', get_string('dbuser', 'enrol_dbprocessoseletivo'), '', ''));

    $settings->add(new admin_setting_configpasswordunmask('enrol_dbprocessoseletivo/dbpass', get_string('dbpass', 'enrol_dbprocessoseletivo'), '', ''));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/dbname', get_string('dbname', 'enrol_dbprocessoseletivo'), '', ''));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/dbencoding', get_string('dbencoding', 'enrol_dbprocessoseletivo'), '', 'utf-8'));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/dbsetupsql', get_string('dbsetupsql', 'enrol_dbprocessoseletivo'), get_string('dbsetupsql_desc', 'enrol_dbprocessoseletivo'), ''));

    $settings->add(new admin_setting_configcheckbox('enrol_dbprocessoseletivo/dbsybasequoting', get_string('dbsybasequoting', 'enrol_dbprocessoseletivo'), get_string('dbsybasequoting_desc', 'enrol_dbprocessoseletivo'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_dbprocessoseletivo/debugdb', get_string('debugdb', 'enrol_dbprocessoseletivo'), get_string('debugdb_desc', 'enrol_dbprocessoseletivo'), 0));



    $settings->add(new admin_setting_heading('enrol_dbprocessoseletivo_localheader', get_string('settingsheaderlocal', 'enrol_dbprocessoseletivo'), ''));

    $options = array('idnumber'=>'idnumber');
    $settings->add(new admin_setting_configselect('enrol_dbprocessoseletivo/localcoursecategoryfield', get_string('localcoursecategoryfield', 'enrol_dbprocessoseletivo'), '', 'idnumber', $options));

    $options = array('id'=>'id', 'idnumber'=>'idnumber', 'shortname'=>'shortname');
    $settings->add(new admin_setting_configselect('enrol_dbprocessoseletivo/localcoursefield', get_string('localcoursefield', 'enrol_dbprocessoseletivo'), '', 'idnumber', $options));

    $options = array('id'=>'id', 'idnumber'=>'idnumber', 'email'=>'email', 'username'=>'username'); // only local users if username selected, no mnet users!
    $settings->add(new admin_setting_configselect('enrol_dbprocessoseletivo/localuserfield', get_string('localuserfield', 'enrol_dbprocessoseletivo'), '', 'idnumber', $options));

    $options = array('id'=>'id', 'shortname'=>'shortname', 'fullname'=>'fullname');
    $settings->add(new admin_setting_configselect('enrol_dbprocessoseletivo/localrolefield', get_string('localrolefield', 'enrol_dbprocessoseletivo'), '', 'shortname', $options));



    $settings->add(new admin_setting_heading('enrol_dbprocessoseletivo_remoteheader', get_string('settingsheaderremote', 'enrol_dbprocessoseletivo'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/remoteenroltable', get_string('remoteenroltable', 'enrol_dbprocessoseletivo'), get_string('remoteenroltable_desc', 'enrol_dbprocessoseletivo'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/remotecoursefield', get_string('remotecoursefield', 'enrol_dbprocessoseletivo'), get_string('remotecoursefield_desc', 'enrol_dbprocessoseletivo'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/remoteuserfield', get_string('remoteuserfield', 'enrol_dbprocessoseletivo'), get_string('remoteuserfield_desc', 'enrol_dbprocessoseletivo'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/remoterolefield', get_string('remoterolefield', 'enrol_dbprocessoseletivo'), get_string('remoterolefield_desc', 'enrol_dbprocessoseletivo'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/remotegroupfield', get_string('remotegroupfield', 'enrol_dbprocessoseletivo'), get_string('remotegroupfield_desc', 'enrol_dbprocessoseletivo'), 'grupo'));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/remotefirstnamefield', 'Campo para nome', 'Campo para primeiro nome', 'nome'));
    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/remotelastnamefield', 'Campo para sobrenome', 'Campo para sobrenome', 'sobrenome'));
    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/remotepasswordfield', 'Campo para senha', 'Campo para senha', 'senha'));
    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/remoteemailfield', 'Campo para e-mail', 'Campo para e-mail', 'email'));

    //$settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/remoteenroltimefield', get_string('remoteenroltimefield', 'enrol_dbprocessoseletivo'), get_string('remoteenroltimefield_desc', 'enrol_dbprocessoseletivo'), ''));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_dbprocessoseletivo/defaultrole', get_string('defaultrole', 'enrol_dbprocessoseletivo'), get_string('defaultrole_desc', 'enrol_dbprocessoseletivo'), $student->id, $options));
    }

    //$settings->add(new admin_setting_configcheckbox('enrol_dbprocessoseletivo/ignorehiddencourses', get_string('ignorehiddencourses', 'enrol_dbprocessoseletivo'), get_string('ignorehiddencourses_desc', 'enrol_dbprocessoseletivo'), 0));

    $options = array(ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
                     ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
                     ENROL_EXT_REMOVED_SUSPEND        => get_string('extremovedsuspend', 'enrol'),
                     ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'));
    $settings->add(new admin_setting_configselect('enrol_dbprocessoseletivo/unenrolaction', get_string('extremovedaction', 'enrol_dbprocessoseletivo'), get_string('extremovedaction_help', 'enrol_dbprocessoseletivo'), ENROL_EXT_REMOVED_UNENROL, $options));



    $settings->add(new admin_setting_heading('enrol_dbprocessoseletivo_newcoursesheader', get_string('settingsheadernewcourses', 'enrol_dbprocessoseletivo'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/newcoursetable', get_string('newcoursetable', 'enrol_dbprocessoseletivo'), get_string('newcoursetable_desc', 'enrol_dbprocessoseletivo'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/newcoursefullname', get_string('newcoursefullname', 'enrol_dbprocessoseletivo'), '', 'fullname'));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/newcourseshortname', get_string('newcourseshortname', 'enrol_dbprocessoseletivo'), '', 'shortname'));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/newcourseidnumber', get_string('newcourseidnumber', 'enrol_dbprocessoseletivo'), '', 'idnumber'));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/newcoursecategory', get_string('newcoursecategory', 'enrol_dbprocessoseletivo'), get_string('newcoursecategory_desc', 'enrol_dbprocessoseletivo'), ''));

    $settings->add(new admin_setting_configcheckbox('enrol_dbprocessoseletivo/createdcourseforcehidden', get_string('createdcourseforcehidden', 'enrol_dbprocessoseletivo'), get_string('createdcourseforcehidden_desc', 'enrol_dbprocessoseletivo'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_dbprocessoseletivo/createcourseonloginuserenrolment', get_string('createcourseonloginuserenrolment', 'enrol_dbprocessoseletivo'), get_string('createcourseonloginuserenrolment_desc', 'enrol_dbprocessoseletivo'), 0));

    $options = array('10'=>'10', '20'=>'20', '30'=>'30', '40'=>'40', '50'=>'50');
    $settings->add(new admin_setting_configselect('enrol_dbprocessoseletivo/maxcreatecourseonlogin', get_string('maxcreatecourseonlogin', 'enrol_dbprocessoseletivo'), get_string('maxcreatecourseonlogin_desc', 'enrol_dbprocessoseletivo'), '10', $options));

    if (!during_initial_install()) {
        require_once($CFG->dirroot.'/course/lib.php');
        $options = array();
        $parentlist = array();
        $options = core_course_category::make_categories_list($options, $parentlist);
        $settings->add(new admin_setting_configselect('enrol_dbprocessoseletivo/defaultcategory', get_string('defaultcategory', 'enrol_dbprocessoseletivo'), get_string('defaultcategory_desc', 'enrol_dbprocessoseletivo'), 1, $options));
        unset($parentlist);
    }

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/templatecourse', get_string('templatecourse', 'enrol_dbprocessoseletivo'), get_string('templatecourse_desc', 'enrol_dbprocessoseletivo'), ''));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/templatecourseextensao', get_string('templatecourseextensao', 'enrol_dbprocessoseletivo'), get_string('templatecourseextensao_desc', 'enrol_dbprocessoseletivo'), ''));

    $settings->add(new admin_setting_heading('enrol_dbprocessoseletivo_newcoursecategoriesheader', get_string('settingsheadernewcoursecategories', 'enrol_dbprocessoseletivo'), get_string('newcoursecategories_desc', 'enrol_dbprocessoseletivo')));

    $settings->add(new admin_setting_configtext('enrol_dbprocessoseletivo/newcoursecategoryname', get_string('newcoursecategoryname', 'enrol_dbprocessoseletivo'), get_string('newcoursecategoryname_desc', 'enrol_dbprocessoseletivo'), ''));

    $options = array(''=>'', '|'=>'|', '#'=>'#', '$'=>'$', '_'=>'_', '-'=>'-', '+'=>'+');
    $settings->add(new admin_setting_configselect('enrol_dbprocessoseletivo/newcoursecategoryhierachyseparator', get_string('newcoursecategoryhierachyseparator', 'enrol_dbprocessoseletivo'), get_string('newcoursecategoryhierachyseparator_desc', 'enrol_dbprocessoseletivo'), '', $options));
    //$settings->add(new admin_setting_configcheckbox('enrol_dbprocessoseletivo/enablewelcomeemail', get_string('enablewelcomeemail', 'enrol_dbprocessoseletivo'), get_string('enablewelcomeemail', 'enrol_dbprocessoseletivo'), 0));
    //$settings->add(new admin_setting_confightmleditor('enrol_dbprocessoseletivo/welcometocoursehtml', get_string('welcometocoursehtmltext', 'enrol_dbxtended'), get_string('welcometocoursehtmldesc', 'enrol_dbxtended'), get_string('welcometocoursehtml', 'enrol_dbxtended')));
}
