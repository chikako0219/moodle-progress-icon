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
 * Newblock block caps.
 *
 * @package	block_progress_icon
 * @copyright  2018 
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/blocks/progress_icon/lib.php');

if ($ADMIN->fulltree) {

	defined('MOODLE_INTERNAL') || die();

	// 画像リスト
	$image_options = array(
		'image001' => get_string('config_image001', 'block_progress_icon'),
		'image002' => get_string('config_image002', 'block_progress_icon'),
		'image003' => get_string('config_image003', 'block_progress_icon'),
		'image004' => get_string('config_image004', 'block_progress_icon'),
		'image005' => get_string('config_image005', 'block_progress_icon'),
		'image006' => get_string('config_image006', 'block_progress_icon'),
		'image007' => get_string('config_image007', 'block_progress_icon'),
		'image008' => get_string('config_image008', 'block_progress_icon'),
		'image009' => get_string('config_image009', 'block_progress_icon'),
		'image010' => get_string('config_image010', 'block_progress_icon'),
		'image011' => get_string('config_image011', 'block_progress_icon'),
		'image012' => get_string('config_image012', 'block_progress_icon'),
		'image013' => get_string('config_image013', 'block_progress_icon'),
		'image014' => get_string('config_image014', 'block_progress_icon'),
		'image015' => get_string('config_image015', 'block_progress_icon'),
		'image016' => get_string('config_image016', 'block_progress_icon'),
		'image017' => get_string('config_image017', 'block_progress_icon'),
		'image018' => get_string('config_image018', 'block_progress_icon'),
		'image019' => get_string('config_image019', 'block_progress_icon'),
		'image020' => get_string('config_image020', 'block_progress_icon'),
		'image021' => get_string('config_image021', 'block_progress_icon'),
		'image022' => get_string('config_image022', 'block_progress_icon'),
		'image023' => get_string('config_image023', 'block_progress_icon'),
		'image024' => get_string('config_image024', 'block_progress_icon'),
		'image025' => get_string('config_image025', 'block_progress_icon'),
		'image026' => get_string('config_image026', 'block_progress_icon'),
		'image027' => get_string('config_image027', 'block_progress_icon'),
		'image028' => get_string('config_image028', 'block_progress_icon'),
		'image029' => get_string('config_image029', 'block_progress_icon'),
		'image030' => get_string('config_image030', 'block_progress_icon'),
	);

	$progressbar_options = array(
		2 => get_string('config_display', 'block_progress_icon'),
		1 => get_string('config_hidden', 'block_progress_icon'),
	);

	// 表示するコース名
	$settings->add(new admin_setting_heading('block_progress_icon/common',
	               get_string('header_common', 'block_progress_icon'),
	               ''));
	$options = array(
	    'shortname' => get_string('shortname', 'block_progress_icon'),
	    'fullname' => get_string('fullname', 'block_progress_icon')
	);
	$settings->add(new admin_setting_configselect('block_progress_icon/coursenametoshow',
	    get_string('coursenametoshow', 'block_progress_icon'),
	    '',
	    DEFAULT_PROGRESSICON_COURSENAMETOSHOW,
	    $options)
	);

	// 画像の幅
	$settings->add(new admin_setting_configtext('block_progress_icon/imagewidth',
	    get_string('imagewidth', 'block_progress_icon'),
	    '',
	    DEFAULT_PROGRESSICON_IMAGEWIDTH,
	    PARAM_INT)
	);

	// 履修中
	$settings->add(new admin_setting_configselect('block_progress_icon/not_completed_image1',
		get_string('not_completed_image', 'block_progress_icon'),
		'',
		DEFAULT_PROGRESSICON_NOT_COMPLETED_IMAGE,
		$image_options)
	);

	$settings->add(new admin_setting_configselect('block_progress_icon/not_completed_progress_bar1',
		get_string('not_completed_progress_bar', 'block_progress_icon'),
		'',
		DEFAULT_PROGRESSICON_NOT_COMPLETED_PROGRESS_BAR,
		$progressbar_options)
	);

	// 履修ずみ
	$settings->add(new admin_setting_configselect('block_progress_icon/completed_image1',
		get_string('completed_image', 'block_progress_icon'),
		'',
		DEFAULT_PROGRESSICON_COMPLETED_IMAGE,
		$image_options)
	);

	$settings->add(new admin_setting_configselect('block_progress_icon/completed_progress_bar1',
		get_string('completed_progress_bar', 'block_progress_icon'),
		'',
		DEFAULT_PROGRESSICON_COMPLETED_PROGRESS_BAR,
		$progressbar_options)
	);
	
	if ( get_config('block_progress_icon', 'not_completed_image1') ) {

		// コース
		$sql = 'SELECT C.* FROM {course} C
		  WHERE C.id != 1
		   AND   C.startdate <= '.strtotime('now +1 month').'
		   AND ( C.enddate   >= '.strtotime('now').' OR C.enddate = 0 )
		  ORDER BY C.id ASC
		';

		$rows = $DB->get_records_sql($sql);

		foreach ($rows as $row) {
			$settings->add(new admin_setting_heading('block_progress_icon/course'.$row->id,
			               $row->fullname,
			               ''));
			// 履修中
			$title = get_string('not_completed_image', 'block_progress_icon');
			if (strpos($_SERVER["REQUEST_URI"], '/admin/upgradesettings.php') !== false) {
				$title = $row->fullname.'/'.$title;
			}
			$settings->add(new admin_setting_configselect('block_progress_icon/not_completed_image'.$row->id,
				$title,
				'',
				get_config('block_progress_icon', 'not_completed_image1') ?: DEFAULT_PROGRESSICON_NOT_COMPLETED_IMAGE,
				$image_options)
			);

			$title = get_string('not_completed_progress_bar', 'block_progress_icon');
			if (strpos($_SERVER["REQUEST_URI"], '/admin/upgradesettings.php') !== false) {
				$title = $row->fullname.'/'.$title;
			}
			$settings->add(new admin_setting_configselect('block_progress_icon/not_completed_progress_bar'.$row->id,
				$title,
				'',
				get_config('block_progress_icon', 'not_completed_progress_bar1') ?: DEFAULT_PROGRESSICON_NOT_COMPLETED_PROGRESS_BAR,
				$progressbar_options)
			);

			// 履修ずみ
			$title = get_string('completed_image', 'block_progress_icon');
			if (strpos($_SERVER["REQUEST_URI"], '/admin/upgradesettings.php') !== false) {
				$title = $row->fullname.'/'.$title;
			}

			// for test
			//$description = '';
			//$setting = new admin_setting_configstoredfile($name, $title, $description, 'completed_image'.$row->id.'_2');
			//$setting->set_updatedcallback('theme_reset_all_caches');
			//$settings->add($setting);

			$settings->add(new admin_setting_configselect('block_progress_icon/completed_image'.$row->id,
				$title,
				'',
				get_config('block_progress_icon', 'completed_image1') ?: DEFAULT_PROGRESSICON_COMPLETED_IMAGE,
				$image_options)
			);

			$title = get_string('completed_progress_bar', 'block_progress_icon');
			if (strpos($_SERVER["REQUEST_URI"], '/admin/upgradesettings.php') !== false) {
				$title = $row->fullname.'/'.$title;
			}
			$settings->add(new admin_setting_configselect('block_progress_icon/completed_progress_bar'.$row->id,
				$title,
				'',
				get_config('block_progress_icon', 'completed_progress_bar1') ?: DEFAULT_PROGRESSICON_COMPLETED_PROGRESS_BAR,
				$progressbar_options)
			);
		}
	}
}
