<?php
include_once "db_programbol.php";
include_once "frontend.php";

/*
1. Admin oldal
2. Választó: évek, osztályok, tantárgyak, diákok, osztályzatok
3. Választás után: új; módosítás; törlés;
4.    - Új: sor beszúrása a kiválasztott részhez
      - Módosítás: 
      - Törlés: 
*/

function openAdminPage()
{
      echo '<form method="post">';
      if (!isset($_POST['admin'])) {
            echo "<input type='submit' name='admin' value='Admin'>\r\n";
      }
      if (isset($_POST['admin'])) {
            echo "<input type='submit' name='' value='Főoldal'>\r";
      }
      echo '</form>';

      if (isset($_POST['admin'])) {
            showCRUDButtons();
      }

      if (isset($_POST['saveClass'])) {
            saveClass();
      }

      if (isset($_POST['saveStudent'])) {
            saveStudent();
      }

      if (isset($_POST['deleteClassConfirmed'])) {
            deleteClass();
      }

      if (isset($_POST['deleteStudentConfirmed'])) {
            deleteStudent();
      }

      if (isset($_POST['editYearTable'])) {
            showYearTable();
      }

      if (isset($_POST['editClass'])) {
            showClassEditor();
      }

      if (isset($_POST['deleteClassConfirm'])) {
            showDeleteClassConfirm();
      }    

      if (isset($_POST['editStudentTable'])) {
            showStudentTable();
      }

      if (isset($_POST['submitShowClass'])) {
            showStudentTable();
            showSelectedClassStudents();
      }

      if (isset($_POST['editStudent'])) {
            showStudentEditor();
      }

      if (isset($_POST['deleteStudent'])) {
            showDeleteStudentConfirm();
      }

}

function showCRUDButtons()
{
      echo '<form method="post">';
      echo "<input type='hidden' name='admin' value='Admin'>\r\n";

      echo "<input type='submit' name='editYearTable' value='Évfolyam/Osztály'>\r";
      echo "<input type='submit' name='editStudentTable' value='Diák'>\r";
      echo "<input type='submit' name='editSubjectTable' value='Tantárgy'>\r\n";
      echo "<input type='submit' name='editMarkTable' value='Jegy'>\r\n";

      echo '</form>';
}

function showYearTable()
{
      echo "
      <form method='post'>
            <input type='hidden' name='admin' value='Admin'>
            <input type='submit' name='editClass' value='Új'>
      </form> 
      ";

      echo '<table>';
      echo "<tr><th><p>Osztály</p></th><th><p>Évfolyam</p></th><th><p>Művelet</p></th></tr>";

      foreach (getAllClasses() as $index => $row) {
            $name = $row['name'];
            $id = $row['id'];
            $year = $row['year'];
            echo "<tr>
                  <td>$name</td>
                  <td>$year</td>
                  <td><form method='post'>
                  <input type='hidden' name='admin' value='Admin'>
                  <input type='hidden' name='id' value='$id'>
                  <input type='hidden' name='year' value='$year'>
                  <input type='hidden' name='name' value='$name'>
                  <input type='submit' name='editClass' value='Módosít'>
                  </form> / 
                  <form method='post'>
                  <input type='hidden' name='admin' value='Admin'>
                  <input type='hidden' name='id' value='$id'>
                  <input type='hidden' name='year' value='$year'>
                  <input type='hidden' name='name' value='$name'>
                  <input type='submit' name='deleteClassConfirm' value='Töröl'>
                  </form></td>
            </tr>";
      }

      echo '</table>';
}

function showStudentTable()
{
      echo "
            <form method='post'>
            <input type='hidden' name='admin' value='Admin'>            
            </form> 
      ";

      $classes = [];
      foreach (getAllStudents() as $index => $row) {
            $id = $row['id'];
            $name = $row['name'];
            $classes[] = $row['class_id'];

      }
      $uniqueClasses = array_values(array_unique($classes));

      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $placeholders = implode(',', array_fill(0, count($uniqueClasses), '?'));

      $query = "select id, name from $dbName.classes where id in ($placeholders)";
      $stmt = $database->prepare($query);
      $types = str_repeat('i', count($uniqueClasses));
      $stmt->bind_param($types, ...$uniqueClasses);

      $stmt->execute();

      $result = $stmt->get_result();
      $results = $result->fetch_all(MYSQLI_ASSOC);

      echo "
            <form method='post'>
                  <input type='hidden' name='admin' value='Admin'>
                  <label for='classes'>Válasszon osztályt:</label>
                  <select name='selectClass' id='classes>";

      foreach ($results as $row) {
            $id = $row['id'];
            $name = $row['name'];
            echo "<option value='$id'>$name</option>";
      }

      echo "
                  </select>
                  <input type='submit' name='submitShowClass' value='Kiválaszt'>
            </form>
      ";

}

