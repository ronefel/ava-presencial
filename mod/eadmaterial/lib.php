<?php  // $Id: lib.php,v 1.7.2.5 2009/04/22 21:30:57 skodak Exp $

/**
 * Library of functions and constants for module eadmaterial
 * This file should have two well differenced parts:
 *   - All the core Moodle functions, neeeded to allow
 *     the module to work integrated in Moodle.
 *   - All the eadmaterial specific functions, needed
 *     to implement all the module logic. Please, note
 *     that, if the module become complex and this lib
 *     grows a lot, it's HIGHLY recommended to move all
 *     these module specific functions to a new php file,
 *     called "locallib.php" (see forum, quiz...). This will
 *     help to save some memory when Moodle is performing
 *     actions across all modules.
 */

define('EADMATERIAL_INCLUDE_TEST', 1);

//TABS
define('EADMATERIAL_TAB1', 1);
    define('EADMATERIAL_TABNAME_PAGEONE', 'firsttabname');
define('EADMATERIAL_TAB2', 2);
    define('EADMATERIAL_TABNAME_PAGETWO', 'secondtabname');
define('EADMATERIAL_TAB3', 3);
   define('EADMATERIAL_TABNAME_PAGETHREE', 'thirdtabname');

//PAGES
// pages of tab 1
// no pages foreseen for first tab

// pages of tab 2
define('EADMATERIAL_TAB2_PAGE1', '1');
    define('EADMATERIAL_TAB2_PAGE1NAME', 'tab2page1');
define('EADMATERIAL_TAB2_PAGE2', '2');
    define('EADMATERIAL_TAB2_PAGE2NAME', 'tab2page2');
define('EADMATERIAL_TAB2_PAGE3', '3');
    define('EADMATERIAL_TAB2_PAGE3NAME', 'tab2page3');
define('EADMATERIAL_TAB2_PAGE4', '4');
    define('EADMATERIAL_TAB2_PAGE4NAME', 'tab2page4');
define('EADMATERIAL_TAB2_PAGE5', '5');
    define('EADMATERIAL_TAB2_PAGE5NAME', 'tab2page5');

// pages of tab 3
define('EADMATERIAL_TAB3_PAGE1', '1');
    define('EADMATERIAL_TAB3_PAGE1NAME', 'tab3page1');
define('EADMATERIAL_TAB3_PAGE2', '2');
    define('EADMATERIAL_TAB3_PAGE2NAME', 'tab3page2');
require_once('locallib.php');
/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $eadmaterial An object from the form in mod_form.php
 * @return int The id of the newly inserted eadmaterial record
 */
function eadmaterial_add_instance($eadmaterial, $mform) {
    global $DB;

    $eadmaterial->timecreated = time();
    # You may have to add extra stuff in here #
    $eadmaterial->introformat = FORMAT_HTML;
    // $context = context_course::instance($eadmaterial->course);
    $eadmaterial->name = $eadmaterial->materialtitle;
    $eadmaterial->id = $DB->insert_record('eadmaterial', $eadmaterial);
    return $eadmaterial->id;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @global object
 * @param object $coursemodule
 * @return cached_cm_info|null
 */
function eadmaterial_get_coursemodule_info($coursemodule) {
    global $DB, $OUTPUT, $CFG;
    if ($label = $DB->get_record('eadmaterial', array('id'=>$coursemodule->instance), 'id, name, materialtitle, intro, url')) {
//        if (empty($label->name)) {
//            // label name missing, fix it
//            $label->name = "eadmaterial{$label->id}";
//            $DB->set_field('eadmaterial', 'name', $label->name, array('id'=>$label->id));
//        }
        $info = new cached_cm_info();
        $imageurl = $OUTPUT->image_url('default', 'mod_eadmaterial');
        // no filtering hre because this info is cached and filtered later
        $html = "<div class='eadmaterial-lista'>
            <div class='eadmaterial-imagem'><img class='img' src='$imageurl' alt='imagem-material'></div>
            <div class='eadmaterial-titulo'>
          <span>$label->materialtitle</span>
        </div><div class='eadmaterial-botao'>
          <div class='eadmaterial-btn'>
            <a href='$CFG->wwwroot/mod/eadmaterial/view.php?id=$coursemodule->id&a=$coursemodule->instance' target='_blank'>
              Iniciar Leitura
            </a>
          </div>
        </div></div>";
        $info->content = $html;
        $info->name  = $label->materialtitle;
        return $info;
    } else {
        return null;
    }
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $eadmaterial An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function eadmaterial_update_instance($eadmaterial) {
    global $DB;
    $eadmaterial->timemodified = time();
    $eadmaterial->id = $eadmaterial->instance;
    $eadmaterial->introformat = FORMAT_HTML;

    if(substr( $eadmaterial->url, 0, 4 ) != "http"){
        $eadmaterial->url = 'http://'.$eadmaterial->url;
    }
    $eadmaterial->name = $eadmaterial->materialtitle;

    return $DB->update_record('eadmaterial', $eadmaterial);
}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function eadmaterial_delete_instance($id) {
    global $DB;
    if (! $eadmaterial = $DB->get_record('eadmaterial', array('id' => $id))) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! $DB->delete_records('eadmaterial', array('id'=>$eadmaterial->id))) {
        $result = false;
    }

    return $result;
}


/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
// function eadmaterial_user_outline($course, $user, $mod, $eadmaterial) {
//     $return = new stdClass();
//     $return->time = $eadmaterial->timemodified;
//     $return->info = '';
//     return $return;
// }


/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
// function eadmaterial_user_complete($course, $user, $mod, $eadmaterial) {
//     return true;
// }


/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in eadmaterial activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function eadmaterial_print_recent_activity($course, $isteacher, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}


/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function eadmaterial_cron () {
    return false;
}


/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of eadmaterial. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $eadmaterialid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function eadmaterial_get_participants($eadmaterialid) {
    return false;
}


/**
 * This function returns if a scale is being used by one eadmaterial
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $eadmaterialid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function eadmaterial_scale_used($eadmaterialid, $scaleid) {
    $return = false;

    //$rec = get_record("eadmaterial","id","$eadmaterialid","scale","-$scaleid");
    //
    //if (!empty($rec) && !empty($scaleid)) {
    //    $return = true;
    //}

    return $return;
}


/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function eadmaterial_install() {
    return true;
}


/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function eadmaterial_uninstall() {
    return true;
}

function eadmaterial_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return false;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_NO_VIEW_LINK:            return false;

        default: return null;
    }
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $eadmaterial    eadmaterial instance
 * @param  stdClass $course         course object
 * @param  stdClass $cm             course module object
 * @param  stdClass $context        context object
 */
function eadmaterial_view($eadmaterial, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $eadmaterial->id
    );

    $event = \mod_eadmaterial\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('eadmaterial', $eadmaterial);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other eadmaterial functions go here.  Each of them must have a name that
/// starts with eadmaterial_
/// Remember (see note in first lines) that, if this section grows, it's HIGHLY
/// recommended to move all funcions below to a new "localib.php" file.


?>
