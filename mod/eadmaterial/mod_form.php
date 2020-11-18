<?php //$Id: mod_form.php,v 1.2.2.3 2009/03/19 12:23:11 mudrd8mz Exp $
/**
 * This file defines the main eadmaterial configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 *
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * The form must provide support for, at least these fields:
 *   - name: text element of 64cc max
 *
 * Also, it's usual to use these fields:
 *   - intro: one htmlarea element to describe the activity
 *            (will be showed in the list of activities of
 *             eadmaterial type (index.php) and in the header
 *             of the eadmaterial main page (view.php).
 *   - introformat: The format used to write the contents
 *             of the intro field. It automatically defaults
 *             to HTML when the htmleditor is used and can be
 *             manually selected if the htmleditor is not used
 *             (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
 *             See lib/weblib.php Constants and the format_text()
 *             function for more info
 */

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_eadmaterial_mod_form extends moodleform_mod
{

    function definition()
    {

        global $COURSE;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
        /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        /// Adding the standard "name" field
        $mform->addElement('hidden', 'name', 'eadmaterial', array('size' => '64'));
        $mform->addElement('text', 'materialtitle', get_string('eadmaterialname', 'eadmaterial'), array('size' => '64'));
        $mform->setType('materialtitle', PARAM_TEXT);
        $mform->addRule('materialtitle', null, 'required', null, 'client');
        $mform->addRule('materialtitle', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('text', 'url', get_string('materialurl', 'eadmaterial'), array('size' => '64'));
        $mform->addRule('url', null, 'required', null, 'client');

        /// Adding the required "intro" field to hold the description of the instance
//        $mform->addElement('editor', 'intro', get_string('eadmaterialintro', 'eadmaterial'), array('rows' => 10));
//        $mform->setType('intro', PARAM_RAW);
//        $mform->addRule('intro', get_string('required'), 'required', null, 'client');
//        $mform->addHelpButton('intro', 'editorhelpbutton', 'eadmaterial');


        /// Adding "introformat" field
        //$mform->addElement('html', 'introformat', get_string('format'));

//-------------------------------------------------------------------------------
        /// Adding the rest of eadmaterial settings, spreeading all them into this fieldset
        /// or adding more fieldsets ('header' elements) if needed for better logic
//        $mform->addElement('static', 'label1', 'eadmaterialsetting1', 'Your eadmaterial fields go here. Replace me!');
//
//        $mform->addElement('header', 'eadmaterialfieldset', get_string('eadmaterialfieldset', 'eadmaterial'));
//        $mform->addElement('static', 'label2', 'eadmaterialsetting2', 'Your eadmaterial fields go here. Replace me!');

//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons(true, false, null);
    }
}

?>
