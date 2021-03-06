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
//
// This file is part of BasicLTI4Moodle
//
// BasicLTI4Moodle is an IMS BasicLTI (Basic Learning Tools for Interoperability)
// consumer for Moodle 1.9 and Moodle 2.0. BasicLTI is a IMS Standard that allows web
// based learning tools to be easily integrated in LMS as native ones. The IMS BasicLTI
// specification is part of the IMS standard Common Cartridge 1.1 Sakai and other main LMS
// are already supporting or going to support BasicLTI. This project Implements the consumer
// for Moodle. Moodle is a Free Open source Learning Management System by Martin Dougiamas.
// BasicLTI4Moodle is a project iniciated and leaded by Ludo(Marc Alier) and Jordi Piguillem
// at the GESSI research group at UPC.
// SimpleLTI consumer for Moodle is an implementation of the early specification of LTI
// by Charles Severance (Dr Chuck) htp://dr-chuck.com , developed by Jordi Piguillem in a
// Google Summer of Code 2008 project co-mentored by Charles Severance and Marc Alier.
//
// BasicLTI4Moodle is copyright 2009 by Marc Alier Forment, Jordi Piguillem and Nikolas Galanis
// of the Universitat Politecnica de Catalunya http://www.upc.edu
// Contact info: Marc Alier Forment granludo @ gmail.com or marc.alier @ upc.edu.

/**
 * This file defines the version of lti
 *
 * @package dailyreport
 * @copyright  2009 Marc Alier, Jordi Piguillem, Nikolas Galanis
 *  marc.alier@upc.edu
 * @copyright  2009 Universitat Politecnica de Catalunya http://www.upc.edu
 * @author     Marc Alier
 * @author     Jordi Piguillem
 * @author     Nikolas Galanis
 * @author     Chris Scribner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/dailyreport/lib.php');
require_once($CFG->dirroot . '/mod/dailyreport/locallib.php');
require_once($CFG->libdir.'/completionlib.php');
include('jdf.php');


global $COURSE, $USER, $DB;

$id       = required_param('id', PARAM_INT);    // Course Module ID
$action   = optional_param('action', '', PARAM_ALPHA); // Action
$d        = optional_param('d', 0, PARAM_INT);  // Page instance ID
$stdid    = optional_param('stdid', 0, PARAM_INT); // Student id to show report

$currentWeekNum = jdate("W", time(), '', "Asia/Tehran", en);
$weekNum  = optional_param('weeknum', $currentWeekNum, PARAM_INT); // WeekNumber which is gonna show
if ($d) {
    if (!$page = $DB->get_record('dailyreport', array('id'=>$d))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('dailyreport', $dailyreport->id, $dailyreport->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('dailyreport', $id)) {
        print_error('invalidcoursemodule');
    }
    $dailyreport = $DB->get_record('dailyreport', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

$PAGE->set_url('/mod/dailyreport/view.php', array('id' => $cm->id));
$PAGE->set_title('Dailyreport');

echo $OUTPUT->header();

$course = $DB->get_record('course', array('id' => $course->id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);
$roleId = $DB->get_record('role_assignments', ['contextid' => $context->id, 'userid' => $USER->id])->roleid;
$roleName = $DB->get_record('role', ['id' => $roleId])->archetype;

$dailyreport = new dailyreport();

echo $dailyreport->view($action, $roleName, $stdid, $context, $course->id, $id, $weekNum);
echo $OUTPUT->footer();