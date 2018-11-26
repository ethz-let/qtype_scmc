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

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/scmc/lib.php');

/**
 * Question hint for scmc.
 *
 * An extension of {@link question_hint} for questions like match and multiple
 * choice with multile answers, where there are options for whether to show the
 * number of parts right at each stage, and to reset the wrong parts.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_hint_scmc extends question_hint_with_parts {

    public $statewhichincorrect;

    /**
     * Constructor.
     * @param int the hint id from the database.
     * @param string $hint The hint text
     * @param int the corresponding text FORMAT_... type.
     * @param bool $shownumcorrect whether the number of right parts should be shown
     * @param bool $clearwrong whether the wrong parts should be reset.
     */
    public function __construct($id, $hint, $hintformat, $shownumcorrect,
                                                            $clearwrong, $statewhichincorrect) {
        parent::__construct($id, $hint, $hintformat, $shownumcorrect, $clearwrong);
        $this->statewhichincorrect = $statewhichincorrect;
    }

    /**
     * Create a basic hint from a row loaded from the question_hints table in the database.
     * @param object $row with property options as well as hint, shownumcorrect and clearwrong set.
     * @return question_hint_scmc
     */
    public static function load_from_record($row) {
        return new question_hint_scmc($row->id, $row->hint, $row->hintformat,
                $row->shownumcorrect, $row->clearwrong, $row->options);
    }

    public function adjust_display_options(question_display_options $options) {
        parent::adjust_display_options($options);
        $options->statewhichincorrect = $this->statewhichincorrect;
    }
}

/**
 * The scmc question type.
 */
class qtype_scmc extends question_type {

    /**
     * Sets the default options for the question.
     *
     * (non-PHPdoc)
     *
     * @see question_type::set_default_options()
     */
    public function set_default_options($question) {
        $scmcconfig = get_config('qtype_scmc');

        if (!isset($question->options)) {
            $question->options = new stdClass();
        }
        if (!isset($question->options->numberofrows)) {
            $question->options->numberofrows = QTYPE_SCMC_NUMBER_OF_OPTIONS;
        }
        if (!isset($question->options->numberofcolumns)) {
            $question->options->numberofcolumns = 1; //QTYPE_SCMC_NUMBER_OF_RESPONSES;
        }
        if (!isset($question->options->shuffleanswers)) {
            $question->options->shuffleanswers = $scmcconfig->shuffleanswers;
        }
        if (!isset($question->options->scoringmethod)) {
            $question->options->scoringmethod = $scmcconfig->scoringmethod;
        }
        if (!isset($question->options->rows)) {
            $rows = array();
            for ($i = 1; $i <= $question->options->numberofrows; ++$i) {
                $row = new stdClass();
                $row->number = $i;
                $row->optiontext = '';
                $row->optiontextformat = FORMAT_HTML;
                $row->optionfeedback = '';
                $row->optionfeedbackformat = FORMAT_HTML;
                $rows[] = $row;
            }
            $question->options->rows = $rows;
        }

        if (!isset($question->options->columns)) {
            $columns = array();
            for ($i = 1; $i <= $question->options->numberofcolumns; ++$i) {
                $column = new stdClass();
                $column->number = $i;
                if (isset($scmcconfig->{'responsetext' . $i})) {
                    $responsetextcol = $scmcconfig->{'responsetext' . $i};
                } else {
                    $responsetextcol = '';
                }
                $column->responsetext = $responsetextcol;
                $column->responsetextformat = FORMAT_MOODLE;
                $columns[] = $column;
            }
            $question->options->columns = $columns;
        }
    }

