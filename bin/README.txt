Das Skript mig_multichoice_to_scmc.php migriert alte ETHZ multichoice Fragen in
den neuen Fragentyp qtype_scmc. Es werden keine Fragen überschrieben
oder gelöscht, sondern immer nur neue Fragen erstellt. Es werden nur
multichoice Fragen migriert, die höchstens vier Optionen und höchstens 2
Anworten haben.

Nur Website-Administratoren dürfen das Skript ausführen. 

Das Skript akzeptiert folgende Parameter in der URL:

 - courseid : Die Moodle ID des Kurses, auf den die Migration
   eingeschränkt werden soll. Default 0, d.h. keine Einschränkung.

 - categoryid: Die Moodle ID der Fragen-Kategory, auf den die Migration
   eingeschränkt werden soll. Default 0, d.h. keine Einschränkung.

 - dryrun: Wenn 1, dann werden keine neuen Fragen erstellt. Es wird nur
   Information über die zu migrierenden Fragen ausgegeben. Default 0.

 - all: Wenn 1, dann werden alle Fragen der Plattform migriert, ohne
   Einschränkungen.  Default 0.

Ein Aufruf geschieht dann in einem Browser z.B. wiefolgt:
   <URL zum Moodle>/question/type/scmc/bin/mig_multichoice_to_scmc.php?courseid=12345&dryrun=1
oder 
   <URL zum Moodle>/question/type/scmc/bin/mig_multichoice_to_scmc.php?categoryid=56789&dryrun=1
   
** ENGLISH **
You should specify either the 'courseid' or the 'categoryid' parameter! Or set the parameter 'all' to 1. No migration will be done without restrictions!


Examples:
	
    Specific Course: MOODLE_URL/question/type/scmc/bin/mig_multichoice_to_scmc.php?courseid=55
    Specific Question Category: MOODLE_URL/question/type/scmc/bin/mig_multichoice_to_scmc.php?categoryid=1
    All Multi question: MOODLE_URL/question/type/scmc/bin/mig_multichoice_to_scmc.php?all=1
	DRY RUN: MOODLE_URL/question/type/scmc/bin/mig_multichoice_to_scmc.php?all=1&dryrun=1
