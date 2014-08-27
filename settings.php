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
 * contactmod
 *
 * @package   contactmod
 * @copyright 2014 Tim Dyer
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

//see more at http://docs.moodle.org/en/Development:Admin_settings#Individual_settings
//various functions are defined in lib/adminlib.php, and docs can be found at
//http://phpdocs.moodle.org/19/moodlecore/_1_9_STABLE_moodle_lib_adminlib_php.html

if ($ADMIN->fulltree) {

	//select what roles to use as faculty
	$choices = Array();
	// get all the global roles
	$allroles = get_all_roles();
	foreach ($allroles as $role) {
		$choices[$role->id] = $role->name;
	}
	$default = Array();
	$default[3] = 1;	//3 = Teacher
	//and then allow each role to be selected for showing in the roster reports.
	$settings->add(new admin_setting_configmulticheckbox('contactmod/facultyroles', get_string('facultyroles', 'contactmod'),
			get_string('facultyrolesdescription', 'contactmod'), $default, $choices));

	
}