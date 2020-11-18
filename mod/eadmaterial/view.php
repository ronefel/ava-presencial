<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $
    
    /**
     * This page prints a particular instance of eadmaterial
     *
     * @author  Your Name <your@email.address>
     * @version $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $
     * @package mod/eadmaterial
     */

    
    
    require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
    require_once(dirname(__FILE__).'/lib.php');


    $id = optional_param('id', 0, PARAM_INT); // course_module ID, or
    $a  = optional_param('a', 0, PARAM_INT);  // eadmaterial instance ID
    global $DB, $USER;  

    if ($id) {
        if (! $cm = get_coursemodule_from_id('eadmaterial', $id)) {
            print_error('Course Module ID was incorrect');
        }
    
        if (! $course = $DB->get_record('course', array('id'=> $cm->course))) {
            print_error('Course is misconfigured');
        }
    
        if (! $eadmaterial = $DB->get_record('eadmaterial', array('id' => $cm->instance))) {
            print_error('Course module is incorrect');
        }

        // $eadmaterial_log = new stdClass();
        // $eadmaterial_log->eadmaterial_id = $eadmaterial->id;
        // $eadmaterial_log->event = 'viewed';
        // $eadmaterial_log->userid = $USER->id;
        // $eadmaterial_log->timestamp = time();

        // $newid = $DB->insert_record('eadmaterial_log', $eadmaterial_log);
    
    } else if ($a) {
        if (! $cm = get_coursemodule_from_instance('eadmaterial', $a)) {
            print_error('Course Instance ID was incorrect');
        }
    
        if (! $course = $DB->get_record('course', array('id'=> $cm->course))) {
            print_error('Course is misconfigured');
        }
    
        if (! $eadmaterial = $DB->get_record('eadmaterial', array('id' => $cm->instance))) {
            print_error('Course module is incorrect');
        }
    
    } else {
        print_error('You must specify a course_module ID or an instance ID');
    }

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);
// Completion and trigger events.
eadmaterial_view($eadmaterial, $course, $cm, $modulecontext);

redirect($eadmaterial->url);
?>