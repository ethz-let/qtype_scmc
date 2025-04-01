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


/**
 * Restore plugin class that provides the necessary information
 * needed to restore one scmc qtype plugin.
 */
class restore_qtype_scmc_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_question_plugin_structure() {
        $result = array();

        // We used get_recommended_name() so this works.
        $elename = 'scmc';
        $elepath = $this->get_pathfor('/scmc');
        $result[] = new restore_path_element($elename, $elepath);

        // We used get_recommended_name() so this works.
        $elename = 'column';
        $elepath = $this->get_pathfor('/columns/column');
        $result[] = new restore_path_element($elename, $elepath);

        // We used get_recommended_name() so this works.
        $elename = 'row';
        $elepath = $this->get_pathfor('/rows/row');
        $result[] = new restore_path_element($elename, $elepath);

        // We used get_recommended_name() so this works.
        $elename = 'weight';
        $elepath = $this->get_pathfor('/weights/weight');
        $result[] = new restore_path_element($elename, $elepath);

        return $result;
    }

    /**
     * Process the qtype/multichoice element.
     */
    public function process_scmc($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        // Detect if the question is created or mapped.
        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');

        $questioncreated = (bool) $this->get_mappingid('question_created', $oldquestionid);

        // If the question has been created by restore, we need to create its
        // qtype_scmc_options too.
        if ($questioncreated) {
            // Adjust some columns.
            $data->questionid = $newquestionid;
            // Insert record.
            $newitemid = $DB->insert_record('qtype_scmc_options', $data);
            // Create mapping (needed for decoding links).
            $this->set_mapping('qtype_scmc_options', $oldid, $newitemid);
        }
    }

    /**
     * Detect if the question is created or mapped.
     *
     * @return bool
     */
    protected function is_question_created() {
        $oldquestionid = $this->get_old_parentid('question');
        $questioncreated = (bool) $this->get_mappingid('question_created', $oldquestionid);

        return $questioncreated;
    }

    /**
     * Process the qtype/scmc/columns/column.
     */
    public function process_column($data) {
        global $DB;

        if (!$this->is_question_created()) {
            return;
        }

        $data = (object) $data;
        $oldid = $data->id;

        $data->questionid = $this->get_new_parentid('question');
        $newitemid = $DB->insert_record('qtype_scmc_columns', $data);
        $this->set_mapping('qtype_scmc_columns', $oldid, $newitemid);
    }

    /**
     * Process the qtype/scmc/rows/row element.
     */
    public function process_row($data) {
        global $DB;

        if (!$this->is_question_created()) {
            return;
        }

        $data = (object) $data;
        $oldid = $data->id;

        $data->questionid = $this->get_new_parentid('question');
        $newitemid = $DB->insert_record('qtype_scmc_rows', $data);

        $this->set_mapping('qtype_scmc_rows', $oldid, $newitemid);
    }

    /**
     * Process the qtype/scmc/weights/weight element.
     */
    public function process_weight($data) {
        global $DB;

        if (!$this->is_question_created()) {
            return;
        }

        $data = (object) $data;
        $oldid = $data->id;

        $data->questionid = $this->get_new_parentid('question');
        $newitemid = $DB->insert_record('qtype_scmc_weights', $data);
        $this->set_mapping('qtype_scmc_weights', $oldid, $newitemid);
    }

    public function recode_response($questionid, $sequencenumber, array $response) {
        if (array_key_exists('_order', $response)) {
            $response['_order'] = $this->recode_option_order($response['_order']);
        }

        return $response;
    }

    /**
     * Recode the option order as stored in the response.
     *
     * @param string $order the original order.
     *
     * @return string the recoded order.
     */
    protected function recode_option_order($order) {
        $neworder = array();
        foreach (explode(',', $order) as $id) {
            if ($newid = $this->get_mappingid('qtype_scmc_rows', $id)) {
                $neworder[] = $newid;
            } else {
                $neworder[] = $id;
            }
        }

        return implode(',', $neworder);
    }

    /**
     * Return the contents of this qtype to be processed by the links decoder.
     */
    public static function define_decode_contents() {
        $contents = array();

        $fields = array('optiontext', 'optionfeedback'
        );
        $contents[] = new restore_decode_content('qtype_scmc_rows', $fields, 'qtype_scmc_rows');

        return $contents;
    }
        /**
     * Convert the backup structure of the MTF question type into a structure matching its
     * question data. This data will then be used to produce an identity hash for comparison with
     * questions in the database. We have to override the parent function, because we use a special
     * structure during backup.
     *
     * @param array $backupdata
     * @return stdClass
     */
    public static function convert_backup_to_questiondata(array $backupdata): stdClass {
        // First, convert standard data via the parent function.
        $questiondata = parent::convert_backup_to_questiondata($backupdata);
        /**
        * Convert the row data. An array of all rows (as objects) is stored in $questiondata->options.
        * Furthermore, there will be properties $questiondata->option_N, each containing an object with
        * properties text (= row's optiontext field) and format (= row's optiontextformat field), with
        * N being the row number. And finally, there is the property $questiondata->feedback_N containing
        * each an object with the properties text (= row's optionfeedback field) and format (= row's
        * optionfeedbackformat field), with N being the row number again.
        */
        foreach ($backupdata['plugin_qtype_scmc_question']['rows']['row'] as $row) {
            $questiondata->options->rows[] = (object) $row;
            $optionfield = 'option_' . $row['number'];
            $feedbackfield = 'feedback_' . $row['number'];
            $questiondata->$optionfield = (object)[
                'text' => $row['optiontext'],
                'format' => $row['optiontextformat'],
            ];
            $questiondata->$feedbackfield = (object)[
                'text' => $row['optionfeedback'],
                'format' => $row['optionfeedbackformat'],
            ];
        }
        /**
        * Next step is the column data. An array of all columns (as objects) is stored in $questiondata->columns.
        * Furthermore, for every column N, a property $questiondata->responsetext_N must be created that holds the
        * content of the column's responstext field.
        */
        foreach ($backupdata['plugin_qtype_scmc_question']['columns']['column'] as $column) {
            $questiondata->options->columns[] = (object) $column;
            $field = 'responsetext_' . $column['number'];
            $questiondata->$field = $column['responsetext'];
        }
        /**
        * Finally, we have to store all weights in the $questiondata->weights property. That is a
        * wo-dimensional array, built like in qtype_mtf::weight_records_to_array(). Also, for every
        * row, we store the number of the column that has a weight > 1. This is done via the
        * weightbutton_N property, with N being the row number.
        */
        $weights = [];
        foreach ($backupdata['plugin_qtype_scmc_question']['weights']['weight'] as $weight) {
            $weight = (object) $weight;
            if (!array_key_exists($weight->rownumber, $weights)) {
                $weights[$weight->rownumber] = [];
            }
            // weightbutton_1, weightbutton_2, ...
            $weights[$weight->rownumber][$weight->columnnumber] = $weight;
            if ($weight->weight > 0.0) {
                $fieldname = 'weightbutton_' . $weight->rownumber;
                $questiondata->$fieldname = $weight->columnnumber;
            }
        }
        $questiondata->options->weights = $weights;

        return $questiondata;
    }

    /**
     * Return a list of paths to fields to be removed from questiondata before creating an identity hash.
     * We have to remove the id and questionid property from all rows, columns and weights.
     *
     * @return array
     */
    protected function define_excluded_identity_hash_fields(): array {
        return [
            '/options/rows/id',
            '/options/rows/questionid',
            '/options/columns/id',
            '/options/columns/questionid',
            '/options/weights/id',
            '/options/weights/questionid',
        ];
    }

    /**
     * Remove excluded fields from the questiondata structure. We use this function to remove the
     * id and questionid fields for the weights, because they cannot be removed via the default
     * mechanism due to the two-dimensional array. Once this is done, we call the parent function
     * to remove the necessary fields.
     *
     * @param stdClass $questiondata
     * @param array $excludefields Paths to the fields to exclude.
     * @return stdClass The $questiondata with excluded fields removed.
     */
    public static function remove_excluded_question_data(stdClass $questiondata, array $excludefields = []): stdClass {
        foreach ($questiondata->options->weights as $weightset) {
            foreach ($weightset as $weight) {
                if (isset($weight->id)) {
                    unset($weight->id);
                }
                if (isset($weight->questionid)) {
                    unset($weight->questionid);
                }
            }
        }

        return restore_qtype_plugin::remove_excluded_question_data($questiondata, $excludefields);
    }
}
