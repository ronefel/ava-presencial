<?php
/**
 * Criado com PhpStorm.
 * Autor: Ello Oliveira
 * Data: 03/11/2017
 * Hora: 12:49
 */

class block_teachers extends  block_base{
    public function init() {
        $this->title = get_string('teachers:title', 'block_teachers');
    }
    public function hide_header() {
        return false;
    }
    function has_config() {return true;}

    public function get_content() {
        global $DB, $COURSE, $CFG, $OUTPUT, $PAGE;
        $PAGE->requires->js_call_amd('core_message/message_user_button', 'send', array('.message-user-button'));
        $id          = optional_param('id', 0, PARAM_INT);
        $name        = optional_param('name', '', PARAM_TEXT);
        $edit        = optional_param('edit', -1, PARAM_BOOL);
        $hide        = optional_param('hide', 0, PARAM_INT);
        $show        = optional_param('show', 0, PARAM_INT);
        $idnumber    = optional_param('idnumber', '', PARAM_RAW);
        $sectionid   = optional_param('sectionid', 0, PARAM_INT);
        $section     = optional_param('section', 0, PARAM_INT);
        $move        = optional_param('move', 0, PARAM_INT);
        $marker      = optional_param('marker',-1 , PARAM_INT);
        $switchrole  = optional_param('switchrole',-1, PARAM_INT); // Deprecated, use course/switchrole.php instead.
        $return      = optional_param('return', 0, PARAM_LOCALURL);
        $params = array();
        if (!empty($name)) {
            $params = array('shortname' => $name);
        } else if (!empty($idnumber)) {
            $params = array('idnumber' => $idnumber);
        } else if (!empty($id)) {
            $params = array('id' => $id);
        }else {
            //Se cair aqui, significa que o bloco está numa área do site que não é um curso.
            //Isso pode acontecer logo quando adicionamos o plugin e ainda é necessário configurá-lo para aparecer
            // apenas nas páginas dos cursos, por isso não levantamos um erro aqui.
            //print_error('unspecifycourseid', 'error');
            $this->content = new stdClass;
            $this->content->text.="<h3 class='sectionname' style='color:rgb(85, 85, 85)'>".get_string("teachers:title", "block_teachers")."</h3>";
            return $this->content;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        //Obtemos a lista de professores matriculados no curso

        $sql_profs = "SELECT DISTINCT usr.id, c.shortname, usr.firstname, usr.lastname, usr.username, r.shortname, usr.picture, usr.description, usr.imagealt, usr.email
        FROM {course} AS c
        INNER JOIN {context} AS cx ON c.id = cx.instanceid
        AND cx.contextlevel = '50'
        INNER JOIN {role_assignments} AS ra ON cx.id = ra.contextid
        INNER JOIN {role} AS r ON ra.roleid = r.id
        INNER JOIN {user} AS usr ON ra.userid = usr.id
        where c.id = ? 
        and r.shortname = 'editingteacher'";

        $profs = $DB->get_records_sql($sql_profs, array($COURSE->id));
        $plural = count($profs)>1;

        $this->content         =  new stdClass;
        $this->content->text = '';

        //$this->content->text .= $PAGE->pagetype. ' '. $PAGE->pagelayout ;

        //Título do curso, desnecessário se o renderer do formato de curso já o renderiza.
        if(get_config('teachers', 'teachershowcoursetitle')) {
            $this->content->text .= '<h2 class="sectionname teachers_title" style="color:#555"><span>' . $COURSE->fullname . '</span></h2><br/>';
        }

        $this->content->text .= '<div style="background-color: #F2F2F2;">';

        //Imprime a label no singular ou no plural, se ela estver habilitada nas configurações
        if(count($profs)>0) {
            $showlabel = get_config("teachers", "show_teacherslabel");
            if ($showlabel) {
                $this->content->text .= "<h5 style='padding: 5px'>" . ($plural ? get_config("teachers", "teacherlabelplural") : get_config("teachers", "teacherlabel")) . "</h5>";
            }
        }
        else{
            $this->content->text .= "<h5 style='padding: 5px;'>" . get_string("teachers:noteacher", "block_teachers") . "</h5>";
        }

        //Inicia a tag de conteúdo do bloco
        $this->content->text.='<div class="teachers-block-content">';



        //Exibe a lista de professores
        foreach ($profs as $professor) {

            $this->content->text .= "<div class='block-teacher-course'>
<div style='display:flex'>
<div class=\"block-teacher-img-content\">" . $OUTPUT->user_picture($professor, array('size'=> get_config("teachers", "teacherimagesize").'px', 'link' => true, 'class' => 'block_teachers_image', 'alttext' => true, 'popup' => false)) .
                "</div>";
            $this->content->text .= "<div class='block-teacher-info' ><h4>".strtoupper($professor->firstname. ($professor->lastname == "Professor"?"":' '.$professor->lastname))."</h4>";
            $this->content->text .= "<div></div><a class='btn btn-primary btn-small' target='_blank' href='".$CFG->wwwroot."/message/index.php?id=".$professor->id."'><span class='header-button-title'>Enviar mensagem</span></a></div></div><div class='block_teacher_description'>".$professor->description . "</div><button class='accordion'><i class='fa fa-angle-down'></i></button></div>";
        }

        return $this->content;
    }

    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('defaulttitle', 'block_simplehtml');
            } else {
                $this->title = $this->config->title;
            }

            if (empty($this->config->text)) {
                $this->config->text = get_string('defaulttext', 'block_simplehtml');
            }
        }
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function applicable_formats() {
        //O bloco precisa aparecer na página inicial do site enquanto estamos configurando para aparecer em todos os cursos,
        // por isso o 'site-index' => true. Se tivéssemos apenas o course-view como true, seria necessário configurar curso por curso.
        //Para fazer o bloco aparecer em todos os cursos, devemos editá-lo na página inicial do site, configurá-lo para aparecer em todo o site,
        // em seguida ir em um curso e configurar apara aparecer apenas nas páginas iniciais dos cursos.
        return array(
            'all' => false,
            'course-view' => true,
            'site-index' => true);
    }

    function is_coursemainpage() {
        global $PAGE, $ME;
var_dump($ME);
        $result = false;

        $url = null;
        if ($PAGE->has_set_url()) {
            $url = $PAGE->url;
        } else if ($ME !== null) {
            $url = new moodle_url(str_ireplace('/view.php', '/', $ME));
        }

        if ($url !== null) {
            $result = $url->compare(context_system::instance()->get_url(), URL_MATCH_BASE);
        }

        return $result;
    }


}