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
 * @see uninstall_plugin()
 *
 * @package    mod_contactmod
 * @copyright  Tim Dyer 2014
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom uninstallation procedure
 */
function xmldb_contactmod_uninstall() {
	global $DB;
	
	// $catid = category id in user_info_category for our custom fields
	$catid = 0;
	if( $q = $DB->get_recordset( 'contactmod_catid', array( "id" => 1 ) ) ) {
		foreach( $q as $qq ) $catid = $qq->catid;
		$q->close();
	}
	
	// $fids is an array of field ids for each field under our category
	$fids = array();
	if( $q = $DB->get_recordset( 'user_info_field', array( "categoryid" => $catid ) ) ) {
		foreach( $q as $qq ) $fids[] = $qq->id;
		$q->close();
	}
	
	// remove data from user_info_data pertaining to $fids array
	foreach( $fids as $fid )
		$DB->delete_records( 'user_info_data', array( 'fieldid' => $fid ) );
	
	// remove actual fields using $fids arrays
	foreach( $fids as $fid )
		$DB->delete_records( 'user_info_field', array( 'id' => $fid ) );
	
	// remove actual category $catid
	$DB->delete_records( 'user_info_category', array( 'id' => $catid ) );			
			
	return true;
}
