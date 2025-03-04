<?php

require_once "db_programbol.php";
//require_once "config.php";


/*
functionok:

      - function: 2025; 2026 -> 2 gomb 2026-re kattintva megjeleníti a 11.-es osztályokat;      2025 => 12.
      - A megjelenített osztályok szintén gombok => osztálynévsor betűrendben
      - osztálynévsor mellett: osztályátlag megjelenítése + tanulók nevei mellett tanulói átlag megjelenítése


*/ 


function htmlHead()
{
      echo '
    <!DOCTYPE html>
    <html lang="hu">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Osztálynapló</title>
    <link rel="stylesheet" href="db_programbol.css">
    </head>
    <body>';

}

function showMainPage()
{
      echo '
      <form method="post">
            <input type="submit" name="creator" value="dbCreator" >
      </form>    
      ';
      openAdminPage();
      if (!isset($_POST['admin'])) {
            openGradePage();
      }

}

function showClasses($year)
{
      echo '<table>';
      echo '<form method="post">';
      echo "<input type='hidden' name='year' value='$year'>\r\n";

      foreach (getClasses($year) as $index => $row) {
            $name=$row['name'];
            $id=$row['id'];
            echo "<tr><th colspan='2'><input type='submit' name='className' value='$name'>\r\n</th></tr>";
      
            foreach (showAverageOfAClassGroupedBySubject($id) as $index => $row) {
                  $subjectName=$row['name'];
                  $avg=$row['avg'];
                  echo '<tr><td style="text-align: left;"><div> '.$subjectName.': </td><td>'.$avg.'</div></td></tr>';
            }
      }
      echo '</table>';
      echo '<table>';
      echo '<tr><th colspan="3">' . $year.' Top 10 tanulója<br></th></tr>';
      //echo '---------------------<br>';
      foreach (showTop10StudentsOfAYearWithAverage($year) as $index => $row) {
            $name=$row['name'];
            $avg=$row['avg'];
            echo '<tr><td style="text-align: left;">'.$index+1 .'.</td><td style="text-align: left;"> '.$name.' </td><td>'.$avg.'<br></td></tr>';
      }
      
      echo '</form>';
      echo '<table>';
}

function showStudents($year, $className)
{
      echo'<table>';
      echo '<form method="post">';

      echo "<input type='hidden' name='year' value='$year'>\r\n";
      echo "<input type='hidden' name='className' value='$className'>\r\n";

      foreach (getStudentsByClassName($className) as $index => $row) {
            $name=$row['name'];
            $id=$row['id'];
            echo "<tr><th colspan='2'><input type='submit' name='student' value='$name'>\r\n</th></tr>";

            foreach (showAverageOfAStudent($id) as $index => $row) {
                  $avg=$row['avg'];
                  echo '<tr><td colspan="2"><div>Tanulmányi átlag: '.$avg.'</div></tr></td>';
            }

            foreach (showAverageOfAStudentGroupedBySubject($id) as $index => $row) {
                  $subjectName=$row['name'];
                  $avg=$row['avg'];
                  echo '<tr><td style="text-align: left;"><div>'.$subjectName.'</td><td> '.$avg.'</div></div></tr>';
            }            
      }

      echo '</form>';
      echo'</table>';
}

function openGradePage() {
      echo '
      <form method="post">
            <input type="submit" name="year" value="2025">
            <input type="submit" name="year" value="2026">
      </form>
      ';
      if (isset($_POST['year'])) {
            $year=$_POST['year'];
            echo '<table><tr><th>Kiválasztott év: '.$year . '<br></th></tr></table>';
            showClasses($year);
      } 
      if (isset($_POST['className'])) {
            $className=$_POST['className'];
            echo '<table><tr><th>Kiválasztott osztály: '.$className.'<br></th></tr>';

            foreach (showAverageOfAClass($className) as $index => $row) {
                  $avg=$row['avg'];
                  echo '<tr><th>Osztályátlag: '.$avg . '</th></tr>';
            }

            showStudents($year, $className);

            echo'<table>';
            echo'<tr><th colspan="3">A '.$className.' osztály Top 10 tanulója</th></tr>';
            foreach (showTop10StudentsWithAverage() as $index => $row) {
                  $name=$row['name'];
                  $avg=$row['avg'];
                  echo '<tr><td style="text-align: left;">' . $index + 1 .'. </td><td style="text-align: left;">'.$name.'</td><td> '.$avg.'' . '<br></td></tr>';
            }
            echo'</table>';
      }

      
}

?>