    /**
     * Loads the question options, rows, columns and weights from the database.
     *
     * (non-PHPdoc)
     *
     * @see question_type::get_question_options()
     */
    public function get_question_options($question) {
        global $DB, $OUTPUT;

        parent::get_question_options($question);

        // Retrieve the question options.
        $question->options = $DB->get_record('qtype_scmc_options',
        array('questionid' => $question->id
        ));
        // Retrieve the question rows (scmc options).
        $question->options->rows = $DB->get_records('qtype_scmc_rows',
        array('questionid' => $question->id
        ), 'number ASC', '*', 0, $question->options->numberofrows);
        // Retrieve the question columns.
        $question->options->columns = $DB->get_records('qtype_scmc_columns',
        array('questionid' => $question->id
        ), 'number ASC', '*', 0, $question->options->numberofcolumns);

        $weightrecords = $DB->get_records('qtype_scmc_weights',
        array('questionid' => $question->id
        ), 'rownumber ASC, columnnumber ASC');

        foreach ($question->options->rows as $key => $row) {
            $question->{'option_' . $row->number}['text'] = $row->optiontext;
            $question->{'option_' . $row->number}['format'] = $row->optiontextformat;
            $question->{'feedback_' . $row->number}['text'] = $row->optionfeedback;
            $question->{'feedback_' . $row->number}['format'] = $row->optionfeedbackformat;
        }

        foreach ($question->options->columns as $key => $column) {
            $question->{'responsetext_' . $column->number} = $column->responsetext;
        }

        foreach ($weightrecords as $key => $weight) {
            if ($weight->weight == 1.0) {
                $question->{'weightbutton_' . $weight->rownumber} = $weight->columnnumber;
            }
        }
        // Put the weight records into an array indexed by rownumber and columnnumber.
        $question->options->weights = $this->weight_records_to_array($weightrecords);

        return true;
    }

    /**
     * Stores the question options in the database.
     *
     * (non-PHPdoc)
     *
     * @see question_type::save_question_options()
     */
    public function save_question_options($question) {
        global $DB;
        $context = $question->context;
        $result = new stdClass();

        // Insert all the new options.
        $options = $DB->get_record('qtype_scmc_options',
        array('questionid' => $question->id
        ));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->scoringmethod = '';
            $options->shuffleanswers = '';
            $options->numberofcolumns = '';
            $options->numberofrows = '';
			$options->answernumbering = '';
            $options->id = $DB->insert_record('qtype_scmc_options', $options);
        }

        $options->scoringmethod = $question->scoringmethod;
        $options->shuffleanswers = $question->shuffleanswers;
        $options->numberofrows = $question->numberofrows;
        $options->numberofcolumns = $question->numberofcolumns;
		$options->answernumbering = $question->answernumbering;
        $DB->update_record('qtype_scmc_options', $options);

        $this->save_hints($question, true);

        // Insert all the new rows.
        $oldrows = $DB->get_records('qtype_scmc_rows',
        array('questionid' => $question->id
        ), 'number ASC');
		$newrows = array();

        for ($i = 1; $i <= $options->numberofrows; ++$i) {
            $row = array_shift($oldrows);
            if (!$row) {
                $row = new stdClass();
                $row->questionid = $question->id;
                $row->number = $i;
                $row->optiontext = '';
                $row->optiontextformat = FORMAT_HTML;
                $row->optionfeedback = '';
                $row->optionfeedbackformat = FORMAT_HTML;

                $row->id = $DB->insert_record('qtype_scmc_rows', $row);
            }

            // Also save images in optiontext and feedback.
            $optiondata = $question->{'option_' . $i};
            $row->optiontext = $this->import_or_save_files($optiondata, $context, 'qtype_scmc',
                    'optiontext', $row->id);
            $row->optiontextformat = $question->{'option_' . $i}['format'];
            $optionfeedback = $question->{'feedback_' . $i};
            $row->optionfeedback = $this->import_or_save_files($optionfeedback, $context,
                    'qtype_scmc', 'feedbacktext', $row->id);
            $row->optionfeedbackformat = $question->{'feedback_' . $i}['format'];

            $DB->update_record('qtype_scmc_rows', $row);

			$newrows[$row->id] = $row->id;
        }
		// Delete any left over old rows.
		$fs = get_file_storage();
		foreach ($oldrows as $oldrow) {
			if (!in_array($oldrow->id, $newrows)) {
					$fs->delete_area_files($context->id, 'qtype_scmc', 'optiontext', $oldrow->id);
					$fs->delete_area_files($context->id, 'qtype_scmc', 'feedbacktext', $oldrow->id);
					$DB->delete_records('qtype_scmc_rows', array('id' => $oldrow->id));
			}
		}
        $oldcolumns = $DB->get_records('qtype_scmc_columns',
        array('questionid' => $question->id
        ), 'number ASC');
		$newcols = array();
        // Insert all new columns.
        for ($i = 1; $i <= $options->numberofcolumns; ++$i) {
            $column = array_shift($oldcolumns);
            if (!$column) {
                $column = new stdClass();
                $column->questionid = $question->id;
                $column->number = $i;
                $column->responsetext = '';
                $column->responsetextformat = FORMAT_MOODLE;

                $column->id = $DB->insert_record('qtype_scmc_columns', $column);
            }

            // Perform an update.
            $column->responsetext = $question->{'responsetext_' . $i};
            $column->responsetextformat = FORMAT_MOODLE;
            $DB->update_record('qtype_scmc_columns', $column);
			$newcols[$column->id] = $column->id;
        }

