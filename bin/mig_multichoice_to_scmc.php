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
require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/question/type/scmc/lib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$all = optional_param('all', 0, PARAM_INT);
$dryrun = optional_param('dryrun', 0, PARAM_INT);

require_login();

if (!is_siteadmin()) {
    echo 'You are not a Website Administrator!';
    die();
}

// Helper function to turn weight records from the database into an array
// indexed by rowid and columnid.
function weight_records_to_array($weightrecords) {
    $weights = array();
    foreach ($weightrecords as $weight) {
        if (!array_key_exists($weight->rowid, $weights)) {
            $weights[$weight->rowid] = array();
        }
        $weights[$weight->rowid][$weight->colid] = $weight;
    }

    return $weights;
}

$starttime = time();

$sql = "SELECT q.*
        FROM {question} q
        WHERE q.qtype = 'multichoice'
        ";
$params = array();

if (!$all && (!($courseid > 0 || $categoryid > 0))) {
    echo "<br/><font color='red'>You should specify either the 'courseid'
    or the 'categoryid' parameter! Or set the parameter 'all' to 1.</font><br/>\n";
    echo "I'm not doing anything without restrictions!\n";
    die();
}

if ($courseid > 0) {
    if (!$course = $DB->get_record('course', array('id' => $courseid
    ))) {
        echo "<br/><font color='red'>Course with ID $courseid  not found...!</font><br/>\n";
        die();
    }
    $coursecontext = context_course::instance($courseid);
    $categories = $DB->get_records('question_categories',
    array('contextid' => $coursecontext->id
    ));

    $catids = array_keys($categories);

    if (!empty($catids)) {
        list($csql, $params) = $DB->get_in_or_equal($catids);
        $sql .= " AND category $csql ";
    } else {
        echo "<br/><font color='red'>No question categories for course found... weird!</font><br/>\n";
        echo "I'm not doing anything without restrictions!\n";
        die();
    }
}

if ($categoryid > 0) {
    if ($category = $DB->get_record('question_categories', array('id' => $categoryid
    ))) {
        echo 'Migration restricted to category "' . $category->name . "\".<br/>\n";
        $sql .= ' AND category = :category ';
        $params = array('category' => $categoryid
        );
    } else {
        echo "<br/><font color='red'>Question category with ID $categoryid  not found...!</font><br/>\n";
        die();
    }
}

$questions = $DB->get_records_sql($sql, $params);
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
echo 'Migrating ' . count($questions) . " multichoice to scmc questions... <br/>\n";

if ($dryrun) {
    echo "***********************************************************<br/>\n";
    echo "*   Dry run: NO changes to the database will be made! *<br/>\n";
    echo "***********************************************************<br/>\n";
}

