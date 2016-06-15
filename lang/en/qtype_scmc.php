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
$string['answerhowmany'] = 'One or multiple answers?';
$string['answernumbering'] = 'Number the choices?';
$string['answernumbering123'] = '1., 2., 3., ...';
$string['answernumberingabc'] = 'a., b., c., ...';
$string['answernumberingABCD'] = 'A., B., C., ...';
$string['answernumberingiii'] = 'i., ii., iii., ...';
$string['answernumberingIIII'] = 'I., II., III., ...';
$string['answernumberingnone'] = 'No numbering'; 
$string['configintro'] = 'Default values for SC/MC questions.';
$string['configscoringmethod'] = 'Default scoring method for SC/MC questions.';
$string['configshuffleoptions'] = 'Default setting for option shuffling in SC/MC questions.';
$string['enterfeedbackhere'] = 'Enter feedback here.';
$string['entergeneralfeedbackhere'] = 'Enter general feedback here.';
$string['enteroptionhere'] = '';
$string['enterstemhere'] = 'Enter the stem or question promt here.';
$string['false'] = 'False';
$string['feedbackforoption'] = 'Feedback for Option {$a}';
$string['generalfeedback'] = 'General Feedback.';
$string['generalfeedback_help'] = 'General feedback is shown to the student after they have completed the question. Unlike specific feedback, which depends on the question type and what response the student gave, the same general feedback text is shown to all students.<br />You can use the general feedback to give students a fully worked answer and perhaps a link to more information they can use if they did not understand the questions.';
$string['maxpoints'] = 'Max. points';
$string['mustsupplyresponses'] = 'You must supply values for all responses.';
$string['mustsupplyvalue'] = 'You must supply a value here.';
$string['optionno'] = 'Option {$a}';
$string['pluginname'] = 'SC/MC (ETH)';
$string['pluginname_help'] = 'In response to a question prompt the candidates choose one or multiple (correct) options. Two formats are available - Single Choice, where one option must be identified as the only correct or best answer, and Multiple-Choice, where all options must be judged according to the criteria provided, e.g. true/false.';
$string['pluginname_link'] = 'question/type/scmc';
$string['pluginnameadding'] = 'Adding a SC/MC question';
$string['pluginnameediting'] = 'Editing a SC/MC question';
$string['pluginnamesummary'] = 'Allows the selection of a single option or multiple options from a predefined list.';
$string['responsedesc'] = 'The text used as a default for response {$a}.';
$string['responseno'] = 'Response {$a}';
$string['responsetext'] = 'Response Text {$a}';
$string['responsetext1'] = 'True';
$string['responsetext2'] = 'False';
$string['responsetexts'] = 'Judgement options';
$string['save'] = 'Save';
$string['scoringscmconezero'] = 'MC1/0';
$string['scoringmethod'] = 'Scoring method';
$string['scoringmethod_help'] = 'If "one answer only" is selected the student is awarded points if the correct/best option is selected, and zero points otherwise.<br />If "multiple answers allowed" is selected there are two alternative scoring methods. <br /><strong>Subpoints</strong>(recommended) The student is awarded subpoints for each correct response.<br/><strong>MC1/0:</strong> The student receives full points if all responses are correct, and zero points otherwise.';
$string['scoringsubpoints'] = 'Subpoints';
$string['shuffleoptions'] = 'Shuffle options';
$string['shuffleoptions_help'] = 'If enabled, the order of the options is randomly shuffled for each attempt,
         provided that "Shuffle within questions" in the activity settings is also enabled.';
$string['stem'] = 'Stem';
$string['tasktitle'] = 'Task title';
$string['true'] = 'Correct';
$string['optionsandfeedback'] = 'Options and Feedback';
$string['correctresponse'] = 'Correct Response';
$string['incorrect'] = 'Incorrect';
$string['answersingleyes'] = 'One answer only';
$string['answersingleno'] = 'Multiple answers';
$string['numberofcolumns'] = 'One or multiple answers?';
$string['numberofrows'] = 'Number of options';
$string['numberofcolumns_help'] = '<strong>One answer only</strong>(recommended): use this setting for Single Choice items. One option must be identified as the only correct or best answer ("one best answer").<br /><strong>Multiple answers allowed</strong>: use this setting for Multiple Choice items. All options must be judged individually according to the criteria provided, e.g. true/false ("multiple true-false").';
$string['numberofrows_help'] = 'Specify the number of options.  When changing to fewer options, surplus options will be deleted once the item is saved.';
$string['deleterawswarning'] = 'Lowering the number of options will delete the last {$a} option(s). Are you sure you want to procceed?';
$string['onlysinglefeedback'] = 'Display Feedback only on selected option';
$string['onlysinglefeedbackhelp'] = 'Display Feedback only on selected option. This configuration applies only on Single Choice Type. If disabled, feedback will be displayed on all options.';
