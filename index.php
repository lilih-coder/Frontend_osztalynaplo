<?php
session_start();

include_once "db_programbol.php";
include_once "frontend.php";

main();

function main()
{
      htmlHead();
      showMainPage();

      if (isset($_POST['creator'])) {
            createDatatbase();
            createStudentsTable();
            createSubjectsTable();
            createClassesTable();
            createMarksTable();
            createValues();
      }
}

?>