$counter = 0;
$notmigrated = array();
foreach ($questions as $question) {
    set_time_limit(600);

    $transaction = $DB->start_delegated_transaction();

    $oldquestionid = $question->id;

    // Retrieve rows and columns and count them.
    $multichoice = $DB->get_record('qtype_multichoice_options', array('questionid' => $oldquestionid
    ));
	
	
    $rows = $DB->get_records('question_answers', array('question' => $question->id
    ), ' id ASC ');
    $rowids = array_keys($rows);
	
	$columns = array();
	if ($multichoice->single == 0){
		$colmcount = 2;
	} else {
		$colmcount = 1;
	}
	
	for($i = 1; $i <= $colmcount; $i++) {
		 $colns = new stdClass();
		 $colns->id = $i;
		 $columns[$i] = $colns;
	}
/*	
    $columns = $DB->get_records('qtype_multichoice_cols',
    array('multichoiceid' => $multichoice->id
    ), ' id ASC ');
*/
    if ($dryrun) {
        echo '--------------------------------------------------------------------------------' .
                 "<br/>\n";
        if (count($rows) <= 1) {
            echo 'Question: "' . $question->name . '" with ID ' . $question->id .
                     " would NOT migrated! It has the wrong number of options!<br/>\n";
            $notmigrated[] = $question;
        } else if ($colmcount < 1 || $colmcount > 2) {
            echo 'Question: "' . $question->name . '" with ID ' . $question->id .
                     " would NOT migrated! It has the wrong number of responses!<br/>\n";
            $notmigrated[] = $question;
        } else {
            echo 'Question: "' . $question->name . '" with ID ' . $question->id .
                     " would be migrated!<br/>\n";
        }
        echo shorten_text($question->questiontext, 100, false, '...');
        continue;
    } else {
        echo '--------------------------------------------------------------------------------' .
                 "<br/>\n";
        echo 'Multichoice Question: "' . $question->name . "\"<br/>\n";
    }

    // If the Multichoice question has got too manu options or responses, we ignore it.
    if (count($rows) <= 1) {
        echo "&nbsp;&nbsp; Question has the wrong number of options! Question is not migrated.<br/>\n";
        $notmigrated[] = $question;
        continue;
    }
    if ($colmcount < 1 || $colmcount > 2) {
        echo "&nbsp;&nbsp; Question has the wrong number of responses! Question is not migrated.<br/>\n";
        $notmigrated[] = $question;
        continue;
    }

    // Create a new scmc question in the same category.
    unset($question->id);
    $question->qtype = 'scmc';
    $question->name = $question->name . ' (scmc)';
    $question->timecreated = time();
    $question->timemodified = time();
    $question->modifiedby = $USER->id;
    $question->createdby = $USER->id;
    // Get the new question ID.
    $question->id = $DB->insert_record('question', $question);

    echo 'New scmc Question: "' . $question->name . '" with ID ' . $question->id . "<br/>\n";

    $rowcount = 1;
	$ignorequestion = 0;
    foreach ($rows as $row) {
        // Create a new scmc row.
        $scmcrow = new stdClass();
        $scmcrow->questionid = $question->id;
        $scmcrow->number = $rowcount++;
        $scmcrow->optiontext = $row->answer;
        $scmcrow->optiontextformat = FORMAT_HTML;
        $scmcrow->optionfeedback = $row->feedback;
        $scmcrow->optionfeedbackformat = FORMAT_HTML;
        $scmcrow->id = $DB->insert_record('qtype_scmc_rows', $scmcrow);
		
		$colcount = 1;
		$textcount = 1;
		foreach ($columns as $column) {		
		
			// Create a new scmc column.
			$scmccolumn = new stdClass();
			$scmccolumn->questionid = $question->id;
			$scmccolumn->number = $colcount++;
			if ($textcount == 1) {
				$scmccolumn->responsetext = 'True';
			} else {
				$scmccolumn->responsetext = 'False';
			}
			$textcount++;
			//$scmccolumn->responsetext = $column->shorttext;
			$scmccolumn->responsetextformat = FORMAT_MOODLE;

			if ( $ignorequestion != $question->id ) {
				$scmccolumn->id = $DB->insert_record('qtype_scmc_columns', $scmccolumn);				
			}

			// Create a new weight entry.
            $scmcweight = new stdClass();
            $scmcweight->questionid = $question->id;
            $scmcweight->rownumber = $scmcrow->number;
            $scmcweight->columnnumber = $scmccolumn->number;
			//$scmcweight->weight = $row->fraction;
			
            if ($row->fraction > 0) {
                $scmcweight->weight = 1.0;
            } else {
                $scmcweight->weight = 0.0;
            }
			
            $scmcweight->id = $DB->insert_record('qtype_scmc_weights', $scmcweight);
			
		}
		$ignorequestion = $question->id;
	
    }

    // Create the scmc options.
    $scmc = new stdClass();
    $scmc->questionid = $question->id;
    $scmc->shuffleoptions = $multichoice->shuffleanswers;
    $scmc->numberofrows = count($rows);
    $scmc->numberofcolumns = $colmcount;
	$scmc->answernumbering = $multichoice->answernumbering;

	if ($colmcount == 1) {
		$scmc->scoringmethod = 'scmconezero';
	}else{
		$scmc->scoringmethod = 'subpoints';
	}
    $scmc->id = $DB->insert_record('qtype_scmc_options', $scmc);

    $transaction->allow_commit();
}
echo '--------------------------------------------------------------------------------' . "<br/>\n";

$endtime = time();
$used = $endtime - $starttime;
$mins = round($used / 60);
$used = ($used - ($mins * 60));

echo "<br/>\n Done\n<br/>";
echo 'Time needed: ' . $mins . ' mins and ' . $used . " secs.<br/>\n<br/>\n";

echo "Questions that were not migrated:<br/>\n";
echo " ID &nbsp;&nbsp; ,  Question Name<br/>\n";
echo "----------------------------------------<br/>\n";
foreach ($notmigrated as $question) {
    echo $question->id . ' , ' . $question->name . "<br/>\n";
}
die();
