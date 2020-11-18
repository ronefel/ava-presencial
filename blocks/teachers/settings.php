<?php
/**
 * Criado com PhpStorm.
 * Autor: Ello Oliveira
 * Data: 06/11/2017
 * Hora: 09:09
 */
$settings->add(new admin_setting_heading(
    'headerconfig',
    get_string('headerconfig', 'block_teachers'),
    get_string('descconfig', 'block_teachers')
));

$settings->add(new admin_setting_configcheckbox(
    'teachers/Allow_HTML',
    get_string('labelallowhtml', 'block_teachers'),
    get_string('descallowhtml', 'block_teachers'),
    '0'
));

$settings->add(new admin_setting_configcheckbox(
    'teachers/show_teacherslabel',
    get_string('labelshowteacherslabel', 'block_teachers'),
    get_string('desclabelshowteacherslabel', 'block_teachers'),
    '0'
));

$settings->add(new admin_setting_configtext(
    'teachers/teacherlabel',
    get_string('labelteacher', 'block_teachers'),
    get_string('desclabelteacher', 'block_teachers'),
    get_string('defaultlabelteacher', 'block_teachers')
));

$settings->add(new admin_setting_configtext(
    'teachers/teacherlabelplural',
    get_string('labelteacherplural', 'block_teachers'),
    '',
    get_string('defaultlabelteacherplural', 'block_teachers')
));


$settings->add(new admin_setting_configtext_with_maxlength(
    'teachers/teacherimagesize',
    get_string('labelteacherimagesize', 'block_teachers'),
    get_string('descteacherimagesize', 'block_teachers'),
    '100', PARAM_INT, null, 4
));

$settings->add(new admin_setting_configtext_with_maxlength(
    'teachers/teacherinfoheight',
    get_string('labelteacherinfoheight', 'block_teachers'),
    get_string('descteacherinfoheight', 'block_teachers'),
    '150', PARAM_INT, null, 4
));

$settings->add(new admin_setting_configtext_with_maxlength(
    'teachers/teacherimagesizeside',
    get_string('labelteacherimagesizeside', 'block_teachers'),
    get_string('descteacherimagesizeside', 'block_teachers'),
    '90', PARAM_INT, null, 4
));


$settings->add(new admin_setting_configcheckbox(
    'teachers/teachershowcoursetitle',
    get_string('labelshowcoursetitle', 'block_teachers'),
    get_string('descshowcoursetitle', 'block_teachers'), false
));