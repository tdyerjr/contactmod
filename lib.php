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
 * Library of interface functions and constants for module contactmod
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the contactmod specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_contactmod
 * @copyright  2014 Tim Dyer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//Tim Added 7/15


function contactmod_cm_info_view(cm_info $cm) {
	global $CFG, $DB, $COURSE;
	$info = "<br><br>Cannot find Module instance: ".$cm->instance.".<br>";
	//$cm->set_after_link( print_r( $cm->id, true ) );
	//return;
	if ($contactmod = $DB->get_record('contactmod', array('id'=>$cm->instance), 'id, name, intro, introformat')) {
		$info = "";
		if (empty($contactmod->name)) {
			// contactmod name missing, fix it
			$contactmod->intro = "contactmod{$contactmod->id}";
			$DB->set_field('contactmod', 'name', $contactmod->name, array('id'=>$contactmod->id));
		
		}	
		  
		 
		//find the database id's for our custom profile field extentions
		$fieldsql = "SELECT id,shortname FROM {user_info_field} WHERE shortname IN ('officehoursmod')";
		$fields = $DB->get_recordset_sql($fieldsql);
		$fieldidlist = '';
		$fieldname = Array();
		$idcount = 0;
		foreach($fields as $field) {
			if($idcount > 0) $fieldidlist .= ',';
			$fieldidlist .= $field->id;
			$fieldname[$field->id] = $field->shortname;
			$idcount++;
		}
		$fields->close();
		
		$facultyroles = get_config('contactmod','facultyroles');
		//$info .= "\n<!--\n" . print_r($facultyroles,true) . "-->\n";
		
		if(!empty($COURSE)) {
		
			//get all enrolled users with the globally selected roles (eg. 'teacher'(3)) at the course level (context 50) :
			$facultyquery = "SELECT u.id, u.firstname, u.lastname, u.username, u.email, u.picture, u.phone1, u.phone2, d.data FROM {user} u
				LEFT JOIN {user_info_data} d
					ON u.id = d.userid
					AND d.fieldid = $fieldidlist
				WHERE u.id in (SELECT userid
					FROM {context} c
					JOIN {role_assignments} ra ON c.id = ra.contextid
					WHERE c.contextlevel = 50
					AND c.instanceid=" . $COURSE->id . " AND ra.roleid IN ($facultyroles))";
			
			$facultylist = $DB->get_recordset_sql($facultyquery);
		
			
			foreach ($facultylist as $faculty) {
				//get profile images, from /user/lib.php and /lib/weblib.php
				
					$facultycontext = context_user::instance($faculty->id, MUST_EXIST);
					$imageurl = moodle_url::make_pluginfile_url($facultycontext->id, 'user', 'icon', NULL, '/', 'f1');
					$faculty->profileimageurl = $imageurl->out(false);
						
				//header title for this faculty
				
				if(isset($faculty->facultyheadermod) && $faculty->facultyheadermod <> '')
					$fheader = s($faculty->facultyheadermod);
				else
					$fheader = s($faculty->firstname . ' ' . $faculty->lastname);
				
				//start with link to profile
				$info .= '<tr><th style=" border: 0px;"><a href="' . $CFG->wwwroot . '/user/view.php?id=' .$faculty->id . '&course=' . $COURSE->id . '" title="' . get_string('profileclicktitle','contactmod') . $fheader . '">';
				
				//if set, add image
				if(!empty($faculty->profileimageurl))
					$info .= '<img src="' . $faculty->profileimageurl . '" alt="' . $fheader . '" width="170" height="175" style="float:left"/></th>';
					
					
				//add header
				$info .= '<th style=" border: 0px;"></br>' . '<p>Instructor: ' . $fheader . '</a></p>';
				
												
				//Cell
				//if(isset($faculty->phone2) && $faculty->phone2 <> '')
					//$info .= '<p>' . get_string('phonelabel2','contactmod') . ': ' . s($faculty->phone2) . '</p>';
				
						
				//Office hours
				if(isset($faculty->data) && $faculty->data <> '')
					$info .= '<p>' . get_string('officehours','contactmod') . ': ' . s($faculty->data) . '</p>';
					
				//Phone
				if(isset($faculty->phone1) && $faculty->phone1 <> '')
					$info .= '<p>' . get_string('phonelabel','contactmod') . ': ' . s($faculty->phone1) . '</p>';

				//email
				$info .= '<p>' . get_string('emaillabel','contactmod') . ': ' . obfuscate_mailto($faculty->email,'') . '</p>' . $contactmod->intro . '</th></tr>';
				
				
			
					
			}
			
			$facultylist->close();
				 
		}

	//show information
	$info = '<br><table style="border: 1px #000 solid; width:550px; max-height:200px; display: block; overflow-y: auto; " align="center">' . $info . '</table>' ;
	
			
	}
	
	$cm->set_after_link($info);
	
}


////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function contactmod_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return false;
        case FEATURE_SHOW_DESCRIPTION:  return false;
		case FEATURE_IDNUMBER:          return false;
        case FEATURE_GROUPS:            return false;
        case FEATURE_GROUPINGS:         return false;;
		case FEATURE_MOD_ARCHETYPE:     return MOD_ARCHETYPE_RESOURCE;
		
        default:                        return null;
    }
}

/**
 * Saves a new instance of the contactmod into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $contactmod An object from the form in mod_form.php
 * @param mod_contactmod_mod_form $mform
 * @return int The id of the newly inserted contactmod record
 */
function contactmod_add_instance(stdClass $contactmod, mod_contactmod_mod_form $mform = null) {
    global $DB;

    $contactmod->timecreated = time();

    # You may have to add extra stuff in here #

    return $DB->insert_record('contactmod', $contactmod);
}

/**
 * Updates an instance of the contactmod in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $contactmod An object from the form in mod_form.php
 * @param mod_contactmod_mod_form $mform
 * @return boolean Success/Fail
 */
function contactmod_update_instance(stdClass $contactmod, mod_contactmod_mod_form $mform = null) {
    global $DB;

    $contactmod->timemodified = time();
    $contactmod->id = $contactmod->instance;

    # You may have to add extra stuff in here #

    return $DB->update_record('contactmod', $contactmod);
}

/**
 * Removes an instance of the contactmod from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function contactmod_delete_instance($id) {
    global $DB;

    if (! $contactmod = $DB->get_record('contactmod', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('contactmod', array('id' => $contactmod->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function contactmod_user_outline($course, $user, $mod, $contactmod) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $contactmod the module instance record
 * @return void, is supposed to echp directly
 */
function contactmod_user_complete($course, $user, $mod, $contactmod) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in contactmod activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function contactmod_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link contactmod_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function contactmod_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see contactmod_get_recent_mod_activity()}

 * @return void
 */
function contactmod_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function contactmod_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function contactmod_get_extra_capabilities() {
    return array();
}


////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function contactmod_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for contactmod file areas
 *
 * @package mod_contactmod
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function contactmod_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the contactmod file areas
 *
 * @package mod_contactmod
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the contactmod's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function contactmod_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding contactmod nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the contactmod module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function contactmod_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the contactmod settings
 *
 * This function is called when the context for the page is a contactmod module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $contactmodnode {@link navigation_node}
 */
function contactmod_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $contactmodnode=null) {
}