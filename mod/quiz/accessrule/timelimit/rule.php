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
 * Implementaton of the quizaccess_timelimit plugin.
 *
 * @package    quizaccess
 * @subpackage timelimit
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');


/**
 * A rule representing the time limit. It does not actually restrict access, but we use this
 * class to encapsulate some of the relevant code.
 *
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_timelimit extends quiz_access_rule_base {

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {

        if (empty($quizobj->get_quiz()->timelimit) || $canignoretimelimits) {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    public function description() {
        return get_string('quiztimelimit', 'quizaccess_timelimit',
                format_time($this->quiz->timelimit));
    }

    public function end_time($attempt) {
        $timedue = $attempt->timestart + $this->quiz->timelimit;
        if ($this->quiz->timeclose) {
            $timedue = min($timedue, $this->quiz->timeclose);
        }
        return $timedue;
    }

    public function time_left_display($attempt, $timenow) {
        // If this is a teacher preview after the time limit expires, don't show the time_left
        $endtime = $this->end_time($attempt);
        if ($attempt->preview && $timenow > $endtime) {
            return false;
        }
        return $endtime - $timenow;
    }

    public function is_preflight_check_required($attemptid) {
        // Warning only required if the attempt is not already started.
        return $attemptid === null;
    }

    public function add_preflight_check_form_fields(mod_quiz_preflight_check_form $quizform,
            MoodleQuickForm $mform, $attemptid) {

        /**
         * Rone Santos
         * mostra um texto personalizado se marcar uma questão por página
         * e habilitado o tempo limite
         */
        if($this->quiz->questionsperpage == '0'){
            $mform->addElement('header', 'honestycheckheader',
                    get_string('confirmstartheader', 'quizaccess_timelimit'));
            $mform->addElement('static', 'honestycheckmessage', '',
                    get_string('confirmstart', 'quizaccess_timelimit', format_time($this->quiz->timelimit)));
        } else {
            $mform->addElement('header', 'honestycheckheader', 'ORIENTAÇÕES IMPORTANTES');
            $mform->addElement('static', 'honestycheckmessage', '',
                    '<p>1. Faça a avaliação em um ambiente tranquilo.</p>
                     <p>2. Será apresentada <b>'.$this->quiz->questionsperpage.' QUESTÃO POR PÁGINA</b>.</p>
                     <p>3. Não esqueça de responder a questão antes de avançar para a próxima questão.</p>
                     <p>4. Para avançar para a próxima questão, basta clicar em <span><b><u>"Próxima página"</u></b></span></p>
                     <p>5. <b><u><span style="color: rgb(239, 69, 64);">ATENÇÃO:</span></u></b> ao avançar para a próxima página, <b>'.($this->quiz->navmethod == 'free' ? '<u>SERÁ POSSÍVEL VOLTAR</u>' : '<u>NÃO SERÁ POSSÍVEL VOLTAR</u>').' </b> à(s) página(s) anterior(es).</p>
                     <p>6. A avaliação terá duração de <b>'.format_time($this->quiz->timelimit).'</b></p>
                     <p>7. O <b>tempo regressivo</b> da avaliação será apresentado no canto superior esquerdo da tela (cronômetro regressivo).</p>
                     <p>8. O <b>cronômetro apresenta o tempo restante</b> para realização da avaliação, contando o tempo, a partir do horário de início da avaliação proposto pelo professor.</p>
                     <p>9. Ao terminar a avaliação, é necessário clicar em <span><b>"<span><u>finalizar tentativa"</u></span></b></span>, disponível na última página.</p>
                     <p>10. Serão <b>anuladas</b> as questões discursivas, na observância de cópias nas respostas.</p>');
        }
    }
}
