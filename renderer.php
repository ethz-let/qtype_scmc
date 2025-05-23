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
 * @author ETH Zurich moodle@id.ethz.ch
 * @copyright ETHz 2016
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Subclass for generating the bits of output specific to scmc questions.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_scmc_renderer extends qtype_renderer {

    /**
     *
     * @return string
     */
    protected function get_input_type() {
        return 'radio';
    }

    /**
     *
     * @param question_attempt $qa
     * @param unknown $value
     *
     * @return string
     */
    protected function get_input_name(question_attempt $qa, $value) {
        return $qa->get_qt_field_name('option');
    }

    /**
     *
     * @param unknown $value
     *
     * @return unknown
     */
    protected function get_input_value($value) {
        return $value;
    }

    /**
     *
     * @param question_attempt $qa
     * @param unknown $value
     *
     * @return string
     */
    protected function get_input_id(question_attempt $qa, $value) {
        return $qa->get_qt_field_name('option' . $value);
    }

    /**
     * Generate the display of the formulation part of the question.
     * This is the
     * area that contains the question text (stem), and the controls for students to
     * input their answers.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     *
     * @return string HTML fragment.
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $displayoptions) {
        global $CFG;
        $scmcconfig = get_config('qtype_scmc');
        if (!$scmcconfig->only_single_feedback && $scmcconfig->only_single_feedback != 0) {
            $scmcconfig->only_single_feedback = 1;
        }
        $question = $qa->get_question();
        $response = $question->get_response($qa);

        $inputname = $qa->get_qt_field_name('option');
        $inputattributes = array('type' => $this->get_input_type(), 'name' => $inputname
        );

        if ($displayoptions->readonly) {
            $inputattributes['disabled'] = 'disabled';
        }
        $this->page->requires->js( new moodle_url($CFG->wwwroot . '/question/type/scmc/js/attempt.js') );

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
        array('class' => 'qtext'
        ));

        $table = new html_table();
        $table->attributes['class'] = 'generaltable';

        $table->head = array();

        // Add the response texts as table headers if question is not single choice.
        if (count($question->columns) > 1) {
            foreach ($question->columns as $column) {
                $cell = new html_table_cell(
                        $question->make_html_inline(
                                $question->format_text($column->responsetext,
                                        $column->responsetextformat, $qa, 'question', 'response',
                                        $column->id)));
                $table->head[] = $cell;
            }
        }

        // Add empty header for correctness if needed.
        if ($displayoptions->correctness) {
            $table->head[] = '';
        }

        // Add empty header for feedback if needed.
        if ($displayoptions->feedback) {
            $table->head[] = '';
        }

        $rowcount = 0;
        $isreadonly = $displayoptions->readonly;

        foreach ($question->get_order($qa) as $key => $rowid) {
            $field = $question->field($key);
            $row = $question->rows[$rowid];

            // Holds the data for one table row.
            $rowdata = array();

            // Add the response radio buttons to the table.
            foreach ($question->columns as $column) {

                $buttonname = $qa->get_field_prefix() . $field;
                $buttonid = 'qtype_scmc_' . $qa->get_field_prefix() . $field;
                $qtypescmcid = 'qtype_scmc_' . $question->id;
                $datacol = 'data-scmc="' . $qtypescmcid . '"';
                $ischecked = false;
                if (array_key_exists($field, $response) && ($response[$field] == $column->number)) {
                    $ischecked = true;
                }
                if (count($question->columns) > 1) {
                    $datamulti = 'data-multiscmc="1"';
                    $singleormulti = 2;
                } else {
                    $datamulti = 'data-multiscmc="0"';
                    $singleormulti = 1;
                }
                $radio = $this->radiobutton(
                    $buttonname,
                    $column->number,
                    $ischecked,
                    $isreadonly,
                    $buttonid,
                    $datacol,
                    $datamulti,
                    $singleormulti,
                    $qtypescmcid);
                // Show correctness icon with radio button if needed.
                if (count($question->columns) > 1 || $scmcconfig->only_single_feedback == 0) {
                    if ($displayoptions->correctness) {
                        $weight = $question->weight($row->number, $column->number);
                        $radio .= '<span class="scmcgreyingout">' . $this->feedback_image($weight > 0.0) .
                                    '</span>';
                    }
                } else {
                    if ($displayoptions->correctness && $ischecked == true) {
                        $weight = $question->weight($row->number, $column->number);
                        $radio .= '<span class="scmcgreyingout">' . $this->feedback_image($weight > 0.0) .
                                    '</span>';
                    }
                }
                $cell = new html_table_cell($radio);
                $cell->attributes['class'] = 'scmcresponsebutton';
                $rowdata[] = $cell;
            }

            // Add the formated option text to the table.
            $rowtext = $this->number_in_style($rowcount, $question->answernumbering) .
                        $question->make_html_inline($question->format_text( $row->optiontext, $row->optiontextformat, $qa,
                            'qtype_scmc', 'optiontext', $row->id));
            $rowcount++;

            $cell = new html_table_cell('<span class="optiontext"><label for="' . $buttonid . '">' . $rowtext . '</label></span>');
            $cell->attributes['class'] = 'optiontext';
            $rowdata[] = $cell;
            // Has a selection been made for this option?
            $isselected = $question->is_answered($response, $key);
            // For correctness we have to grade the option...
            if ($displayoptions->correctness) {
                if (count($question->columns) > 1  || $scmcconfig->only_single_feedback == 0) {
                    $rowgrade = $question->grading()->grade_row($question, $key, $row, $response);
                    $cell = new html_table_cell($this->feedback_image($rowgrade));
                    $cell->attributes['class'] = 'scmccorrectness';
                    $rowdata[] = $cell;
                } else {
                    if ($ischecked == true) {
                        $rowgrade = $question->grading()->grade_row($question, $key, $row, $response);
                        $cell = new html_table_cell($this->feedback_image($rowgrade));
                        $cell->attributes['class'] = 'scmccorrectness';
                        $rowdata[] = $cell;
                    } else {
                        $rowgrade = '';
                        $cell = new html_table_cell('');
                        $cell->attributes['class'] = 'scmccorrectness';
                        $rowdata[] = $cell;
                    }
                }
            }
            if (count($question->columns) > 1  || $scmcconfig->only_single_feedback == 0) {
                // Add the feedback to the table, if it is visible.
                if ($displayoptions->feedback && empty($displayoptions->suppresschoicefeedback) &&
                            $isselected && trim($row->optionfeedback)) {
                    $cell = new html_table_cell(
                            html_writer::tag('div',
                                    $question->make_html_inline(
                                            $question->format_text($row->optionfeedback,
                                                    $row->optionfeedbackformat, $qa, 'qtype_scmc',
                                                    'feedbacktext', $rowid)),
                                    array('class' => 'scmcspecificfeedback')));

                    $rowdata[] = $cell;
                } else {
                        $cell = new html_table_cell( html_writer::tag('div', ''));
                        $rowdata[] = $cell;
                }
            } else { // Single Choice.
                // Add the feedback to the table, if it is visible.
                if ($displayoptions->feedback && empty($displayoptions->suppresschoicefeedback) &&
                            $isselected && trim($row->optionfeedback)) {
                    if ($ischecked == true) {
                        $feedbackstr = $question->format_text($row->optionfeedback,
                                                    $row->optionfeedbackformat, $qa, 'qtype_scmc',
                                                    'feedbacktext', $rowid);
                    } else {
                        $feedbackstr = '';
                    }
                    $cell = new html_table_cell(
                            html_writer::tag('div',
                                    $question->make_html_inline(
                                            $feedbackstr),
                                    array('class' => 'scmcspecificfeedback')));

                    $rowdata[] = $cell;
                } else {
                        $cell = new html_table_cell( html_writer::tag('div', ''));
                        $rowdata[] = $cell;
                }
            }
            $table->data[] = $rowdata;
        }
        $result .= html_writer::table($table, true);
        return $result;
    }

    /**
     * Returns the HTML representation of a radio button with the given attributes.
     *
     * @param unknown $name
     * @param unknown $value
     * @param unknown $checked
     * @param unknown $readonly
     *
     * @return string
     */
    protected static function radiobutton($name, $value, $checked, $readonly, $id = '',
        $datacol = '', $datamulti = '', $singleormulti = 2, $qtypescmcid = '') {

        $readonly = $readonly ? 'readonly="readonly" disabled="disabled"' : '';
        $checked = $checked ? 'checked="checked"' : '';
        $result = '';

        if ($id == '') {
            $id = $name;
        }
        if ($singleormulti >= 2) {
            $result .= '<input type="radio" id="' . $id . '" name="' . $name .
                '" value="' . $value . '" ' . $checked . ' ' .
                $readonly . ' ' . $datacol . ' ' . $datamulti . '/>';
        } else {
            $result .= '<input type="hidden" id="hidden_'. $id .'" name="'. $name .
                '" value="2" data-hiddenscmc="' . $qtypescmcid .  '" disabled="disabled">';
            $result .= '<input type="radio" id="' . $id . '" name="' . $name .
                '" value="' . $value . '" ' . $checked . ' ' .
                $readonly . ' ' . $datacol . ' ' . $datamulti . '/>';
        }
        return $result;
    }

    /**
     * The prompt for the user to answer a question.
     *
     * @return Ambigous <string, lang_string, unknown, mixed>
     */
    protected function prompt() {
        return get_string('selectone', 'qtype_scmc');
    }

    /**
     * (non-PHPdoc).
     *
     * @see qtype_renderer::correct_response()
     */
    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $result = array();
        $response = '';
        $correctresponse = $question->get_correct_response(true);
        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];

            if (isset($correctresponse[$rowid])) {
                if (isset($question->columns[$correctresponse[$rowid]])) {
                    $correctcolumn = $question->columns[$correctresponse[$rowid]];
                }
            } else {
                $correctcolumn = new stdClass;
                $correctcolumn->responsetextformat = 1;
                $correctcolumn->responsetext = get_string('false', 'qtype_scmc');
                $correctcolumn->id = $rowid;
            }

            $result[] = ' ' .
                        $question->make_html_inline(
                            $question->format_text($row->optiontext, $row->optiontextformat, $qa,
                                    'qtype_scmc', 'optiontext', $rowid)) . ': ' . $question->make_html_inline(
                            $question->format_text($correctcolumn->responsetext,
                                    $correctcolumn->responsetextformat, $qa, 'question', 'response',
                                    $correctcolumn->id));
        }
        if (!empty($result)) {
            $response = '<ul style="list-style-type: none;"><li>';
            $response .= implode('</li><li>', $result);
            $response .= '</li></ul>';
        }

        return $response;
    }

    protected function number_html($qnum) {
        return $qnum . '. ';
    }

    /**
     * @param int $num The number, starting at 0.
     * @param string $style The style to render the number in. One of the
     * options returned by {@link qtype_scmc:;get_numbering_styles()}.
     * @return string the number $num in the requested style.
     */
    protected function number_in_style($num, $style) {
        switch($style) {
            case 'abc':
                $number = chr(ord('a') + $num);
                break;
            case 'ABCD':
                $number = chr(ord('A') + $num);
                break;
            case '123':
                $number = $num + 1;
                break;
            case 'iii':
                $number = question_utils::int_to_roman($num + 1);
                break;
            case 'IIII':
                $number = strtoupper(question_utils::int_to_roman($num + 1));
                break;
            case 'none':
                return '';
            default:
                return 'ERR';
        }
        return $this->number_html($number);
    }
}
