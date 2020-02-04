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
 * Progress Icon block definition
 *
 * @package    block_progress_icon
 * @copyright  2018
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/progress_icon/lib.php');
require_once($CFG->dirroot.'/blocks/completion_progress/lib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Completion Progress block class
 *
 * @copyright 2016 Michael de Raadt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_progress_icon extends block_base {

	/**
	 * Sets the block title
	 *
	 * @return void
	 */
	public function init() {
		$this->title = get_string('pluginname', 'block_progress_icon');
	}

	/**
	 *  we have global config/settings data
	 *
	 * @return bool
	 */
	public function has_config() {
		return true;
	}

	/**
	 * Controls the block title based on instance configuration
	 *
	 * @return bool
	 */
	public function specialization() {
		if (isset($this->config->progressTitle) && trim($this->config->progressTitle) != '') {
			$this->title = format_string($this->config->progressTitle);
		}
	}

	/**
	 * Controls whether multiple instances of the block are allowed on a page
	 *
	 * @return bool
	 */
	public function instance_allow_multiple() {
		return !block_progress_icon_on_site_page();
	}

	/**
	 * Controls whether the block is configurable
	 *
	 * @return bool
	 */
	public function instance_allow_config() {
		return !block_progress_icon_on_site_page();
	}

	/**
	 * Defines where the block can be added
	 *
	 * @return array
	 */
	public function applicable_formats() {
		return array(
		    'course-view'    => false,
		    'site'           => false,
		    'mod'            => false,
		    'my'             => true
		);
	}

	/**
	 * Creates the blocks main content
	 *
	 * @return string
	 */
	public function get_content() {
		global $USER, $COURSE, $CFG, $OUTPUT, $DB;

		// If content has already been generated, don't waste time generating it again.
		if ($this->content !== null) {
			return $this->content;
		}
		$this->content = new stdClass;
		$this->content->text = '';
		$this->content->footer = '';
		$blockinstancesonpage = array();

		// Guests do not have any progress. Don't show them the block.
		if (!isloggedin() or isguestuser()) {
			return $this->content;
		}

		// Draw the multi-bar content for the Dashboard and Front page.
		if (block_progress_icon_on_site_page()) {

			if (!$CFG->enablecompletion) {
				$this->content->text .= get_string('completion_not_enabled', 'block_progress_icon');
				return $this->content;
			}

			// Show a message when the user is not enrolled in any courses.
			$courses = enrol_get_my_courses();
			if (($this->page->user_is_editing() || is_siteadmin()) && empty($courses)) {
				$this->content->text = get_string('no_courses', 'block_progress_icon');
				return $this->content;
			}

			$coursenametoshow = get_config('block_progress_icon', 'coursenametoshow') ?:
				DEFAULT_PROGRESSICON_COURSENAMETOSHOW;

			foreach ($courses as $courseid => $course) {
				$course = $DB->get_record('course',array('id' => $courseid));

				// Get specific block config and context.
				$completion = new completion_info($course);
				if ($course->visible && $completion->is_enabled()) {

					$this->content->text .="<div style='width:380px;min-width:380px;height:300px;min-hight:300px;float:left;padding:2em 20px;'>";
					// コース名を表示
					$courselink = new moodle_url('/course/view.php', array('id' => $course->id));
					$linktext = HTML_WRITER::tag('h3', s($course->$coursenametoshow));
					$this->content->text .= HTML_WRITER::link($courselink, $linktext);

					// 完了判定
					$completions = true;
					$activities = block_progress_icon_get_activities($course->id);
					$activities = block_progress_icon_filter_visibility($activities, $USER->id, $course->id);
					if (!block_progress_icon_completions($activities, $USER->id, $course)) {
						$completions = false;
					}


					$show_completion_progress = true;
					$show_image = DEFAULT_PROGRESSICON_NOT_COMPLETED_IMAGE;

					if ( empty($activities) ) {
						$show_completion_progress = false;
					} else {
						if ($completions) {
							$show_completion_progress = (
								get_config('block_progress_icon', 'completed_progress_bar'.$course->id)
									?: get_config('block_progress_icon', 'completed_progress_bar1')
										 ?: DEFAULT_PROGRESSICON_COMPLETED_PROGRESS_BAR
							);

							$show_image = (
								get_config('block_progress_icon', 'completed_image'.$course->id)
									?: get_config('block_progress_icon', 'completed_image1')
										 ?: DEFAULT_PROGRESSICON_COMPLETED_IMAGE
							);
						} else {

							$show_completion_progress = (
								get_config('block_progress_icon', 'not_completed_progress_bar'.$course->id)
									?: get_config('block_progress_icon', 'not_completed_progress_bar2')
										?: DEFAULT_PROGRESSICON_NOT_COMPLETED_PROGRESS_BAR
							);

							$show_image = (
								get_config('block_progress_icon', 'not_completed_image'.$course->id)
									?: get_config('block_progress_icon', 'not_completed_image2')
										?: DEFAULT_PROGRESSICON_NOT_COMPLETED_IMAGE
							);
						}
					}

					$summary = strip_tags($course->summary);
					$linktext = '<img src="'.$OUTPUT->image_url($show_image, 'block_progress_icon').'" width="100%" title="'.$summary.'" class="progress_image">';
					$courselink = new moodle_url('/course/view.php', array('id' => $course->id));
					$this->content->text .= HTML_WRITER::link($courselink, $linktext);

					// プログレスバーの表示
					if ($show_completion_progress == 2) {
						$cmp_submissions = block_completion_progress_student_submissions($course->id, $USER->id);
						$cmp_completions = block_completion_progress_completions($activities, $USER->id, $course,
						    $cmp_submissions);
						$this->content->text .= block_completion_progress_bar($activities,
						                                            $cmp_completions,
						                                            null,
						                                            $USER->id,
						                                            $course->id,
						                                            $course->id);
						$blockinstancesonpage = array_merge($blockinstancesonpage, array($course->id));
					}
					$this->content->text .="</div>";
				}
			}

		}
		$this->content->text .="<br style='clear:both;' />";

		// Organise access to JS.
		$jsmodule = array(
			'name' => 'block_completion_progress',
			'fullpath' => '/blocks/completion_progress/module.js',
			'requires' => array(),
			'strings' => array(),
		);

		$arguments = array($blockinstancesonpage, array($USER->id));
		$this->page->requires->js_init_call('M.block_completion_progress.setupScrolling', array(), false, $jsmodule);
		$this->page->requires->js_init_call('M.block_completion_progress.init', $arguments, false, $jsmodule);

		return $this->content;
	}

}