		// Delete any left over old columns.
		foreach ($oldcolumns as $oldcolumn) {
			if (!in_array($oldcolumn->id, $newcols)) {
					$DB->delete_records('qtype_scmc_columns', array('id' => $oldcolumn->id));
			}
		}

        // Set all the new weights.
        $oldweightrecords = $DB->get_records('qtype_scmc_weights',
        array('questionid' => $question->id
        ), 'rownumber ASC, columnnumber ASC');
		$newweights = array();
        // Put the old weights into an array.
        $oldweights = $this->weight_records_to_array($oldweightrecords);

        for ($i = 1; $i <= $options->numberofrows; ++$i) {
            for ($j = 1; $j <= $options->numberofcolumns; ++$j) {
                if (!empty($oldweights[$i][$j])) {
                    $weight = $oldweights[$i][$j];
                } else {
                    $weight = new stdClass();
                    $weight->questionid = $question->id;
                    $weight->rownumber = $i;
                    $weight->columnnumber = $j;
                    $weight->weight = 0.0;
                    $weight->id = $DB->insert_record('qtype_scmc_weights', $weight);
                }

                // Perform the weight update.
                if (property_exists($question, 'weightbutton_' . $i)) {
                    if ($question->{'weightbutton_' . $i} == $j) {
                        $weight->weight = 1.0;
                    } else {
                        $weight->weight = 0.0;
                    }
                } else {
                    $weight->weight = 0.0;
                }
                $DB->update_record('qtype_scmc_weights', $weight);
				$newweights[$weight->id] = $weight->id;
            }
        }
		// Delete any left over old weights.
		foreach ($oldweightrecords as $oldweightrecord) {
			if (!in_array($oldweightrecord->id, $newweights)) {
					$DB->delete_records('qtype_scmc_weights', array('id' => $oldweightrecord->id));
			}
		}
    }

    public function save_hints($formdata, $withparts = false) {
        global $DB;
        $context = $formdata->context;

        $oldhints = $DB->get_records('question_hints',
                array('questionid' => $formdata->id), 'id ASC');

        if (!empty($formdata->hint)) {
            $numhints = max(array_keys($formdata->hint)) + 1;
        } else {
            $numhints = 0;
        }

        if ($withparts) {
            if (!empty($formdata->hintclearwrong)) {
                $numclears = max(array_keys($formdata->hintclearwrong)) + 1;
            } else {
                $numclears = 0;
            }
            if (!empty($formdata->hintshownumcorrect)) {
                $numshows = max(array_keys($formdata->hintshownumcorrect)) + 1;
            } else {
                $numshows = 0;
            }
            $numhints = max($numhints, $numclears, $numshows);
        }

        for ($i = 0; $i < $numhints; $i += 1) {
            if (html_is_blank($formdata->hint[$i]['text'])) {
                $formdata->hint[$i]['text'] = '';
            }

            if ($withparts) {
                $clearwrong = !empty($formdata->hintclearwrong[$i]);
                $shownumcorrect = !empty($formdata->hintshownumcorrect[$i]);
                $statewhichincorrect = !empty($formdata->hintoptions[$i]);
            }

            if (empty($formdata->hint[$i]['text']) && empty($clearwrong) &&
                    empty($shownumcorrect) && empty($statewhichincorrect)) {
                continue;
            }

            // Update an existing hint if possible.
            $hint = array_shift($oldhints);
            if (!$hint) {
                $hint = new stdClass();
                $hint->questionid = $formdata->id;
                $hint->hint = '';
                $hint->id = $DB->insert_record('question_hints', $hint);
            }

            $hint->hint = $this->import_or_save_files($formdata->hint[$i],
                    $context, 'question', 'hint', $hint->id);
            $hint->hintformat = $formdata->hint[$i]['format'];
            if ($withparts) {
                $hint->clearwrong = $clearwrong;
                $hint->shownumcorrect = $shownumcorrect;
                $hint->options = $statewhichincorrect;
            }
            $DB->update_record('question_hints', $hint);
        }

        // Delete any remaining old hints.
        $fs = get_file_storage();
        foreach ($oldhints as $oldhint) {
            $fs->delete_area_files($context->id, 'question', 'hint', $oldhint->id);
            $DB->delete_records('question_hints', array('id' => $oldhint->id));
        }
    }

    protected function make_hint($hint) {
        return question_hint_scmc::load_from_record($hint);
    }
    

    /**
     * Initialise the common question_definition fields.
     *
     * @param question_definition $question the question_definition we are creating.
     * @param object $questiondata the question data loaded from the database.
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);

        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $question->scoringmethod = $questiondata->options->scoringmethod;
        $question->numberofrows = $questiondata->options->numberofrows;
        $question->numberofcolumns = $questiondata->options->numberofcolumns;
        $question->rows = $questiondata->options->rows;
        $question->columns = $questiondata->options->columns;
        $question->weights = $questiondata->options->weights;
		$question->answernumbering = $questiondata->options->answernumbering;
    }

    /**
     * Custom method for deleting scmc questions.
     *
     * (non-PHPdoc)
     *
     * @see question_type::delete_question()
     */
    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('qtype_scmc_options', array('questionid' => $questionid
        ));
        $DB->delete_records('qtype_scmc_rows', array('questionid' => $questionid
        ));
        $DB->delete_records('qtype_scmc_columns', array('questionid' => $questionid
        ));
        $DB->delete_records('qtype_scmc_weights', array('questionid' => $questionid
        ));
        parent::delete_question($questionid, $contextid);
    }

    /**
     * Turns an array of records from the table qtype_scmc_weights into an array of array indexed
     * by rows and columns.
     *
     * @param unknown $weightrecords
     *
     * @return Ambigous <multitype:multitype: , unknown>
     */
    private function weight_records_to_array($weightrecords) {
        $weights = array();
        foreach ($weightrecords as $id => $weight) {
            if (!array_key_exists($weight->rownumber, $weights)) {
                $weights[$weight->rownumber] = array();
            }
            $weights[$weight->rownumber][$weight->columnnumber] = $weight;
        }

        return $weights;
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_type::get_random_guess_score()
     */
    public function get_random_guess_score($questiondata) {
        $scoring = $questiondata->options->scoringmethod;
		$questionoptioncount = $questiondata->options->numberofcolumns;
		$totalfraction = 0;
		$countpositiveweights = 0;
		$question = $this->make_question($questiondata);
        $weights = $question->weights;
		foreach ($question->rows as $rowid => $row) {
			foreach ($question->columns as $columnid => $column) {
					$weight = $weights[$row->number][$column->number]->weight;
					$totalfraction += $weight;		
					if ($weight > 0){
						$countpositiveweights ++;
					}
			}
		}
		if ($questionoptioncount > 1) {			
			if ($scoring == 'scmconezero') { 
				return pow(0.5,count($question->rows)); //1.0 / (pow(2,count($question->rows)));
			} else if ($scoring == 'subpoints') {
				return 0.5; //1.0 / count($questiondata->options->rows);
			} else {
				return 0.00;
			}		
		} else {
			// Single choice questions - average choice fraction.
			return 1.0 / count($questiondata->options->rows);
		}
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_type::get_possible_responses()
     */
    public function get_possible_responses($questiondata) {
		
		$questionoptioncount = $questiondata->options->numberofcolumns;
		
        $question = $this->make_question($questiondata);
        $weights = $question->weights;
        $parts = array();
		if ($questionoptioncount > 1) {
			foreach ($question->rows as $rowid => $row) {
				$choices = array();
				foreach ($question->columns as $columnid => $column) {
					// Calculate the partial credit.
					if ($question->scoringmethod == 'subpoints') {
						$partialcredit = 0.0;
					} else {
						$partialcredit = -0.999; // Due to non-linear math.
					}
					if (($question->scoringmethod == 'subpoints') &&
							 $weights[$row->number][$column->number]->weight > 0) {
						$partialcredit = 1 / count($question->rows);
					}
					$correctreponse = '';
					if ($weights[$row->number][$column->number]->weight > 0) { // Is it correct
																			   // Response?
						$correctreponse = ' (' . get_string('correctresponse', 'qtype_scmc') . ')';
					}
					$choices[$columnid] = new question_possible_response(
							question_utils::to_plain_text($row->optiontext, $row->optiontextformat) .
									 ': ' . question_utils::to_plain_text(
											$column->responsetext . $correctreponse,
											$column->responsetextformat), $partialcredit);
				}			
				$choices[null] = question_possible_response::no_response();	

				$parts[$rowid] = $choices;
			}
			return $parts;
		} else {
		    $choices = array();
            foreach ($question->rows as $rowid => $row) {
				
				foreach ($question->columns as $columnid => $column) {
					if ($weights[$row->number][$column->number]->weight > 0) { // Is it correct
																			   // Response?
						$correctreponse = ' (' . get_string('correctresponse', 'qtype_scmc') . ')';
						$partialcredit = 1;
					} else {
						$correctreponse = '';
						$partialcredit = 0;
					}
				}
                $choices[$rowid] = new question_possible_response(
						question_utils::to_plain_text($row->optiontext . $correctreponse, $row->optiontextformat), $partialcredit);
            }
            $choices[null] = question_possible_response::no_response();
            return array($questiondata->id => $choices);

		}

    }

    /**
     * @return array of the numbering styles supported. For each one, there
     *      should be a lang string answernumberingxxx in teh qtype_scmc
     *      language file, and a case in the switch statement in number_in_style,
     *      and it should be listed in the definition of this column in install.xml.
     */
    public static function get_numbering_styles() {
        $styles = array();
        foreach (array('none', 'abc', 'ABCD', '123', 'iii', 'IIII') as $numberingoption) {
            $styles[$numberingoption] =
                    get_string('answernumbering' . $numberingoption, 'qtype_scmc');
        }
        return $styles;
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_type::move_files()
     */
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_options_and_feedback($questionid, $oldcontextid, $newcontextid, true);
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_type::delete_files()
     */
    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_options_and_feedback($questionid, $contextid);
    }

    /**
     * Move all the files belonging to this question's options and feedbacks
     * when the question is moved from one context to another.
     *
     * @param int $questionid the question being moved.
     * @param int $oldcontextid the context it is moving from.
     * @param int $newcontextid the context it is moving to.
     * @param bool $answerstoo whether there is an 'answer' question area,
     *        as well as an 'answerfeedback' one. Default false.
     */
    protected function move_files_in_options_and_feedback($questionid, $oldcontextid, $newcontextid,
            $answerstoo = false) {
        global $DB;

        $fs = get_file_storage();

        $rowids = $DB->get_records_menu('qtype_scmc_rows',
        array('questionid' => $questionid
        ), 'id', 'id,1');
        foreach ($rowids as $rowid => $notused) {
            $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, 'qtype_scmc',
            'optiontext', $rowid);
            $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, 'qtype_scmc',
            'feedbacktext', $rowid);
        }
    }

    /**
     * Delete all the files belonging to this question's options and feedback.
     *
     *
     * @param unknown $questionid
     * @param unknown $contextid
     */
    protected function delete_files_in_options_and_feedback($questionid, $contextid) {
        global $DB;
        $fs = get_file_storage();

        $rowids = $DB->get_records_menu('qtype_scmc_rows',
        array('questionid' => $questionid
        ), 'id', 'id,1');

        foreach ($rowids as $rowid => $notused) {
            $fs->delete_area_files($contextid, 'qtype_scmc', 'optiontext', $rowid);
            $fs->delete_area_files($contextid, 'qtype_scmc', 'feedbacktext', $rowid);
        }
    }

    /**
     * Provide export functionality for xml format.
     *
     * @param question object the question object
     * @param format object the format object so that helper methods can be used
     * @param extra mixed any additional format specific data that may be passed by the format (see
     *        format code for info)
     *
     * @return string the data to append to the output buffer or false if error
     */
    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $expout = '';
        $fs = get_file_storage();
        $contextid = $question->contextid;

        // First set the additional fields.
        $expout .= '    <scoringmethod>' . $format->writetext($question->options->scoringmethod) .
                 "</scoringmethod>\n";
        $expout .= '    <shuffleanswers>' . $format->get_single($question->options->shuffleanswers) .
                 "</shuffleanswers>\n";
        $expout .= '    <numberofrows>' . $question->options->numberofrows . "</numberofrows>\n";
        $expout .= '    <numberofcolumns>' . $question->options->numberofcolumns .
                 "</numberofcolumns>\n";
		$expout .= '    <answernumbering>' . $question->options->answernumbering . "</answernumbering>\n";

        // Now we export the question rows (options).
        foreach ($question->options->rows as $row) {
            $number = $row->number;
            $expout .= "    <row number=\"$number\">\n";
            $textformat = $format->get_format($row->optiontextformat);
            $files = $fs->get_area_files($contextid, 'qtype_scmc', 'optiontext', $row->id);
            $expout .= "      <optiontext format=\"$textformat\">\n" . '        ' .
                     $format->writetext($row->optiontext);
            $expout .= $format->write_files($files);
            $expout .= "      </optiontext>\n";

            $textformat = $format->get_format($row->optionfeedbackformat);
            $files = $fs->get_area_files($contextid, 'qtype_scmc', 'feedbacktext', $row->id);
            $expout .= "      <feedbacktext format=\"$textformat\">\n" . '        ' .
                     $format->writetext($row->optionfeedback);
            $expout .= $format->write_files($files);
            $expout .= "      </feedbacktext>\n";
            $expout .= "    </row>\n";
        }

        // Now we export the columns (responses).
        foreach ($question->options->columns as $column) {
            $number = $column->number;
            $expout .= "    <column number=\"$number\">\n";
            $textformat = $format->get_format($column->responsetextformat);
            $expout .= "      <responsetext format=\"$textformat\">\n" . '        ' .
                     $format->writetext($column->responsetext);
            $expout .= "      </responsetext>\n";
            $expout .= "    </column>\n";
        }

        // Finally, we export the weights.
        $weights = call_user_func_array('array_merge', $question->options->weights);
        foreach ($weights as $weight) {
            $rownumber = $weight->rownumber;
            $columnnumber = $weight->columnnumber;
            $value = $weight->weight;

            $expout .= "    <weight rownumber=\"$rownumber\" columnnumber=\"$columnnumber\">\n";
            $expout .= "      <value>\n";
            $expout .= '         ' . $value . "\n";
            $expout .= "      </value>\n";
            $expout .= "    </weight>\n";
        }

        return $expout;
    }

    /**
     * Provide import functionality for xml format.
     *
     * @param data mixed the segment of data containing the question
     * @param question object question object processed (so far) by standard import code
     * @param format object the format object so that helper methods can be used (in particular
     *        error())
     * @param extra mixed any additional format specific data that may be passed by the format (see
     *        format code for info)
     *
     * @return object question object suitable for save_options() call or false if cannot handle
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        // Check whether the question is for us.
        if (!isset($data['@']['type']) || $data['@']['type'] != 'scmc') {
            return false;
        }

        $question = $format->import_headers($data);
        $question->qtype = 'scmc';

        $question->scoringmethod = $format->getpath($data,
        array('#', 'scoringmethod', 0, '#', 'text', 0, '#'
        ), 'scmc');
        $question->shuffleanswers = $format->trans_single(
        $format->getpath($data, array('#', 'shuffleanswers', 0, '#'
        ), 1));
        $question->numberofrows = $format->getpath($data,
        array('#', 'numberofrows', 0, '#'
        ), QTYPE_SCMC_NUMBER_OF_OPTIONS);
        $question->numberofcolumns = $format->getpath($data,
        array('#', 'numberofcolumns', 0, '#'
        ), QTYPE_SCMC_NUMBER_OF_RESPONSES);
        $question->answernumbering = $format->getpath($data,
        array('#', 'answernumbering', 0, '#'
        ), 'none');

        $rows = $data['#']['row'];
        $i = 1;
        foreach ($rows as $row) {
            $number = $format->getpath($row, array('@', 'number'
            ), $i++);

            $question->{'option_' . $number} = array();
            $question->{'option_' . $number}['text'] = $format->getpath($row,
            array('#', 'optiontext', 0, '#', 'text', 0, '#'
            ), '', true);
            $question->{'option_' . $number}['format'] = $format->trans_format(
            $format->getpath($row, array('#', 'optiontext', 0, '@', 'format'
            ), FORMAT_HTML));

            $question->{'option_' . $number}['files'] = array();

            // Restore files in options (rows).
            $files = $format->getpath($row, array('#', 'optiontext', 0, '#', 'file'
            ), array(), false);
            foreach ($files as $file) {
                $filesdata = new stdclass();
                $filesdata->content = $file['#'];
                $filesdata->encoding = $file['@']['encoding'];
                $filesdata->name = $file['@']['name'];
                $question->{'option_' . $number}['files'][] = $filesdata;
            }

            $question->{'feedback_' . $number} = array();
            $question->{'feedback_' . $number}['text'] = $format->getpath($row,
            array('#', 'feedbacktext', 0, '#', 'text', 0, '#'
            ), '', true);
            $question->{'feedback_' . $number}['format'] = $format->trans_format(
            $format->getpath($row, array('#', 'feedbacktext', 0, '@', 'format'
            ), FORMAT_HTML));

            // Restore files in option feedback.
            $question->{'feedback_' . $number}['files'] = array();
            $files = $format->getpath($row,
            array('#', 'feedbacktext', 0, '#', 'file'
            ), array(), false);

            foreach ($files as $file) {
                $filesdata = new stdclass();
                $filesdata->content = $file['#'];
                $filesdata->encoding = $file['@']['encoding'];
                $filesdata->name = $file['@']['name'];
                $question->{'feedback_' . $number}['files'][] = $filesdata;
            }
        }

        $columns = $data['#']['column'];
        $j = 1;
        foreach ($columns as $column) {
            $number = $format->getpath($column, array('@', 'number'
            ), $j++);
            $question->{'responsetext_' . $number} = $format->getpath($column,
            array('#', 'responsetext', 0, '#', 'text', 0, '#'
            ), '', true);
        }

        // Finally, import the weights.
        $weights = $data['#']['weight'];
        foreach ($weights as $weight) {
            $rownumber = $format->getpath($weight, array('@', 'rownumber'
            ), 1);
            $columnnumber = $format->getpath($weight, array('@', 'columnnumber'
            ), 1);
            $value = $format->getpath($weight, array('#', 'value', 0, '#'
            ), 0.0);

            if ($value > 0.0) {
                $question->{'weightbutton_' . $rownumber} = $columnnumber;
            }
        }

        return $question;
    }
}
