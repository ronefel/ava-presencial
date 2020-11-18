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
 * format_buttons_renderer
 *
 * @package    format_buttons
 * @author     Rodrigo Brandão <https://www.linkedin.com/in/brandaorodrigo>
 * @copyright  2019 Rodrigo Brandão <brandrod@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/format/topics/renderer.php');

/**
 * format_buttons_renderer
 *
 * @package    format_buttons
 * @author     Rodrigo Brandão <https://www.linkedin.com/in/brandaorodrigo>
 * @copyright  2019 Rodrigo Brandão <brandrod@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_buttons_renderer extends format_topics_renderer
{

    /**
     * get_button_section
     *
     * @param stdclass $course
     * @param string $name
     * @return string
     */
    protected function get_color_config($course, $name)
    {
        $return = false;
        if (isset($course->{$name})) {
            $color = str_replace('#', '', $course->{$name});
            $color = substr($color, 0, 6);
            if (preg_match('/^#?[a-f0-9]{6}$/i', $color)) {
                $return = '#'.$color;
            }
        }
        return $return;
    }

    /**
     * number_to_roman
     *
     * @param integer $number
     * @return string
     */
    protected function number_to_roman($number)
    {
        $number = intval($number);
        $return = '';
        $romanarray = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        ];
        foreach ($romanarray as $roman => $value) {
            $matches = intval($number / $value);
            $return .= str_repeat($roman, $matches);
            $number = $number % $value;
        }
        return $return;
    }

    /**
     * number_to_alphabet
     *
     * @param integer $number
     * @return string
     */
    protected function number_to_alphabet($number)
    {
        $number = $number - 1;
        $alphabet = range("A", "Z");
        if ($number <= 25) {
            return $alphabet[$number];
        } else if ($number > 25) {
            $dividend = ($number + 1);
            $alpha = '';
            while ($dividend > 0) {
                $modulo = ($dividend - 1) % 26;
                $alpha = $alphabet[$modulo] . $alpha;
                $dividend = floor((($dividend - $modulo) / 26));
            }
            return $alpha;
        }
    }

    /**
     * Rone Santos - 05/12/2019
     * button_to_icon
     *
     * @param integer $number
     * @return string
     */
    protected function button_to_icon($thissection) {
        global $PAGE, $CFG;

        switch (mb_strtoupper($thissection->name)) {
            case 'PLANO DE ENSINO' :
                $icon = "<div class='buttonsection-icon'><img id='icon-planodeensino' src='$CFG->wwwroot/course/format/buttons/pix/icons8-training-50-1.png' /> <div class='tip'>Plano de Ensino</div></div><div class='section-title-under'>Plano de Ensino</div>";
                break;
            case 'WEB CONFERÊNCIA' :
                $icon = "<div class='buttonsection-icon'><img id='icon-webconferencia' src='$CFG->wwwroot/course/format/buttons/pix/icons8-video-conference-50.png' /><div class='tip'>Web Conferência</div></div><div class='section-title-under'>Web Conferência</div>";
                break;
            case 'SALA DE AULA VIRTUAL' :
                $icon = "<div class='buttonsection-icon'><img id='icon-webconferencia' src='$CFG->wwwroot/course/format/buttons/pix/icons8-video-conference-50.png' /><div class='tip'>Sala de Aula Virtual</div></div><div class='section-title-under'>Sala de Aula Virtual</div>";
                break;
            case 'MATERIAL DIDÁTICO' :
                $icon = "<div class='buttonsection-icon'><img id='icon-materialdidatico' src='$CFG->wwwroot/course/format/buttons/pix/icons8-books-50.png' /><div class='tip'>Material Didático</div></div><div class='section-title-under'> Material Didático</div>";
                break;
            case 'ATIVIDADES AVALIATIVAS' :
                $icon = "<div class='buttonsection-icon'><img id='icon-atividadeavaliativa' src='$CFG->wwwroot/course/format/buttons/pix/icons8-hand-with-pen-filled-50.png' /><div class='tip'>Atividades Avaliativas</div></div><div class='section-title-under'>Atividades Avaliativas</div>";
                break;
            case 'ATIVIDADES DE FIXAÇÃO' :
                $icon = "<div class='buttonsection-icon'><img id='icon-atividadefixacao' src='$CFG->wwwroot/course/format/buttons/pix/icons8-choice-filled-50.png' /><div class='tip'>Atividades de Fixação</div></div><div class='section-title-under'>Atividades de Fixação</div>";
                break;
            case 'BATE-PAPO' :
                $icon = "<div class='buttonsection-icon'><img id='icon-batepapo' src='$CFG->wwwroot/course/format/buttons/pix/icons8-chat-50.png' /><div class='tip'>Bate-Papo</div></div><div class='section-title-under'>Bate-Papo</div>";
                break;
            case 'MULTIMÍDIA' :
                $icon = "<div class='buttonsection-icon'><img id='icon-multimidia' src='$CFG->wwwroot/course/format/buttons/pix/icons8-play-50.png'/><div class='tip'>Multimídia</div></div><div class='section-title-under'>Multimídia</div>";
                break;
            case 'RECADOS' :
                $icon = "<div class='buttonsection-icon'><img id='icon-recados' src='$CFG->wwwroot/course/format/buttons/pix/icons8-note-filled-50.png'/><div class='tip'>Recados</div></div><div class='section-title-under'>Recados</div>";
                break;
            case 'CRONOGRAMA' :
                $icon = "<div class='buttonsection-icon'><img id='icon-cronograma' src='$CFG->wwwroot/course/format/buttons/pix/icons8-calendar-filled-50.png'/></i><div class='tip'>Cronograma</div></div><div class='section-title-under'>Cronograma</div>";
                break;
            case 'FÓRUM DE DISCUSSÃO' :
                $icon = "<div class='buttonsection-icon'><img id='icon-discussao' src='$CFG->wwwroot/course/format/buttons/pix/icons8-discussion-forum-50.png'/><div class='tip'>Fórum de Discussão</div></div><div class='section-title-under'>Fórum de Discussão</div>";
                break;
            case 'CERTIFICADO' :
                $icon = "<div class='buttonsection-icon'><img id='icon-certificado' src='$CFG->wwwroot/course/format/buttons/pix/icons8-certificate-50.png'/></i><div class='tip'>Certificado</div></div><div class='section-title-under'>Certificado</div>";
                break;
            case 'BATE PAPO / FÓRUM' :
                $icon = "<div class='buttonsection-icon'><img id='icon-forum' src='$CFG->wwwroot/course/format/buttons/pix/icons8-collaboration-50.png' /></i><div class='tip'>Bate-papo / Fórum</div></div><div class='section-title-under'>Bate-papo / Fórum</div>";
                break;
            case 'BATE-PAPO / CHAT' :
                $icon = "<div class='buttonsection-icon'><img id='icon-chat' src='$CFG->wwwroot/course/format/buttons/pix/icons8-chat-50.png' /><div class='tip'>Bate-papo / Chat</div></div><div class='section-title-under'>Bate-papo / Chat</div>";
                break;
            case 'GABARITOS' :
                $icon = "<div class='buttonsection-icon'><img id='icon-gabarito' src='$CFG->wwwroot/course/format/buttons/pix/icons8-pass-fail-50.png' /></i><div class='tip'>Gabaritos</div></div><div class='section-title-under'>Gabaritos</div>";
                break;
            default :
                $icon = "<div class='buttonsection-icon'><img id='icon-$thissection->name' src='$CFG->wwwroot/course/format/buttons/pix/favicon2.png' /></i><div class='tip'>$thissection->name</div></div><div class='section-title-under'>$thissection->name</div>";
                break;
        }

        return $icon;

    }

    /**
     * get_button_section
     *
     * @param stdclass $course
     * @param string $sectionvisible
     * @return string
     */
    protected function get_button_section($course, $sectionvisible)
    {
        global $PAGE;
        $html = '';
        $css = '';
        if ($colorcurrent = $this->get_color_config($course, 'colorcurrent')) {
            $css .=
            '#buttonsectioncontainer .buttonsection.current {
                background: ' . $colorcurrent . ';
            }
            ';
        }
        if ($colorvisible = $this->get_color_config($course, 'colorvisible')) {
            $css .=
            '#buttonsectioncontainer .buttonsection.sectionvisible {
                background: ' . $colorvisible . ';
            }
            ';
        }
        if ($css) {
            $html .= html_writer::tag('style', $css);
        }
        $withoutdivisor = true;
        for ($k = 1; $k <= 12; $k++) {
            if ($course->{'divisor' . $k}) {
                $withoutdivisor = false;
            }
        }
        if ($withoutdivisor) {
            $course->divisor1 = 999;
        }
        $divisorshow = false;
        $count = 1;
        $currentdivisor = 1;
        $modinfo = get_fast_modinfo($course);
        $inline = '';
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                continue;
            }
            if ($section > $course->numsections) {
                continue;
            }
            if ($course->hiddensections && !(int)$thissection->visible) {
                continue;
            }
            if (isset($course->{'divisor' . $currentdivisor}) &&
                $count > $course->{'divisor' . $currentdivisor}) {
                $currentdivisor++;
                $count = 1;
            }
            if (isset($course->{'divisor' . $currentdivisor}) &&
                $course->{'divisor' . $currentdivisor} != 0 &&
                !isset($divisorshow[$currentdivisor])) {
                $currentdivisorhtml = format_string($course->{'divisortext' . $currentdivisor});
                $currentdivisorhtml = str_replace('[br]', '<br>', $currentdivisorhtml);
                $currentdivisorhtml = html_writer::tag('div', $currentdivisorhtml, ['class' => 'divisortext']);
                if ($course->inlinesections) {
                    $inline = 'inlinebuttonsections';
                }
                //$html .= html_writer::tag('div', $currentdivisorhtml, ['class' => "divisorsection $inline"]);
                $divisorshow[$currentdivisor] = true;
            }
            $id = 'buttonsection-' . $section;
            if ($course->sequential) {
                $name = $section;
            } else {
                if (isset($course->{'divisor' . $currentdivisor}) &&
                $course->{'divisor' . $currentdivisor} == 1) {
                    $name = '&bull;&bull;&bull;';
                } else {
                    $name = $count;
                }
            }
            if ($course->sectiontype == 'alphabet' && is_numeric($name)) {
                $name = $this->number_to_alphabet($name);
            }
            if ($course->sectiontype == 'roman' && is_numeric($name)) {
                $name = $this->number_to_roman($name);
            }

            $class = 'buttonsection';
            $onclick = 'M.format_buttons.show(' . $section . ',' . $course->id . ')';
            if (!$thissection->available &&
                !empty($thissection->availableinfo)) {
                $class .= ' sectionhidden';
            } else if (!$thissection->uservisible || !$thissection->visible) {
                $class .= ' sectionhidden';
                $onclick = false;
            }
            if ($course->marker == $section) {
                $class .= ' current';
            }
            if ($sectionvisible == $section) {
                $class .= ' sectionvisible';
            }            
            
            /**
             * Rone Santos - 05/12/2019
             * Seta o tipo icon
             */
            if ($course->sectiontype == 'icon' || !$course->sectiontype) {
                $name = $this->button_to_icon($thissection);
                $class .= ' icon';
            }

            if ($PAGE->user_is_editing()) {
                $onclick = false;
            }
            $html .= html_writer::tag('div', $name, ['id' => $id, 'class' => $class, 'onclick' => $onclick]);
            $count++;
        }
        $html = html_writer::tag('div', $html, ['id' => 'buttonsectioncontainer', 'class' => $course->buttonstyle]);
        if ($PAGE->user_is_editing()) {
            $html .= html_writer::tag('div', get_string('editing', 'format_buttons'), ['class' => 'alert alert-warning alert-block fade in']);
        }
        return $html;
    }

    /**
     * start_section_list
     *
     * @return string
     */
    protected function start_section_list()
    {
        return html_writer::start_tag('ul', ['class' => 'buttons']);
    }

    /**
     * section_header
     *
     * @param stdclass $section
     * @param stdclass $course
     * @param bool $onsectionpage
     * @param int $sectionreturn
     * @return string
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null)
    {
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

        // button format - ini
        if ($course->showdefaultsectionname) {
            $o.= $this->output->heading($sectionname, 3, 'sectionname' . $classes);
        }

        /**
        * Rone Santos
        * Lista os professores
        */
        if($section->section == 0){
        $o .= $this->listTeachers($course->id);
        }

        // button format - end

        $o .= $this->section_availability($section);

        $o .= html_writer::start_tag('div', array('class' => 'summary'));
        if ($section->uservisible || $section->visible) {
            // Show summary if section is available or has availability restriction information.
            // Do not show summary if section is hidden but we still display it because of course setting
            // "Hidden sections are shown in collapsed form".
            $o .= $this->format_summary_text($section);
        }
        $o .= html_writer::end_tag('div');

        return $o;
    }

    /**
     * Rone Santos
     * Renderiza o título do curso
     */
    protected function render_format_buttons_header(format_buttons_header $header) {
        $span = html_writer::tag('span',$header->course->fullname);
        return html_writer::tag('h2', $span, array('class'=> 'sectionname teachers_title mb-4'));
    }

    /**
     * print_multiple_section_page
     *
     * @param stdclass $course
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused)
    {
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);

        // buttons format - ini
        if (isset($_COOKIE['sectionvisible_' . $course->id])) {
            $sectionvisible = $_COOKIE['sectionvisible_' . $course->id];
        } else if ($course->marker > 0) {
            $sectionvisible = $course->marker;
        } else {
            $sectionvisible = 1;
        }
        $htmlsection = false;
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            $htmlsection[$section] = '';
            if ($section == 0) {
                $section0 = $thissection;
                continue;
            }
            if ($section > $course->numsections) {
                continue;
            }
            /* if is not editing verify the rules to display the sections */
            if (!$PAGE->user_is_editing()) {
                if ($course->hiddensections && !(int)$thissection->visible) {
                    continue;
                }
                if (!$thissection->available && !empty($thissection->availableinfo)) {
                    $htmlsection[$section] .= $this->section_header($thissection, $course, false, 0);
                    continue;
                }
                if (!$thissection->uservisible || !$thissection->visible) {
                    $htmlsection[$section] .= $this->section_hidden($section, $course->id);
                    continue;
                }
            }
            $htmlsection[$section] .= $this->section_header($thissection, $course, false, 0);
            if ($thissection->uservisible) {
                $htmlsection[$section] .= $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                $htmlsection[$section] .= $this->courserenderer->course_section_add_cm_control($course, $section, 0);
            }
            $htmlsection[$section] .= $this->section_footer();
        }
        if ($section0->summary || !empty($modinfo->sections[0]) || $PAGE->user_is_editing()) {
            $htmlsection0 = $this->section_header($section0, $course, false, 0);
            $htmlsection0 .= $this->courserenderer->course_section_cm_list($course, $section0, 0);
            $htmlsection0 .= $this->courserenderer->course_section_add_cm_control($course, 0, 0);
            $htmlsection0 .= $this->section_footer();
        }
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');
        echo $this->course_activity_clipboard($course, 0);
        echo $this->start_section_list();
        if ($course->sectionposition == 0 and isset($htmlsection0)) {
            echo html_writer::tag('span', $htmlsection0, ['class' => 'above']);
        }
        echo $this->get_button_section($course, $sectionvisible);
        foreach ($htmlsection as $current) {
            echo $current;
        }
        if ($course->sectionposition == 1 and isset($htmlsection0)) {
            echo html_writer::tag('span', $htmlsection0, ['class' => 'below']);
        }
        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }
            echo $this->end_section_list();
            echo html_writer::start_tag('div', ['id' => 'changenumsections', 'class' => 'mdl-right']);
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php', ['courseid' => $course->id,
                'increase' => true, 'sesskey' => sesskey()]);
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon.get_accesshide($straddsection), ['class' => 'increase-sections']);
            if ($course->numsections > 0) {
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php', ['courseid' => $course->id,
                    'increase' => false, 'sesskey' => sesskey()]);
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link(
                    $url,
                    $icon.get_accesshide($strremovesection),
                    ['class' => 'reduce-sections']
                );
            }
            echo html_writer::end_tag('div');
        } else {
            echo $this->end_section_list();
        }
        if (!$PAGE->user_is_editing()) {
            $PAGE->requires->js_init_call('M.format_buttons.init', [$course->numsections, $sectionvisible, $course->id]);
        }
        // button format - end
    }


    protected function listTeachers($courseId){
        global $DB, $OUTPUT, $PAGE, $CFG;

        require_once(__DIR__.'/../../../theme/lambda/Mobile_Detect.php');
        $isSessionMobile = (new Mobile_Detect)->isMobile();

        $teacherList = array();
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

            $profs = $DB->get_records_sql($sql_profs, array($courseId));


            if (!($PAGE->user_is_editing())) {
                $context = context_course::instance($courseId);
                foreach ($profs as $professor) {
                    $role = $this->get_role($professor->role);
                    $roleAlias = $DB->get_field('role_names', 'name', ['contextid'=>$context->id, 'roleid'=>$role->id]);
                    $teacherList[] = array('HTMLpicture' => $OUTPUT->user_picture($professor, array('size' => true, 'link' => true, 'class' => 'fac-ead_teachers_image', 'alttext' => true)),
                        'fullname' => strtolower($professor->firstname.' '.$professor->lastname),
                        'messagelink' => $CFG->wwwroot."/message/index.php?id=".$professor->id,
                        'role' => $roleAlias ?: $role->name
                    );
                }
            }
            $manyTeachers = count($teacherList) > 1 && $isSessionMobile || false;

        $this->mustache = new Mustache_Engine(array('loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/templates'),));

        $html = $this->mustache->render('teachers',array('teachers'=> $teacherList, 'manyTeachers' => $manyTeachers));

        return $html;
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
