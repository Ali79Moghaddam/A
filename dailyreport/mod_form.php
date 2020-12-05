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
 * Page configuration form
 *
 * @package mod_page
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/page/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_dailyreport_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB;
        
        global $COURSE, $USER;

        $mform = $this->_form;

        $config = get_config('dailyreport');

        echo "----------------------" . $COURSE->id;
        //-------------------------------------------------------

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'title', "عنوان", array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('title', PARAM_TEXT);
        } else {
            $mform->setType('title', PARAM_CLEANHTML);
        }
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        //-------------------------------------------------------
        
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();

        //-------------------------------------------------------
        // $mform->addElement('hidden', 'revision');
        // $mform->setType('revision', PARAM_INT);
        // $mform->setDefault('revision', 1);
    }

    /**
     * Enforce defaults here.
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/

}

