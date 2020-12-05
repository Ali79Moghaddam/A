<?php

require_once('../../config.php');
require_once($CFG->libdir.'/completionlib.php');
require_once('locallib.php');
require_once('jdf.php');

global $DB, $USER, $COURSE;
$state = $_GET['state'];
$state = json_decode($state, true);

//$weekNum = jdate('W', time(), none, 'Asia/Tehran', 'en');

$weekNum = (int)$state[weekNum];

$dailyreportId = $DB->get_record('modules', ['name' => 'dailyreport'])->id;
$activityId = $DB->get_record('course_modules', ['course' => $state[courseId], 'module' => $dailyreportId])->id;

$dayNum = 0;
foreach($state[days] as $day) {
    foreach($day as $records) {
        foreach($records as $record) {
            $data = new stdClass();
            $data->duty = dutyId($state, $record[duty]);
            $data->start = $record[startTime];
            $data->studyingtime = $record[studyingTime];
            $data->description = $record[description];
            $data->timemodified = time();
            $data->userid = $USER->id;
            $data->weekday = $dayNum;
            $data->weeknum = $weekNum; 
            $data->activityid = $activityId;
            if($record[deleted] == 1 && $record[recent] != 1) {
                try {
                    $data->id = (int)$record[id];
                    $DB->delete_records('dailyreport_record', ['id' => $data->id]);
                    echo "OK!";
                }catch(Exception $e) {
                    echo $e;
                }
            }else if($record[recent] == 1 && $record[deleted] != 1) {
                try {
                    $DB->insert_record_raw('dailyreport_record', $data);
                    print_r($data);
                }catch(Exception $e) {
                    echo $e;
                }
            }else {
                try {
                    $data->id = (int)$record[id];      
                    $DB->update_record('dailyreport_record', $data);
                }catch(Exception $e) {
                    echo $e;
                }
            }
        }
    }
    $dayNum++;
}

if($state[description]) {
    $descriptionId = $DB->get_record('dailyreport_record', ['userid' => $USER->id, 'weeknum' => $weekNum, 'activityid' => $activityId, 'weekday' => 7])->id;
    if($descriptionId) {
        $data->id = $descriptionId;
        $data->description = $state[description];
        $data->weekday = 7;
        $DB->update_record('dailyreport_record', $data);  
    }else {
        try {
            $data->description = $state[description];
            $data->weekday = 7;
            $DB->insert_record_raw('dailyreport_record', $data);
        }catch(Exception $e) {
            echo $e;
        }
    }
}

function dutyId($state, $selectedDuty) {
    $i = 0;
    foreach($state[duties] as $duty) {
        if($duty == $selectedDuty) return ($i+1);
        $i++;
    }
    return 0;  
}