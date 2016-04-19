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
require_once($CFG->dirroot . '/question/type/scmc/grading/qtype_scmc_grading.class.php');


class qtype_scmc_grading_scmconezero extends qtype_scmc_grading {

    const TYPE = 'scmconezero';

    public function get_name() {
        return self::TYPE;
    }

    public function get_title() {
        return get_string('scoring' . self::TYPE, 'qtype_scmc');
    }

    /**
     * Returns the question's grade.
     *
     * (non-PHPdoc)
     *
     * @see qtype_scmc_grading::grade_question()
     */
    public function grade_question($question, $answers) {
        $correctrows = 0;
        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];
            $grade = $this->grade_row($question, $key, $row, $answers);
            if ($grade > 0) {
                ++$correctrows;
            }
        }
        // scmc1/0: if all responses are correct => all points, else 0 points.
        // i.e. points = if (correct_responses == num_options) then max_points else 0.
		// If single choice, either 0 or 1
		if ($question->numberofcolumns < 2) {			
			if ($correctrows == 1) {
				return 1;
			} else {
				return 0;
			}
		} else {
			if ($correctrows == $question->numberofrows) {
				return 1;
			} else {
				return 0;
			}
		}	
    }
}
