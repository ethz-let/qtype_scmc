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
 * @package qtype_scmc
 * @author Amr Hourani amr.hourani@id.ethz.ch
 * @copyright ETHz 2016 amr.hourani@id.ethz.ch
 */
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/question/type/scmc/lib.php');

    // Introductory explanation that all the settings are defaults for the edit_scmc_form.
    $settings->add(
            new admin_setting_heading('configintro', '', get_string('configintro', 'qtype_scmc')));
    // Scoring methods.
    $options = array(
        'scmconezero' => get_string('scoringscmconezero', 'qtype_scmc'),
        'subpoints' => get_string('scoringsubpoints', 'qtype_scmc')
    );

    $settings->add(
            new admin_setting_configselect('qtype_scmc/scoringmethod',
                    get_string('configscoringmethod', 'qtype_scmc'), get_string('scoringmethod_help', 'qtype_scmc'), 'subpoints', $options));

    // Shuffle options.
    $settings->add(
            new admin_setting_configcheckbox('qtype_scmc/shuffleoptions',
                    get_string('shuffleoptions', 'qtype_scmc'),
                    get_string('shuffleoptions_help', 'qtype_scmc'), 1));
    // Display full feedback for single choice or only selection feedback.
	$options = array(
        '1' => get_string('yes'),
        '0' => get_string('no')
    );
    $settings->add(
            new admin_setting_configselect('qtype_scmc/only_single_feedback',
                    get_string('onlysinglefeedback', 'qtype_scmc'),
                    get_string('onlysinglefeedbackhelp', 'qtype_scmc'), 1, $options));	
}
