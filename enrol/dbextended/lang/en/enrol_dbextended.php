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
 * Strings for component 'enrol_dbextended', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   enrol_dbextended
 * @copyright  2012 Luis Alcantara, based on code from Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['createcourseonloginuserenrolment'] = 'Create user\'s enrolments\' courses on user login';
$string['createcourseonloginuserenrolment_desc'] = 'Enables the local course creation for each course loaded from the external database, which the authenticating user is enrolled to, and the course has all required data for creation under Moodle. <br />Use ONLY under systems with little course enrolments number per user.';
$string['createdcourseforcehidden'] = 'Force new courses created unvisible to students';
$string['createdcourseforcehidden_desc'] = 'Check this if new courses created from external resource must be created hidden to students independently of the template course settings or default configurations.';
$string['created_from_dbextended'] = 'Created from enrol_dbextended';
$string['dbencoding'] = 'Database encoding';
$string['dbhost'] = 'Database host';
$string['dbhost_desc'] = 'Type database server IP address or host name';
$string['dbname'] = 'Database name';
$string['dbpass'] = 'Database password';
$string['dbsetupsql'] = 'Database setup command';
$string['dbsetupsql_desc'] = 'SQL command for special database setup, often used to setup communication encoding - example for MySQL and PostgreSQL: <em>SET NAMES \'utf8\'</em>';
$string['dbsybasequoting'] = 'Use sybase quotes';
$string['dbsybasequoting_desc'] = 'Sybase style single quote escaping - needed for Oracle, MS SQL and some other databases. Do not use for MySQL!';
$string['dbtype'] = 'Database driver';
$string['dbtype_desc'] = 'ADOdb database driver name, type of the external database engine.';
$string['dbuser'] = 'Database user';
$string['debugdb'] = 'Debug ADOdb';
$string['debugdb_desc'] = 'Debug ADOdb connection to external database - use when getting empty page during login. Not suitable for production sites!';
$string['defaultcategory'] = 'Default new course category';
$string['defaultcategory_desc'] = 'The default category for auto-created courses. Used when no new category id specified or not found.';
$string['defaultrole'] = 'Default role';
$string['defaultrole_desc'] = 'The role that will be assigned by default if no other role is specified in external table.';
$string['extremovedaction'] = 'External unenrol action';
$string['extremovedaction_help'] = 'Select action to carry out when user enrolment disappears from external enrolment source. Please note that some user data and settings are purged from course during course unenrolment.';
$string['ignorehiddencourses'] = 'Ignore hidden courses';
$string['ignorehiddencourses_desc'] = 'If enabled users will not be enrolled on courses that are set to be unavailable to students.';
$string['localcoursecategoryfield'] = 'Local course category field';
$string['localcoursefield'] = 'Local course field';
$string['localrolefield'] = 'Local role field';
$string['localuserfield'] = 'Local user field';
$string['maxcreatecourseonlogin'] = 'Max. number of courses to be created on every user login';
$string['maxcreatecourseonlogin_desc'] = 'Sets the maximum number of courses to be created on every user login. If there are more new course enrolments assigned to this user on the external system then the value set here, they will be created on the next user login. This will prevent long time login and potential login crash.';
// the next two came out of order due to cyclic referencing
$string['newcoursecategory'] = 'New course\'s course category identifier field';
$string['newcoursecategoryname'] = 'New course category name field or list';
$string['newcoursecategories_desc'] = 'Enables external course categories to be reproduced under Moodle, if all settings above are correctly set up.<br />
    The \''.$string['newcoursecategory'].'\' will be used as the external course category identifier list, and all returned course categories will be created if unavalible locally.<br />
    The number of split text on the \''.$string['newcoursecategory'].'\' and the \''.$string['newcoursecategoryname'].'\' fields must mach.<br />
    Each category identifier in the hierarchy MUST BE UNIQUE, otherwise it will be found and used as the course category destination.';
$string['newcoursecategory_desc'] = 'The new course category identifier field enables the identification of the target course category destination for the creating courses.<br />
    If the course category is not found, the new course will be created under the \''.$string['defaultcategory'].'\'.<br />
    Lastly, if the course categories creation is enabled, all missing course categories will be automatically created.';