function showSelectedClassStudents()
{  
      if (isset($_POST['submitShowClass']) && isset($_POST['selectClass'])) {
            $selectedClass = $_POST['selectClass'];

            $dbName = DB_NAME;
            $database = connectToDB("mysql");

            $query = "select id, name from $dbName.students where class_id = ?";
            $stmt = $database->prepare($query);
            $stmt->bind_param('i', $selectedClass);
            $stmt->execute();

            $result = $stmt->get_result();
            $students = $result->fetch_all(MYSQLI_ASSOC);

            if (!empty($students)) {
                  echo '<form method="post">';
                  echo "<input type='hidden' name='admin' value='Admin'>\r\n";
                  echo "<input type='submit' name='editStudent' value='Új tanuló hozzáadása'>";
                  echo '</form>';
                  echo '<h2>A kiválasztott osztály tanulói:</h2>';
                  echo '<table>';
                  foreach ($students as $student) {
                        $id = $student['id'];
                        $name = $student['name'];
                        echo "<tr>
                              <td style='text-align: left;'>$name</td>  
                              <td>
                                    <form method='post'>
                                    <input type='hidden' name='admin' value='Admin'>
                                    <input type='hidden' name='id' value='$id'>
                                    <input type='hidden' name='name' value='$name'>
                                    <input type='submit' name='editStudent' value='Módosít'>
                                    </form>
                                    <p> / </p>
                                    <form method='post'>
                                    <input type='hidden' name='admin' value='Admin'>
                                    <input type='hidden' name='id' value='$id'>
                                    <input type='hidden' name='name' value='$name'>
                                    <input type='submit' name='deleteStudent' value='Töröl'>
                                    </form>
                              </td>
                              </tr>";
                  }
                  echo '</table>';

            }
      }
}

function showClassEditor()
{
      if (isset($_POST['id'])) {
            $id = $_POST['id'];
      } else {
            $id = '';
      }
      if (isset($_POST['year'])) {
            $year = $_POST['year'];
      } else {
            $year = '2025';
      }
      if (isset($_POST['name'])) {
            $name = $_POST['name'];
      } else {
            $name = '';
      }
      echo "
      <form method='post'>
      <input type='hidden' name='admin' value='Admin'>
      <input type='hidden' name='editYearTable' value='Évfolyam/Osztály'>
      
      <input type='hidden' name='id' value='$id'>
      <table>                 
            <tr><td>Osztály</td><td><input type='text' name='name' value='$name'></td></tr>
            <tr><td>Évfolyam</td><td><input type='text' name='year' value='$year'></td></tr>

      </table>
      <input type='submit' name='saveClass' value='Mentés'>
      </form>
      ";
}

function showStudentEditor(){
      if (isset($_POST['id'])) {
            $id = $_POST['id'];
      } else {
            $id = '';
      }
      if (isset($_POST['name'])) {
            $name = $_POST['name'];
      } else {
            $name = '';
      }
      echo "
      <form method='post'>
      <input type='hidden' name='admin' value='Admin'>
      <input type='hidden' name='editStudentTable' value='Diák'>
      
      <input type='hidden' name='id' value='$id'>
      <table>                 
            <tr><td>Tanuló</td><td><input type='text' name='name' value='$name'></td></tr>
      </table>
      <input type='submit' name='saveStudent' value='Mentés'>
      </form>
      ";
}

function showDeleteClassConfirm()
{
      $id = $_POST['id'];
      $year = $_POST['year'];
      $name = $_POST['name'];

      echo "
      <form method='post'>
      
      <h3>Biztos benne?</h3>
      <input type='hidden' name='admin' value='Admin'>
      <input type='hidden' name='editYearTable' value='Évfolyam/Osztály'>
      
      <input type='hidden' name='id' value='$id'>
      <table>                 
            <tr><td>Osztály</td><td>$name</td></tr>
            <tr><td>Évfolyam</td><td>$year</td></tr>
      </table>
      <input type='submit' name='deleteClassConfirmed' value='Igen'>
      <input type='submit' name='editYearTable' value='Mégsem'>
      </form>
      ";

}

function showDeleteStudentConfirm()
{
      $id = $_POST['id'];
      $name = $_POST['name'];

      echo "
      <form method='post'>
      
      <h3>Biztos benne?</h3>
      <input type='hidden' name='admin' value='Admin'>
      <input type='hidden' name='editStudentTable' value='Diák'>
      
      <input type='hidden' name='id' value='$id'>
      <input type='hidden' name='name' value='$name'>
      <table>                 
            <tr><td>Tanuló</td><td>$name</td></tr>
      </table>
      <input type='submit' name='deleteStudentConfirmed' value='Igen'>
      <input type='submit' name='cancelStudentDelete' value='Mégsem'>
      </form>
      ";


}

function showNewStudentInserter(){
      echo"Semmi";
}     
function saveClass()
{
      $id = $_POST["id"];
      $year = $_POST["year"];
      $name = $_POST["name"];
      $result = saveClassToDB($id, $name, $year);
      if ($result == 0) {
            showErrorMessage("A mentés nem sikerült!");
      }
}

function saveStudent()
{
      $id = $_POST["id"];
      $name = $_POST["name"];
      $result = saveStudentToDB($id, $name, );
      if ($result == 0) {
            showErrorMessage("A mentés nem sikerült!");
      }
}


function deleteClass()
{
      $id = $_POST["id"];
      $result = deleteClassFromDB($id);
      if ($result == 0) {
            showErrorMessage("A törlés nem sikerült!");
      }
}

function deleteStudent()
{
      $id = $_POST["id"];
      $result = deleteStudentFromDB($id);
      if ($result == 0) {
            showErrorMessage("A törlés nem sikerült!");
      }
}

function showErrorMessage(string $message)
{
      echo "<h2>$message</h2>";

}

function showOptionButtons()
{
      echo "<input type='submit' name='new' value='Új'>\r";
}

function showNewStudentButton(){
      echo "<input type='submit' name='editStudent' value='Új tanuló hozzáadása'>";
}



?>