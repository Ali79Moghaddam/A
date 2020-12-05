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

defined('MOODLE_INTERNAL') || die;

require_once('../../config.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

include('styles.php');

class dailyreport {

    /* @param string $action The current action if any.
    * @param array $args Optional arguments to pass to the view (instead of getting them from GET and POST).
    * @return string - The page output.
    */


    public function view($action='', $roleName='student', $stdid, $context, $courseId, $id, $weekNum, $args = array()) {
        
        global $DB, $OUTPUT;
        
        $dailyreportId = $DB->get_record('modules', ['name' => 'dailyreport'])->id;
        $activityId = $DB->get_record('course_modules', ['course' => $courseId, 'module' => $dailyreportId])->id;
        if($roleName == "teacher" || $roleName == "manager") {
            if($action == '') 
                $this->teacher_page($context, $id);
            else if($action == "report") 
                $this->student_report($stdid, $weekNum, $courseId, $activityId);
        }else if($roleName == "student") 
            $this->student_page($context, $courseId, $id, $weekNum, $activityId);
    }

    private function teacher_page($context, $id) {
        global $DB, $OUTPUT;

        $studenstsId = $DB->get_records('role_assignments', ['contextid' => $context->id]);
        echo "<div class='students_table'>";
        echo "<table>";
        echo "<tr>";
        echo "<td class='table_report'>View report</td>";
        echo "<td class='table_name'>Name</td>";
        echo "<td class='table_profile_pic'>Profile image</td>";
        echo "</tr>";
        foreach($studenstsId as $student) {
            $user = $DB->get_record('user', ['id' => $student->userid]);
            $studentName = $user->firstname . " " . $user->lastname;
            $roleId = $DB->get_record('role_assignments', ['contextid' => $context->id, 'userid' => $student->userid])->roleid;
            $roleName = $DB->get_record('role', ['id' => $roleId])->archetype;
            if($roleName == "teacher" || $roleName == "manager") continue;
            echo "<tr>";
            echo "<td><a href='/mod/dailyreport/view.php?id=" . strval($id) . "&action=report&stdid=" . strval($user->id) . "'>View report</a></td>";
            echo "<td>" . $studentName . "</td>";
            echo "<td>" . $OUTPUT->user_picture($user, array('size'=>50)) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }

    private function student_report($stdid, $weekNum, $courseId, $activityId) {
        global $DB, $USER;
        echo "<center><a href='/mod/dailyreport/view.php?id=" . strval($activityId) . "&action=report&stdid=" . strval($stdid) . "&weeknum=" . strval($weekNum-1) . "'><- هفته قبل</a>";
        echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
        echo "<a href='/mod/dailyreport/view.php?id=" . strval($activityId) . "&action=report&stdid=" . strval($stdid) . "&weeknum=" . strval($weekNum+1) . "'>هفته بعد -></a></center>";
        $stdReports = $DB->get_records('dailyreport_record', ['userid' => (int)$stdid, 'weeknum' => (int)$weekNum, 'activityid' => $activityId]);
        echo "<div>";
        echo "<table class='reports_table'>";
        echo "<tr>";
        echo "<th>توضیحات</th>";
        echo "<th>مدت زمان</th>";
        echo "<th>زمان شروع</th>";
        echo "<th>درس</th>";
        echo "<th>روز</th>";
        echo "</tr>";
        $num_of_reports = $this->num_of_reports_on_each_day($stdReports);
        $duties = json_decode($this->makeDuties());
        $dutyList = array();
        foreach($duties as $duty) {
            array_push($dutyList, $duty->duty);
        }
        for($i = 0; $i < 7; $i++) {
            $flag = false;
            foreach($stdReports as $report) {
                if($report->weekday == $i) {
                    if($flag == false) {
                        echo "<tr>";
                        echo "<td>" . $report->description . "</td>";
                        echo "<td>" . $report->studyingtime . "</td>";
                        echo "<td>" . $report->start . "</td>";
                        echo "<td>" . $dutyList[(int)$report->duty-1] . "</td>";
                        echo "<th rowspan=" . strval($num_of_reports[$i]) . ">" . $this->day_name($i) . "</th>" ;
                        
                        echo "</tr>";
                        $flag = true;
                    } else {
                        echo "<tr>";
                        echo "<td>" . $report->description . "</td>";
                        echo "<td>" . $report->studyingtime . "</td>";
                        echo "<td>" . $report->start . "</td>";
                        echo "<td>" . $dutyList[(int)$report->duty-1] . "</td>";
                        
                        echo "</tr>";
                    }
                }
            }
        }
        echo "</table>";
        echo "</div><br>";
        $description = $DB->get_record('dailyreport_record', ['userid' => $stdid, 'weeknum' => $weekNum, 'activityid' => $activityId, 'weekday' => 7])->description;
        echo "<center>توضیحات :</center>";
        echo "<center><textarea id='' cols='100' rows='5' disabled>" . $description . "</textarea></center><br>";
        echo "<form method='POST'>";
        $feedback = $DB->get_record('dailyreport_feedback', ['userid' => $USER->id, 'stdid' => $stdid, 'activityid' => $activityId, 'weeknum' => $weekNum])->feedback;
        echo "<center>بازخورد :</center>";
        echo "<center><textarea id='feedback' name='feedback' cols='100' rows='5' placeholder='Feedback'>" . $feedback . "</textarea></center><br>";
        echo "<input type='text' value=" . strval($stdid) . " name='stdid' hidden />";
        echo "<input type='text' value=" . strval($weekNum) . " name='weeknum' hidden />";
        echo "<input type='text' value=" . strval($activityId) . " name='activityid' hidden />";
        echo "<center><input type='submit' name='send_feedback' value='Send' /></center>";
        echo "</form>";
    }

    private function num_of_reports_on_each_day($stdReports) {
        $num_of_reports = array(0,0,0,0,0,0,0);
        foreach($stdReports as $report) {
            $num_of_reports[$report->weekday]++;
        }
        return $num_of_reports;
    }

    private function day_name($daynum) {
        switch($daynum) {
            case 0: return 'شنبه';
            case 1: return "یک شنبه";
            case 2: return "دو شنبه";
            case 3: return "سه شنبه";
            case 4: return "چهار شنبه";
            case 5: return "پنج شنبه";
            case 6: return "جمعه";
        }
    }

    private function student_page($context, $courseId, $id, $weekNum, $activityId) {
        global $DB, $USER;
        echo "<center><a href='/mod/dailyreport/view.php?id=" . strval($activityId) . "&action=report&stdid=" . strval($stdid) . "&weeknum=" . strval($weekNum-1) . "'><- هفته قبل</a>";
        echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
        echo "<a href='/mod/dailyreport/view.php?id=" . strval($activityId) . "&action=report&stdid=" . strval($stdid) . "&weeknum=" . strval($weekNum+1) . "'>هفته بعد -></a></center>";
        echo "<div class='dailyreport-form' id='dailyreport-form'></div>";
        echo "<script src='https://code.jquery.com/jquery-2.2.4.min.js'></script>";
        echo "<script src='index.js'></script>";
        echo "<script>initialState(" . $courseId . "," . $weekNum . "," . $this->makeRecords($courseId, $activityId, $weekNum) . "," . $this->makeDuties() . "," . $this->makeDescription($weekNum, $activityId) . ")</script>";
        echo "<script>render()</script>";
        echo "</div>";
        echo "<center><button type='button' onclick='sendData()'>Submit</button></center><br>";
        $feedback = $DB->get_record('dailyreport_feedback', ['stdid' => (int)$USER->id, 'activityid' => (int)$activityId, 'weeknum' => (int)$weekNum])->feedback;
        echo "<center>Feedback :<center>";
        echo "<center><textarea id='feedback' cols='100' rows='5' disabled>" . $feedback . "</textarea><center>";
    }

    private function makeRecords($courseId, $activityId, $weekNum) {
        global $DB, $USER;
        return json_encode($DB->get_records('dailyreport_record', ['userid' => $USER->id, 'activityid' => $activityId, 'weeknum' => $weekNum]));
    }

    private function makeDuties() {
        global $DB;
        return json_encode($DB->get_records('dailyreport_duty'));
    }

    private function makeDescription($weekNum, $activityId) {
        global $DB, $USER;
        $description = $DB->get_record('dailyreport_record', ['userid' => (int)$USER->id, 'weeknum' => (int)$weekNum, 'activityid' => (int)$activityId, 'weekday' => 7])->description;
        return json_encode($description);
    }
}

if(isset($_POST['send_feedback'])) {
    global $USER;
    
    $data = new stdClass();
    $data->userid = (int)$USER->id;
    $data->stdid = (int)$_POST['stdid'];
    $data->activityid = (int)$_POST['activityid'];
    $data->feedback = $_POST['feedback'];
    $data->weeknum = (int)$_POST['weeknum'];
    $data->timemodified = time();
    try{
        $id = $DB->get_record('dailyreport_feedback', ['userid' => $data->userid, 'stdid' => $data->stdid, 'activityid' => $data->activityid, 'weeknum' => $data->weeknum])->id;
        if($id) {
            $data->id = $id;
            $DB->update_record('dailyreport_feedback', $data);
            echo "<script>console.log(" . json_encode($data) . ")</script>";
        }else 
            $DB->insert_record_raw('dailyreport_feedback', $data);
        $_POST['send_feedback'] = null;
    }catch(Execption $e) {
        print_r($e);
    }
}