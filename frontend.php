<?php

require_once "db_programbol.php";
//require_once "config.php";


/*
functionok:

      - function: 2025; 2026 -> 2 gomb 2026-re kattintva megjeleníti a 11.-es osztályokat;      2025 => 12.
      - A megjelenített osztályok szintén gombok => osztálynévsor betűrendben
      - osztálynévsor mellett: osztályátlag megjelenítése + tanulók nevei mellett tanulói átlag megjelenítése


*/ 
function showClasses($year)
{
      echo '<form method="post">';
      echo "<input type='hidden' name='year' value='$year'>\r\n";

      foreach (getClasses($year) as $index => $row) {
            $name=$row['name'];
            $id=$row['id'];
            echo "<input type='submit' name='className' value='$name'>\r\n";
      
            foreach (showAverageOfAClassGroupedBySubject($id) as $index => $row) {
                  $subjectName=$row['name'];
                  $avg=$row['avg'];
                  echo '<div>Tanulmányi átlag ['.$subjectName.']: '.$avg.'</div>';
            }
      }
      
      echo $year.'Top 10 tanulója<br>';
      echo '---------------------<br>';
      foreach (showTop10StudentsOfAYearWithAverage($year) as $index => $row) {
            $name=$row['name'];
            $avg=$row['avg'];
            echo 'Top '.$index.' tanuló: '.$name.' ['.$avg.']<br>';
      }
      
      echo '</form>';
}

function showStudents($year, $className)
{
      echo '<form method="post">';

      echo "<input type='hidden' name='year' value='$year'>\r\n";
      echo "<input type='hidden' name='className' value='$className'>\r\n";

      foreach (getStudentsByClassName($className) as $index => $row) {
            $name=$row['name'];
            $id=$row['id'];
            echo "<input type='submit' name='student' value='$name'>\r\n";

            foreach (showAverageOfAStudent($id) as $index => $row) {
                  $avg=$row['avg'];
                  echo '<div>Tanulmányi átlag: '.$avg.'</div>';
            }

            foreach (showAverageOfAStudentGroupedBySubject($id) as $index => $row) {
                  $subjectName=$row['name'];
                  $avg=$row['avg'];
                  echo '<div>Tanulmányi átlag ['.$subjectName.']: '.$avg.'</div>';
            }            
      }

      echo '</form>';
}

function grade() {
      echo '
      <form method="post">
            <input type="submit" name="year" value="2025">
            <input type="submit" name="year" value="2026">
      </form>
      ';
      if (isset($_POST['year'])) {
            $year=$_POST['year'];
            echo 'Kiválasztott év: '.$year;
            showClasses($year);
      } 
      if (isset($_POST['className'])) {
            $className=$_POST['className'];
            echo 'Kiválasztott osztály: '.$className.'<br>';

            foreach (showAverageOfAClass($className) as $index => $row) {
                  $avg=$row['avg'];
                  echo 'Osztályátlag: '.$avg;
            }

            showStudents($year, $className);
      }

      
      foreach (showTop10StudentsWithAverage() as $index => $row) {
            $name=$row['name'];
            $avg=$row['avg'];
            echo '<tr>Top '.$index + 1 .' tanuló: '.$name.' ['.$avg.']' . '<br></tr>';
      }
}
?>