$string['newcoursecategoryhierachyseparator'] = 'A special character to split course categories names';
$string['newcoursecategoryhierachyseparator_desc'] = 'A special character that could be used under course categories hierarchy reproduction, applied on name splitting if more than one category level retrieved.';
$string['newcoursecategoryhierachyseparator_notapplied'] = 'not applied';
$string['newcoursecategoryidnumber'] = 'New course category id field or list';
$string['newcoursecategoryidnumber_desc'] = 'The external Course Category identifier or identifier list. If using list, the field '.$string['newcoursecategoryhierachyseparator'].' must be set.';
$string['newcoursecategoryname_desc'] = 'The external Course Category name or name list. If using list, the field '.$string['newcoursecategoryhierachyseparator'].' must be set.';
$string['newcoursefullname'] = 'New course full name field';
$string['newcourseidnumber'] = 'New course ID number field';
$string['newcourseshortname'] = 'New course short name field';
$string['newcoursetable'] = 'Remote new courses table';
$string['newcoursetable_desc'] = 'Specify of the name of the table that contains list of courses that should be created automatically. Empty means no courses are created.';
$string['newcoursemodality'] = 'Remote course modality table';
$string['newcoursemodality_desc'] = '';
$string['newcoursetype'] = 'Remote course type table';
$string['newcoursetype_desc'] = '';
$string['newcoursestartdate'] = 'Remote course start date table';
$string['newcoursestartdate_desc'] = '';
$string['newcourseenddate'] = 'Remote course end date table';
$string['newcourseenddate_desc'] = '';

$string['newcourselimitdaten1'] = 'Lock date field N1';
$string['newcourselimitdaten1_desc'] = 'Specify of the name of the field that contains lock date for grade N1';
$string['newcourselimitdaten2'] = 'Lock date field N2';
$string['newcourselimitdaten2_desc'] = 'Specify of the name of the field that contains lock date for grade N2';
$string['newcourselimitdaten3'] = 'Lock date field N3';
$string['newcourselimitdaten3_desc'] = 'Specify of the name of the field that contains lock date for grade N3';

$string['pluginname'] = 'External database extended';
$string['pluginname_desc'] = 'You can use an external database (of nearly any kind) to control your enrolments. It is assumed your external database contains at least a field containing a course ID, and a field containing a user ID. These are compared against fields that you choose in the local course and user tables.';
$string['remotecoursefield'] = 'Remote course field';
$string['remotecoursefield_desc'] = 'The name of the field in the remote table that we are using to match entries in the course table.';
$string['remoteenroltable'] = 'Remote user enrolment table';
$string['remoteenroltable_desc'] = 'Specify the name of the table that contains list of user enrolments. Empty means no user enrolment sync.';
$string['remoterolefield'] = 'Remote role field';
$string['remoterolefield_desc'] = 'The name of the field in the remote table that we are using to match entries in the roles table.';
$string['remoteuserfield'] = 'Remote user field';
$string['remoterafield'] = 'Campo de RA';
$string['remoterafield_desc'] = 'Campo usado para armazenar o Registro Acadêmico que será vinculado à matrícula';
$string['remotegroupfield_desc'] = 'Campo da tabela remota que é usado para colocar um usuário num grupo';
$string['remotegroupfield'] = 'Campo "Grupo" remoto';
$string['settingsheaderdb'] = 'External database connection';
$string['settingsheaderlocal'] = 'Local field mapping';
$string['settingsheaderremote'] = 'Remote enrolmtemplatecourseposent sync';
$string['settingsheadernewcourses'] = 'Creation of new courses';
$string['settingsheadernewcoursecategories'] = 'Creation of new course categories';
$string['remoteuserfield_desc'] = 'The name of the field in the remote table that we are using to match entries in the user table.';
$string['templatecourse'] = 'New course template';
$string['templatecourseead'] = 'Novo modelo de curso - EaD';
$string['templatecoursepos'] = 'Novo modelo de curso - Pós';
$string['templatecourse_desc'] = 'Optional: auto-created courses can copy their settings from a template course. Type here the shortname of the template course.';
$string['welcometocoursetext'] = 'Hello, {$a->shortname}! Welcome to {$a->coursename}! If you have not done so already, you should edit your profile page so that we can learn more about you: {$a->profileurl}';
$string['welcometocoursehtml'] = '<p>Hello, {$a->shortname}</p><p>Welcome to <strong>{$a->coursename}!</strong><p/><p> If you have not done so already, you should edit your profile page so that we can learn more about you: {$a->profileurl}</p>';
$string['welcometocoursehtmltext'] = 'HTML da mensagem de boas-vindas do curso';
$string['welcometocoursehtmldesc'] = 'Mensagem que será mostrada ao estudante quando o usuário é inscrito em um curso';
$string['enablewelcomeemail'] = 'Enable welcome email';
$string['remoteturmadiscfield'] = 'Campo de Turma/Disciplina';
$string['remoteturmadiscfield_desc'] = 'Campo usado para armazenar a turma/disciplina nas matrículas importadas.';