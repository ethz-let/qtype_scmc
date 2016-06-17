# moodle-qtype_scmc SC/MC (ETH) question type for moodle 2.6+ to 3.x

*** Info regarding migration from qtype_multichoice to qtype_scmc ***

You should specify either the 'courseid' or the 'categoryid' parameter! Or set the parameter 'all' to 1. No migration will be done without restrictions!

Examples:
	
- Specific Course: MOODLE_URL/question/type/scmc/bin/mig_multichoice_to_scmc.php?courseid=55
- Specific Question Category: MOODLE_URL/question/type/scmc/bin/mig_multichoice_to_scmc.php?categoryid=1
- All Multi question: MOODLE_URL/question/type/scmc/bin/mig_multichoice_to_scmc.php?all=1
- DRY RUN: MOODLE_URL/question/type/scmc/bin/mig_multichoice_to_scmc.php?all=1&dryrun=1

Script should be run in SSH / Shell Command Line unless the number of questions is less than 1K to avoid interruption by browser.
