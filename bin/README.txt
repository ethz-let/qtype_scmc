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

Sobald dryrun nicht angegeben wird (oder auf 0 gesetzt wird), wird die
Migration durchgeführt. Da keine Fragen gelöscht werden, kann die
Migration beliebig oft wiederholt werden. Es werden dann immer wieder
neue scmc Fragen hinzugefügt.

Die Bewertungsmethoden werden wiefolgt migriert:

    multichoice Methode       scmc Methode
       'all'         =>   'subpoints'
       'kany'        =>   'scmc'
       'scmc'      =>   'scmconezero'

 default :  'scmc'
