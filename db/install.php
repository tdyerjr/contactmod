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
 * This file replaces the legacy STATEMENTS section in db/install.xml,
 * lib.php/modulename_install() post installation hook and partially defaults.php
 *
 * @package    mod_contactmod
 * @copyright  2014 Tim Dyer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post installation procedure
 *
 * @see upgrade_plugins_modules()
 */
 
defined('MOODLE_INTERNAL') || die();

 
function xmldb_contactmod_install() {
	global $DB;
	
	// $category is the new entry in user_info_category ( name, sortorder )
	$category = new stdClass();
	$category->name = get_string('categoryname','contactmod');
	$category->sortorder = 1;
	
	// $catid will be the auto-incremented id of the category inserted above
	$catid = $DB->insert_record('user_info_category',$category);
	
	// insert $catid into our contactmod_catid table ( catid is only field )
	$cat = new stdClass();
	$cat->catid = $catid;
	$DB->insert_record('contactmod_catid', $cat);
	 
	// create a new field for Office Hours and insert into user_info_field
	$field = new stdClass();
	$field->shortname = 'officehoursmod';
	$field->name = get_string('officehoursname','contactmod');
	$field->datatype = 'text';
	$field->description = get_string('officehoursdescr','contactmod');
	$field->descriptionformat = 1;
	$field->categoryid = $catid;
	$field->sortorder = 1;
	$field->required = 1;
	$field->locked = 1;
	$field->visible = 1;
	$field->forceunique = 0;
	$field->signup = 0;
	$field->defaultdataformat = 0;
	$DB->insert_record( 'user_info_field', $field );	
	
	return true;
}

/**
 * Post installation recovery procedure
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_contactmod_install_recovery() {
}
