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
 * Progress Icon block version details
 *
 * @package    block_progress_icon
 * @copyright  2018 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2018021000;
$plugin->requires  = 2017051500; // Moodle 3.3
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = 'Version for Moodle 3.3 onwards';
$plugin->component = 'block_progress_icon';

$plugin->dependencies = array('block_completion_progress' => 2017050500);
