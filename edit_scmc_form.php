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
 *
 * @package qtype_scmc
 * @author Amr Hourani amr.hourani@id.ethz.ch
 * @copyright ETHz 2016 amr.hourani@id.ethz.ch
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/scmc/lib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');


/**
 * Kprime editing form definition.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_scmc_edit_form extends question_edit_form {

    private $numberofrows;

    private $numberofcolumns;

    /**
     * (non-PHPdoc).
     *
     * @see myquestion_edit_form::qtype()
     */
    public function qtype() {
        return 'scmc';
    }

    /**
     * Build the form definition.
     *
     * This adds all the form fields that the default question type supports.
     * If your question type does not support all these fields, then you can
     * override this method and remove the ones you don't want with $mform->removeElement().
     */
    protected function definition() {
        global $COURSE, $CFG, $DB;

        $qtype = $this->qtype();
        $langfile = "qtype_$qtype";

        $mform = $this->_form;

        // Standard fields at the start of the form.
        $mform->addElement('header', 'categoryheader', get_string('category', 'question'));

        // Standard fields at the start of the form.
        $mform->addElement('header', 'generalheader', get_string("general", 'form'));

        if (!isset($this->question->id)) {
            if (!empty($this->question->formoptions->mustbeusable)) {
                $contexts = $this->contexts->having_add_and_use();
            } else {
                $contexts = $this->contexts->having_cap('moodle/question:add');
            }

            // Adding question.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'), array('contexts' => $contexts));
        } else if (!($this->question->formoptions->canmove || $this->question->formoptions->cansaveasnew)) {
            // Editing question with no permission to move from category.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                            array('contexts' => array($this->categorycontext)));
            $mform->addElement('hidden', 'usecurrentcat', 1);
            $mform->setType('usecurrentcat', PARAM_BOOL);
            $mform->setConstant('usecurrentcat', 1);
        } else {
            // Editing question with permission to move from category or save as new q.
            $currentgrp = array();
            $currentgrp[0] = $mform->createElement('questioncategory', 'category', get_string('categorycurrent', 'question'),
                                                array('contexts' => array($this->categorycontext)));
            // Validate if the question is being duplicated.
            $beingcopied = false;
            if (isset($this->question->beingcopied)) {
                $beingcopied = $this->question->beingcopied;
            }
            if (($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) && ($beingcopied)) {
                // Not move only form.
                $currentgrp[1] = $mform->createElement('checkbox', 'usecurrentcat', '',
                                                    get_string('categorycurrentuse', 'question'));
                $mform->setDefault('usecurrentcat', 1);
            }
            $currentgrp[0]->freeze();
            $currentgrp[0]->setPersistantFreeze(false);
            $mform->addGroup($currentgrp, 'currentgrp', get_string('categorycurrent', 'question'), null, false);

            if (($beingcopied)) {
                $mform->addElement('questioncategory', 'categorymoveto', get_string('categorymoveto', 'question'),
                                array('contexts' => array($this->categorycontext)));
                if ($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) {
                    // Not move only form.
                    $mform->disabledIf('categorymoveto', 'usecurrentcat', 'checked');
                }
            }
        }

        if (class_exists('qbank_editquestion\\editquestion_helper') && !empty($this->question->id) && !$this->question->beingcopied) {
            // Add extra information from plugins when editing a question (e.g.: Authors, version control and usage).
            $functionname = 'edit_form_display';
            $questiondata = [];
            $plugins = get_plugin_list_with_function('qbank', $functionname);
            foreach ($plugins as $componentname => $plugin) {
                $element = new StdClass();
                $element->pluginhtml = component_callback($componentname, $functionname, [$this->question]);
                $questiondata['editelements'][] = $element;
            }
            $mform->addElement('static', 'versioninfo', get_string('versioninfo', 'qbank_editquestion'),
                            $PAGE->get_renderer('qbank_editquestion')->render_question_info($questiondata));
        }
        $mform->addElement('text', 'name', get_string('tasktitle', 'qtype_scmc'),
                array('size' => 50, 'maxlength' => 255
                ));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'defaultmark', get_string('maxpoints', 'qtype_scmc'),
                array('size' => 7
                ));
        $mform->setType('defaultmark', PARAM_FLOAT);
        $mform->setDefault('defaultmark', 1);
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        $mform->addElement('editor', 'questiontext', get_string('stem', 'qtype_scmc'),
                array('rows' => 15
                ), $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');
        $mform->setDefault('questiontext',
                array('text' => get_string('enterstemhere', 'qtype_scmc')
                ));
        if (class_exists('qbank_editquestion\\editquestion_helper')) {
            $mform->addElement('select', 'status', get_string('status', 'qbank_editquestion'),
                            \qbank_editquestion\editquestion_helper::get_question_status_list());
        }
        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'),
                array('rows' => 10
                ), $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'qtype_scmc');
        $mform->addElement('select', 'answernumbering', get_string('answernumbering', 'qtype_scmc'),
                qtype_scmc::get_numbering_styles());
        if (!empty($this->question->options->answernumbering)) {
            $mform->setDefault('answernumbering', array($this->question->options->answernumbering
            ));
        }

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'question'), 'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumber', 'question');
        $mform->setType('idnumber', PARAM_RAW);

        // Any questiontype specific fields.
        $this->definition_inner($mform);
        $this->add_interactive_settings();

        if (core_tag_tag::is_enabled('core_question', 'question') &&
             \core\plugininfo\qbank::is_plugin_enabled('qbank_tagquestion')) {
            $this->add_tag_fields($mform);
        }

        if (!empty($this->customfieldpluginenabled) && $this->customfieldpluginenabled) {
            // Add custom fields to the form.
            $this->customfieldhandler = qbank_customfields\customfield\question_handler::create();
            $this->customfieldhandler->set_parent_context($this->categorycontext); // For question handler only.
            $this->customfieldhandler->instance_form_definition($mform, empty($this->question->id) ? 0 : $this->question->id);
        }

        $this->add_hidden_fields();

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);

        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'updatebutton', get_string('savechangesandcontinueediting', 'question'));
        if ($this->can_preview()) {
            if (class_exists('qbank_editquestion\\editquestion_helper')) {
                if (\core\plugininfo\qbank::is_plugin_enabled('qbank_previewquestion')) {
                    $previewlink = $PAGE->get_renderer('qbank_previewquestion')->question_preview_link($this->question->id,
                                                                                                    $this->context, true);
                    $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
                }
            } else {
                $previewlink = $PAGE->get_renderer('core_question')->question_preview_link($this->question->id, $this->context, true);
                $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
            }
        }

        $mform->addGroup($buttonarray, 'updatebuttonar', '', array(' '), false);
        $mform->closeHeaderBefore('updatebuttonar');

        $this->add_action_buttons(true, get_string('savechanges'));

        if ((!empty($this->question->id)) && (!($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew))) {
            $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar', 'currentgrp'));
        }
    }

    /**
     * Adds question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {
        $scmcconfig = get_config('qtype_scmc');

        if (isset($this->question->options->rows) && count($this->question->options->rows) > 0) {
            $this->numberofrows = count($this->question->options->rows);
        } else {
            $this->numberofrows = 3;
        }
        // LMDL-141.
        $lastnumberofrows = $this->numberofrows;
        if ($this->numberofrows > 5) { // Reset to the max. i.e 5.
            $this->numberofrows = 5;
        }
        if (isset($this->question->options->columns) && count($this->question->options->columns) > 0) {
            $this->numberofcolumns = count($this->question->options->columns);
        } else {
            $this->numberofcolumns = 1;
        }
        $this->editoroptions['changeformat'] = 1;
        $menu = array(1 => get_string('answersingleyes', 'qtype_scmc'),
            2 => get_string('answersingleno', 'qtype_scmc')
        );
        $mform->addElement('select', 'numberofcolumns', get_string('numberofcolumns', 'qtype_scmc'),
                $menu);
        $mform->setDefault('numberofcolumns', 1);
        $mform->addHelpButton('numberofcolumns', 'numberofcolumns', 'qtype_scmc');

        $numberoptionsmenu = array(2 => 2, 3 => 3, 4 => 4, 5 => 5
        );

        $numoptionsdisabled = array();
        $mform->addElement('select', 'numberofrows', get_string('numberofrows', 'qtype_scmc'),
                $numberoptionsmenu, $numoptionsdisabled);
        $mform->setDefault('numberofrows', 3);
        $mform->addHelpButton('numberofrows', 'numberofrows', 'qtype_scmc');

        $mform->addElement('header', 'scoringmethodheader',
                get_string('scoringmethod', 'qtype_scmc'));
        // Add the scoring method radio buttons.
        $attributes = array();
        $scoringbuttons = array();
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '',
                get_string('scoringsubpoints', 'qtype_scmc'), 'subpoints', $attributes);
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '',
                get_string('scoringscmconezero', 'qtype_scmc'), 'scmconezero', $attributes);
        $mform->addGroup($scoringbuttons, 'radiogroupscoring',
                get_string('scoringmethod', 'qtype_scmc'), array(' <br/> '
                ), false);
        $mform->addHelpButton('radiogroupscoring', 'scoringmethod', 'qtype_scmc');
        $mform->setDefault('scoringmethod', 'subpoints');

        // Add the shuffleanswers checkbox.
        $mform->addElement('advcheckbox', 'shuffleanswers',
                get_string('shuffleanswers', 'qtype_scmc'), null, null, array(0, 1
                ));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_scmc');

        $mform->addElement('header', 'optionsandfeedbackheader',
                get_string('optionsandfeedback', 'qtype_scmc'));

        // Add the response text fields.
        $mform->addElement('html', '<span id="judgmentoptionsspan">');
        $responses = array();
        for ($i = 1; $i <= 2; ++$i) {
            $label = '';
            if ($i == 1) {
                $label = get_string('responsetexts', 'qtype_scmc');
            }
            $mform->addElement('text', 'responsetext_' . $i, $label, array('size' => 6
            ));
            $mform->setType('responsetext_' . $i, PARAM_TEXT);
            $mform->addRule('responsetext_' . $i, null, 'required', null, 'client');

            $mform->setDefault('responsetext_' . $i, get_string('responsetext' . $i, 'qtype_scmc'));
        }
        $mform->addElement('html', '</span>');
        $responsetexts = array();
        if (isset($this->question->options->columns) && !empty($this->question->options->columns)) {
            foreach ($this->question->options->columns as $key => $column) {
                $responsetexts[] = format_text($column->responsetext, FORMAT_HTML);
            }
            // What if only one col? have the max just in case.
            if (count($responsetexts)) {
                for ($i = count($this->question->options->columns) + 1; $i <=
                         QTYPE_SCMC_NUMBER_OF_RESPONSES; $i++) {
                    // Always default it to second options values...
                    $responsetexts[] = get_string('responsetext2', 'qtype_scmc');
                }
            }
        } else {
            $responsetexts[] = get_string('responsetext1', 'qtype_scmc');
            $responsetexts[] = get_string('responsetext2', 'qtype_scmc');
        }

        // Add an option text editor, response radio buttons and a feedback editor for each option.
        for ($i = 1; $i <= 5; ++$i) {

            // Add the option editor.
            $mform->addElement('html', '<div class="optionbox" id="optionbox_response_' . $i . '">');
            // Open div.optionbox.
            $mform->addElement('html', '<div class="optionandresponses">'); // Open div.optionbox.

            $mform->addElement('html', '<div class="optiontext">'); // Open div.optiontext.
            $mform->addElement('html',
                    '<label class="optiontitle">' . get_string('optionno', 'qtype_scmc', $i) .
                             '</label>');
            $mform->addElement('editor', 'option_' . $i, '', array('rows' => 8
            ), $this->editoroptions);
            $mform->setDefault('option_' . $i,
                    array('text' => get_string('enteroptionhere', 'qtype_scmc')
                    ));
            $mform->setType('option_' . $i, PARAM_RAW);

            $mform->addElement('html', '</div>'); // Close div.optiontext.

            // Add the radio buttons for responses.
            $mform->addElement('html', '<div class="responses">'); // Open div.responses.
            $radiobuttons = array();
            $radiobuttonname = 'weightbutton_' . $i;

            for ($j = 1; $j <= 2; ++$j) {
                if ($j == 1) {
                    $negativeorpositive = 'positive'; // Usually TRUE.
                } else {
                    $negativeorpositive = 'negative'; // Usually FALSE.
                }
                $attributes = array('data-colscmc' => $negativeorpositive
                );

                if (array_key_exists($j - 1, $responsetexts)) {
                    $radiobuttons[] = &$mform->createElement(
                        'radio', $radiobuttonname, '', $responsetexts[$j - 1], $j, $attributes);
                } else {
                    $radiobuttons[] = &$mform->createElement('radio', $radiobuttonname, '', '', $j, $attributes);
                }
            }
            $mform->addGroup($radiobuttons, $radiobuttonname, '', array('<br/>'
            ), false);
            $mform->setDefault($radiobuttonname, 2);

            $mform->addElement('html', '</div>'); // Close div.responses.
            $mform->addElement('html', '</div>'); // Close div.optionsandresponses.

            $mform->addElement('html', '<br /><br />'); // Close div.optionsandresponses.

            // Add the feedback text editor in a new line.
            $mform->addElement('html', '<div class="feedbacktext">'); // Open div.feedbacktext.
            $mform->addElement('html',
                    '<label class="feedbacktitle">' .
                             get_string('feedbackforoption', 'qtype_scmc', $i) . '</label>');
            $mform->addElement('editor', 'feedback_' . $i, '',
                    array('rows' => 2, 'placeholder' => ''
                    ), $this->editoroptions);
            $mform->setType('feedback_' . $i, PARAM_RAW);

            $mform->addElement('html', '</div>'); // Close div.feedbacktext.
            $mform->addElement('html', '</div><br />'); // Close div.optionbox.
        }

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);

        // Keep state of number of options to warn user if they go lower.
        $mform->addElement('hidden', 'qtype_scmc_lastnumberofcols');
        $mform->setType('qtype_scmc_lastnumberofcols', PARAM_INT);
        $mform->setDefault('qtype_scmc_lastnumberofcols', $lastnumberofrows);

        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_ALPHA);

        $this->add_hidden_fields();
    }

    public function js_call() {
        global $PAGE;
        foreach (array_keys(
                get_string_manager()->load_component_strings('qtype_scmc', current_language())) as $string) {
            $PAGE->requires->string_for_js($string, 'qtype_scmc');
        }
        $PAGE->requires->jquery();
        $PAGE->requires->yui_module('moodle-qtype_scmc-form', '', array(0
        ));
    }

    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        list($repeated, $repeatedoptions) = parent::get_hint_fields($withclearwrong, $withshownumpartscorrect);
        $repeatedoptions['hintclearwrong']['disabledif'] = array('single', 'eq', 1);
        $repeatedoptions['hintshownumcorrect']['disabledif'] = array('single', 'eq', 1);
        return array($repeated, $repeatedoptions);
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_edit_form::data_preprocessing()
     */
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (isset($question->options)) {
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->scoringmethod = $question->options->scoringmethod;
            $question->rows = $question->options->rows;
            $question->columns = $question->options->columns;
            // LMDL-141.
            if ($question->options->numberofrows > 5) { // Reset to the max. i.e 5.
                $question->options->numberofrows = 5;
            }
            $question->numberofrows = $question->options->numberofrows;
            $question->numberofcolumns = $question->options->numberofcolumns;
        }

        if (isset($this->question->id)) {
            $key = 1;
            foreach ($question->options->rows as $row) {
                // Restore all images in the option text.
                $draftid = file_get_submitted_draft_itemid('option_' . $key);
                $question->{'option_' . $key}['text'] = file_prepare_draft_area($draftid,
                        $this->context->id, 'qtype_scmc', 'optiontext',
                        !empty($row->id) ? (int) $row->id : null, $this->fileoptions,
                        $row->optiontext);
                $question->{'option_' . $key}['itemid'] = $draftid;

                // Now do the same for the feedback text.
                $draftid = file_get_submitted_draft_itemid('feedback_' . $key);
                $question->{'feedback_' . $key}['text'] = file_prepare_draft_area($draftid,
                        $this->context->id, 'qtype_scmc', 'feedbacktext',
                        !empty($row->id) ? (int) $row->id : null, $this->fileoptions,
                        $row->optionfeedback);
                $question->{'feedback_' . $key}['itemid'] = $draftid;

                ++$key;
            }
        }
        $this->js_call();
        return $question;
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_edit_form::validation()
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // Check for empty option texts.
        for ($i = 1; $i <= $data['numberofrows']; ++$i) {
            $optiontext = $data['option_' . $i]['text'];
            // Remove HTML tags.
            $optiontext = trim(strip_tags($optiontext));
            // Remove newlines.
            $optiontext = preg_replace("/[\r\n]+/i", '', $optiontext);
            // Remove whitespaces and tabs.
            $optiontext = preg_replace("/[\s\t]+/i", '', $optiontext);
            // Also remove UTF-8 non-breaking whitespaces.
            $optiontext = trim($optiontext, "\xC2\xA0\n");
            // Now check whether the string is empty.
            if (empty($optiontext)) {
                $errors['option_' . $i] = get_string('mustsupplyvalue', 'qtype_scmc');
            }
        }
        // Check for empty response texts.
        for ($j = 1; $j <= $data['numberofcolumns']; ++$j) {
            if (trim(strip_tags($data['responsetext_' . $j])) == false) {
                $errors['responsetext_' . $j] = get_string('mustsupplyvalue', 'qtype_scmc');
            }
        }

        return $errors;
    }
}
