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
 * Progress Icon block common configuration and helper functions
 *
 * @package    block_progress_icon
 * @copyright  2018
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/completionlib.php');

// Global defaults.
const DEFAULT_PROGRESSICON_COURSENAMETOSHOW = 'shortname';
const DEFAULT_PROGRESSICON_ACTIVITIESINCLUDED = 'activitycompletion';

const DEFAULT_PROGRESSICON_IMAGEWIDTH = 380;

const DEFAULT_PROGRESSICON_NOT_COMPLETED_IMAGE = 'image001';
const DEFAULT_PROGRESSICON_NOT_COMPLETED_PROGRESS_BAR = 2;
const DEFAULT_PROGRESSICON_COMPLETED_IMAGE = 'image002';
const DEFAULT_PROGRESSICON_COMPLETED_PROGRESS_BAR = 1;

/**
 * Returns the activities with completion set in current course
 *
 * @param int    courseid   ID of the course
 * @param int    config     The block instance configuration
 * @param string forceorder An override for the course order setting
 * @return array Activities with completion settings in the course
 */
function block_progress_icon_get_activities($courseid, $config = null, $forceorder = null) {
    global $DB;
    $modinfo = get_fast_modinfo($courseid, -1);
    $sections = $modinfo->get_sections();
    $activities = array();

    $course = $DB->get_record('course',array('id' => $courseid));
    $completion = new completion_info($course);
    $current = $completion->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY);
    
    foreach ($modinfo->instances as $module => $instances) {
        $modulename = get_string('pluginname', $module);
        foreach ($instances as $index => $cm) {
            foreach ( $current as $key => $value ) {
                if ( $cm->id == $value->moduleinstance ) {
                    $activities[] = array (
                        'type'       => $module,
                        'modulename' => $modulename,
                        'id'         => $cm->id,
                        'instance'   => $cm->instance,
                        'name'       => $cm->name,
                        'expected'   => $cm->completionexpected,
                        'section'    => $cm->sectionnum,
                        'position'   => array_search($cm->id, $sections[$cm->sectionnum]),
                        'url'        => method_exists($cm->url, 'out') ? $cm->url->out() : '',
                        'context'    => $cm->context,
                        'icon'       => $cm->get_icon_url(),
                        'available'  => $cm->available,
                    );
                }
            }
        }
    }

    return $activities;
}

/**
 * Used to compare two activities/resources based on order on course page
 *
 * @param array $a array of event information
 * @param array $b array of event information
 * @return <0, 0 or >0 depending on order of activities/resources on course page
 */
function block_progress_icon_compare_events($a, $b) {
    if ($a['section'] != $b['section']) {
        return $a['section'] - $b['section'];
    } else {
        return $a['position'] - $b['position'];
    }
}

/**
 * Used to compare two activities/resources based their expected completion times
 *
 * @param array $a array of event information
 * @param array $b array of event information
 * @return <0, 0 or >0 depending on time then order of activities/resources
 */
function block_progress_icon_compare_times($a, $b) {
    if (
        $a['expected'] != 0 &&
        $b['expected'] != 0 &&
        $a['expected'] != $b['expected']
    ) {
        return $a['expected'] - $b['expected'];
    } else if ($a['expected'] != 0 && $b['expected'] == 0) {
        return -1;
    } else if ($a['expected'] == 0 && $b['expected'] != 0) {
        return 1;
    } else {
        return block_progress_icon_compare_events($a, $b);
    }
}

/**
 * Filters activities that a user cannot see due to grouping constraints
 *
 * @param array  $activities The possible activities that can occur for modules
 * @param array  $userid The user's id
 * @param string $courseid the course for filtering visibility
 * @return array The array with restricted activities removed
 */
function block_progress_icon_filter_visibility($activities, $userid, $courseid) {
    global $CFG;
    $filteredactivities = array();
    $modinfo = get_fast_modinfo($courseid, $userid);
    $coursecontext = CONTEXT_COURSE::instance($courseid);

    // Keep only activities that are visible.
    foreach ($activities as $index => $activity) {

        $coursemodule = $modinfo->cms[$activity['id']];

        // Check visibility in course.
        if (!$coursemodule->visible && !has_capability('moodle/course:viewhiddenactivities', $coursecontext, $userid)) {
            continue;
        }

        // Check availability, allowing for visible, but not accessible items.
        if (!empty($CFG->enableavailability)) {
            if (has_capability('moodle/course:viewhiddenactivities', $coursecontext, $userid)) {
                $activity['available'] = true;
            } else {
                if (isset($coursemodule->available) && !$coursemodule->available && empty($coursemodule->availableinfo)) {
                    continue;
                }
                $activity['available'] = $coursemodule->available;
            }
        }

        // Check visibility by grouping constraints (includes capability check).
        if (!empty($CFG->enablegroupmembersonly)) {
            if (isset($coursemodule->uservisible)) {
                if ($coursemodule->uservisible != 1 && empty($coursemodule->availableinfo)) {
                    continue;
                }
            } else if (!groups_course_module_visible($coursemodule, $userid)) {
                continue;
            }
        }

        // Save the visible event.
        $filteredactivities[] = $activity;
    }
    return $filteredactivities;
}

/**
 * Checked if a user has completed an activity/resource
 *
 * @param array $activities  The activities with completion in the course
 * @param int   $userid      The user's id
 * @param int   $course      The course instance
 * @return array   an describing the user's attempts based on module+instance identifiers
 */
function block_progress_icon_completions($activities, $userid, $course) {
    $completions = array();
    $completion = new completion_info($course);
    $cm = new stdClass();

    if ( empty($activities) ) {
        if ($course->enddate == 0) {
            return False;
        } elseif ($course->enddate > strtotime('now')) {
            return False;
        } else {
            return True;
        }
    } else {
        foreach ($activities as $activity) {
            $cm->id = $activity['id'];
            $activitycompletion = $completion->get_data($cm, true, $userid);
            if ($activitycompletion->completionstate == 0 ) {
                return False;
            }
        }
    }

    return True;

}


/**
 * Checks whether the current page is the My home page.
 *
 * @return bool True when on the My home page.
 */
function block_progress_icon_on_site_page() {
    global $SCRIPT, $COURSE;

    return $SCRIPT === '/my/index.php' || $COURSE->id == 1;
}

