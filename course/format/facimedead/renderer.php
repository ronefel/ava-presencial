<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Renderer for outputting the weeks course format.
 *
 * @package format_facimedead
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');
require_once($CFG->dirroot.'/course/format/facimedead/lib.php');


/**
 * Basic renderer for weeks format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_facimedead_renderer extends format_section_renderer_base {
    public $mustache;
    /**
     * Generate the starting container html for a list of sections
    * @return string HTML to output.
    */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'facimedead'));
    }

    /**
     * Generate the closing container html for a list of sections
    * @return string HTML to output.
    */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
    * @return string the page title
    */
    protected function page_title() {
        return get_string('weeklyoutline');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
    *
    * @param stdClass $section The course_section entry from DB
    * @param stdClass $course The course entry from DB
    * @return string HTML to output.
    */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
    *
    * @param stdClass $section The course_section entry from DB
    * @param stdClass $course The course entry from DB
    * @return string HTML to output.
    */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    protected function first_section_pre_content($section, $thissection, $course){
        global $DB, $OUTPUT, $PAGE, $CFG;

        require_once(__DIR__.'/../../../theme/lambda/Mobile_Detect.php');
        $isSessionMobile = (new Mobile_Detect)->isMobile();

        $labels = array();
        $teacherList = array();
        if($section == 0) {
            $sql_profs = "SELECT DISTINCT
            U.id,
            U.firstname,
            U.lastname,
            U.picture,
            U.firstnamephonetic,
            U.lastnamephonetic,
            U.middlename,
            U.alternatename,
            U.imagealt,
            U.email,
            R.shortname AS role
       FROM {course} C
       JOIN {context} CX
         ON CX.instanceid = C.id
        AND CX.contextlevel = '50'
       JOIN {enrol} E
         ON E.courseid = C.id
       JOIN {user_enrolments} UE
         ON UE.enrolid = E.id
       JOIN {user} U
         ON U.id = UE.userid
       JOIN {role_assignments} AS RA
         ON RA.contextid = CX.id
        AND RA.userid = U.id
       JOIN {role} AS R
         ON R.id = RA.roleid
      WHERE C.id = ?
        AND R.shortname IN ('editingteacher', 'tutor')" ;

            $profs = $DB->get_records_sql($sql_profs, array($course->id));
            $modinfo = get_fast_modinfo($course->id);

            if (!($PAGE->user_is_editing())) {
                $context = context_course::instance($course->id);
                foreach ($profs as $professor) {
                    $role = $this->get_role($professor->role);
                    $roleAlias = $DB->get_field('role_names', 'name', ['contextid'=>$context->id, 'roleid'=>$role->id]);
                    $teacherList[] = array('HTMLpicture' => $OUTPUT->user_picture($professor, array('size' => true, 'link' => true, 'class' => 'fac-ead_teachers_image', 'alttext' => true)),
                        'fullname' => strtolower($professor->firstname.' '.$professor->lastname),
                        'messagelink' => $CFG->wwwroot."/message/index.php?id=".$professor->id,
                        'role' => $roleAlias ?: $role->name
                    );
                }
                foreach ($modinfo->sections[0] as $modnumber) {
                    $module = $modinfo->cms[$modnumber];
                    if ($module->modname == 'label' && !$module->deletioninprogress && $module->uservisible){
                            $labels[] = array('profInfo' => $module->content);
                    }
                }
            }
            $modulesList = $this->courserenderer->course_section_cm_list($course, $thissection, 0, array('showlabel' => false));
            $modules = '';
            $modules .= $this->reload_ul_modules($modulesList, $course);
            $modules .= $this->courserenderer->course_section_add_cm_control($course, 0, 0);
            $manyTeachers = count($teacherList) > 1 && $isSessionMobile || false;
        }

        $this->mustache = new Mustache_Engine(array('loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/templates'),));

        $html = $this->mustache->render('teachers',array('teachers'=> $teacherList,'labels' => $labels, 'modules'=> $modules, 'manyTeachers' => $manyTeachers));

        return $html;
    }
    protected function reload_ul_modules($moduleList, $course){
        global $PAGE, $CFG;
        $html = '';
        if ($PAGE->user_is_editing()){
            $html = $moduleList;
        }else{
            $modinfo = get_fast_modinfo($course->id);
            $mod_info = $modinfo->sections[0];
            $list = array();
            $rotulo = array();

            foreach ($modinfo->sections[0] as $modnumber) {
                $module = $modinfo->cms[$modnumber];
                if ($module->modname != 'label' && !$module->deletioninprogress){
                    $icon = $module->name;
                    $icon = mb_strtolower($icon);
                    $icon = iconv('UTF-8', 'ASCII//TRANSLIT', $icon);
                    $icon = preg_replace('/\s/','-',$icon);
                    $iconLink = '';
                    switch ($icon){
                        case strpos($icon,'chat-com-o-professor'):
                            $icon = 'icons8-chat-50';
                            $iconLink = $CFG->wwwroot.'/theme/lambda/pix/icons-facimed/icons8-chat-50.png';
                            break;
                        case strpos($icon,'plano-de-ensino'):
                            $icon = 'icons8-training-50-1';
                            $iconLink = $CFG->wwwroot.'/theme/lambda/pix/icons-facimed/icons8-training-50-1.png';
                            break;
                        case strpos($icon,'livro-da-disciplina'):
                            $icon = 'icons8-books-50';
                            $iconLink = $CFG->wwwroot.'/theme/lambda/pix/icons-facimed/icons8-books-50.png';
                            break;
                        case strpos($icon,'avisos'):
                            $icon = 'icons8-commercial-filled-50';
                            $iconLink = $CFG->wwwroot.'/theme/lambda/pix/icons-facimed/icons8-commercial-filled-50.png';
                            break;
                        case strpos($icon,'web-conferencia'):
                            $icon = 'icons8-video-conference-50';
                            $iconLink = $CFG->wwwroot.'/theme/lambda/pix/icons-facimed/icons8-video-conference-50.png';
                            break;
                        default:
                            $icon = 'icons8-document-filled-50';
                            $iconLink = $CFG->wwwroot.'/theme/lambda/pix/icons-facimed/icons8-document-filled-50.png';
                            break;
                    }
                    $resource = false;
                    if($module->modname == 'resource'){
                        $resource = true;
                    }
                    $list[] = array('mod'=>$module,'icon' => $icon, 'iconLink' => $iconLink, 'resource' => $resource);
                }
            }
            $this->mustache = new Mustache_Engine(array('loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/templates'),));
            $html .= $this->mustache->render('module',array('modules'=> $list,'rotulos'=>$rotulo));
        }
        return $html;
    }
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
        global $PAGE;

        $o = '';
        $currenttext = '';
        $sectionstyle = '';

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            }
            if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o.= html_writer::start_tag('li', array('id' => 'section-'.$section->section,
            'class' => 'section main clearfix'.$sectionstyle, 'role'=>'region',
            'aria-label'=> get_section_name($course, $section)));

        // Create a span that contains the section title to be used to create the keyboard section move menu.
        $o .= html_writer::tag('span', get_section_name($course, $section), array('class' => 'hidden sectionname'));

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));



        // When not on a section page, we display the section titles except the general section if null
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $classes = ' accesshide';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }
        $sectionname = html_writer::tag('span', $this->section_title($section, $course));
        if($section->section != 0){
            $classes.= ' notfirst';
        }
        $o.= html_writer::tag('h3', $sectionname, array('class' => 'sectionname' . $classes, 'style'=>'font-weight:bold'));
        $notediting = '';
        if(!$PAGE->user_is_editing()) {
            $notediting = ' notediting';
        }


        if($section->section == 0) {
            $o .= html_writer::start_div('fac-firstsection-content'.$notediting);
        }

        $o .= $this->section_availability($section);

        return $o;
    }

    protected function render_format_facimedead_header(format_facimedead_header $header) {
        $span = html_writer::tag('span',$header->course->fullname);
        return html_writer::tag('h2', $span, array('class'=> 'sectionname teachers_title'));
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $courseformat = course_get_format($course);
        $course = course_get_format($course)->get_course();

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        if($PAGE->user_is_editing()){
            echo html_writer::div('true', '', array('id'=>'user_is_editing', 'style'=>'display:none'));
        }
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        // Now the list of sections..
        echo $this->start_section_list();
        $numsections = course_get_format($course)->get_last_section_number();

        $sectionsarray = array();

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            $sectionsarray[$section] = $thissection;
        }

        foreach ($sectionsarray as $section => $thissection) {
            if ($section == 0) {
                // 0-section is displayed a little different then the others
                if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                    echo $this->section_header($thissection, $course, false, 0);

                    $precontent = $this->first_section_pre_content($section, $thissection, $course);
                    echo $precontent.'</div>';
                    // $modulesList =$this->courserenderer->course_section_cm_list($course, $thissection, 0, array('showlabel' => false));
                    // echo $this->reload_ul_modules($modulesList, $course);
                    // echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);
                    echo $this->section_footer();
                }
                continue;
            }
            if ($section > $numsections) {
                // activities inside this section are 'orphaned', this section will be printed as 'stealth' below
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                ($thissection->visible && !$thissection->available && !empty($thissection->availableinfo));
            if (!$showsection) {
                // If the hiddensections option is set to 'show hidden sections in collapsed
                // form', then display the hidden section message - UNLESS the section is
                // hidden by the availability system, which is set to hide the reason.
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $course->id);
                }

                continue;
            }

            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $course, null);
            } else {
                echo $this->section_header($thissection, $course, false, 0);
                if ($thissection->uservisible) {
                    echo html_writer::start_tag('div', array('class' => 'fac-section-panel'));

                    echo html_writer::start_tag('div', array('class' => 'summary'));
                    echo $this->format_summary_text($thissection);
                    echo html_writer::end_tag('div');

                    echo $this->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, $section, 0);
                }

                echo $this->section_footer();
            }
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $numsections or empty($modinfo->sections[$section])) {
                    // this is not stealth section or it is empty
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            echo $this->change_number_sections($course, 0);
        } else {
            echo $this->end_section_list();
        }

    }

    /**
     * Generate a summary of a section for display on the 'coruse index page'
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param array    $mods (argument not used)
     * @return string HTML to output.
     */
    protected function section_summary($section, $course, $mods) {
        $classattr = 'section main section-summary clearfix';
        $linkclasses = '';

        // If section is hidden then display grey section link
        if (!$section->visible) {
            $classattr .= ' hidden';
            $linkclasses .= ' dimmed_text';
        } else if (course_get_format($course)->is_section_current($section)) {
            $classattr .= ' current';
        }

        $title = get_section_name($course, $section);
        $o = '';
        $o .= html_writer::start_tag('li', array('id' => 'section-'.$section->section,
            'class' => $classattr, 'role'=>'region', 'aria-label'=> $title));

        $o .= html_writer::tag('div', '', array('class' => 'left side'));
        $o .= html_writer::tag('div', '', array('class' => 'right side'));
        $o .= html_writer::start_tag('div', array('class' => 'content'));

        if ($section->uservisible) {
            $title = html_writer::tag('a', $title,
                array('href' => course_get_url($course, $section->section), 'class' => $linkclasses));
        }
        $o .= $this->output->heading($title, 3, 'section-title');

        $o.= html_writer::start_tag('div', array('class' => 'summarytext'));
        $o.= $this->format_summary_text($section);
        $o.= html_writer::end_tag('div');
        $o.= $this->section_activity_summary($section, $course, null);

        $o .= $this->section_availability($section);

        $o .= html_writer::end_tag('div');
        $o .= html_writer::end_tag('li');

        return $o;
    }

    /**
     * Generate a summary of the activites in a section
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course the course record from DB
     * @param array    $mods (argument not used)
     * @return string HTML to output.
     */
    protected function section_activity_summary($section, $course, $mods) {
        $modinfo = get_fast_modinfo($course);
        if (empty($modinfo->sections[$section->section])) {
            return '';
        }

        // Generate array with count of activities in this section:
        $sectionmods = array();
        $total = 0;
        $complete = 0;
        $cancomplete = isloggedin() && !isguestuser();
        $completioninfo = new completion_info($course);
        foreach ($modinfo->sections[$section->section] as $cmid) {
            $thismod = $modinfo->cms[$cmid];

            if ($thismod->modname == 'label') {
                // Labels are special (not interesting for students)!
                continue;
            }

            if ($thismod->uservisible) {
                if (isset($sectionmods[$thismod->modname])) {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modplural;
                    $sectionmods[$thismod->modname]['count']++;
                } else {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modfullname;
                    $sectionmods[$thismod->modname]['count'] = 1;
                }
                if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                    $total++;
                    $completiondata = $completioninfo->get_data($thismod, true);
                    if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                        $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                        $complete++;
                    }
                }
            }
        }

        if (empty($sectionmods)) {
            // No sections
            return '';
        }

        // Output section activities summary:
        $o = '';
        $o.= html_writer::start_tag('div', array('class' => 'section-summary-activities mdl-right'));
        foreach ($sectionmods as $mod) {
            $o.= html_writer::start_tag('span', array('class' => 'activity-count'));
            $o.= $mod['name'].': '.$mod['count'];
            $o.= html_writer::end_tag('span');
        }
        $o.= html_writer::end_tag('div');

        // Output section completion data
        if ($total > 0) {
            $a = new stdClass;
            $a->complete = $complete;
            $a->total = $total;

            $o.= html_writer::start_tag('div', array('class' => 'section-summary-activities mdl-right'));
            $o.= html_writer::tag('span', get_string('progresstotal', 'completion', $a), array('class' => 'activity-count'));
            $o.= html_writer::end_tag('div');
        }

        return $o;
    }



    public function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $USER;

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // check if we are currently in the process of moving a module with JavaScript disabled
        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array();
        $subsectionsstarted = false;  //Indica se as subseções estão sendo renderizadas
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];
                $subsectiontitlefound = false;
                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }

                if ($modulehtml = $this->course_section_cm_list_item($course,
                    $completioninfo, $mod, $sectionreturn, $displayoptions)) {

                    if($mod->modname == 'label' && !$this->page->user_is_editing()) { //Se nao estou no modo edição e encontrei uma label
                        $x = new DOMDocument();
                        $x->loadHTML($modulehtml);
                        $subsectiontitle = $x->getElementsByTagName('h3');

                        if($subsectiontitle->length){ //...  e essa label tem um h3
                            $modulehtml = str_replace('<p>', '', $modulehtml);
                            $modulehtml = str_replace('</p>', '', $modulehtml);
                            $modulehtml =  html_writer::start_div('facimedead_subsectionfull').$modulehtml.html_writer::start_div('facimedead_subsection');
                            if($subsectionsstarted) {
                                $modulehtml = html_writer::end_div().html_writer::end_div().$modulehtml;
                            }
                            $subsectionsstarted = true;
                        }

                    }

                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }
        $sectionoutput = '';
        if (!empty($moduleshtml) || $ismoving) {
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $sectionoutput .= html_writer::tag('li',
                        html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                        array('class' => 'movehere'));
                }

                $sectionoutput .= $modulehtml;
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $sectionoutput .= html_writer::tag('li',
                    html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                    array('class' => 'movehere'));
            }
        }

        // Always output the section module list.
        $editing = '';
        if($this->page->user_is_editing()){
            $editing = ' editing';
        }
        $output .= html_writer::start_div('module-list'.$editing);
        $output .= html_writer::tag('ul', $sectionoutput, array('class' => 'section img-text'));
        $output .= html_writer::div(' ','fac_tag_content');
        $output.= html_writer::end_div();



        return $output;
    }


    public function course_section_cm_list_item($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';
        if ($modulehtml = $this->courserenderer->course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
            $modclasses = 'activity ' . $mod->modname . ' modtype_' . $mod->modname . ' ' . $mod->extraclasses;
            $output .= html_writer::tag('li', $modulehtml, array('class' => $modclasses, 'id' => 'module-' . $mod->id));
        }
        return $output;
    }

    /**
     * Gets the role from it's shortname.
     * @throws Exception
     * @param string $roleshortname
     * @return mixed a fieldset object containing the first matching record, false or exception if error not found depending on mode
     */
    protected function get_role($roleshortname) {
        global $DB;

        if (!$role = $DB->get_record('role', array('shortname' => $roleshortname))) {
            throw new Exception('The specified role with shortname "' . $roleshortname . '" does not exist');
        }

        return $role;
    }

}
