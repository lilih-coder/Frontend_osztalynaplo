<?php
include_once "config.php";
include_once "frontend.php";
include_once "CRUD.php";


function connectToDB($dbname = DB_NAME)
{
      $db_server = "localhost";
      $db_user = "root";
      $db_password = "";
      mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
      $conn = new mysqli($db_server, $db_user, $db_password, $dbname);

      if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
      }
      //echo "Connected successfully<br>";

      return $conn;
}

function createDatatbase($dbname = DB_NAME)
{
      $database = connectToDB("mysql");
      $database->query("DROP DATABASE IF EXISTS $dbname;");
      $database->query("CREATE DATABASE $dbname
      CHARACTER SET utf8 COLLATE utf8_hungarian_ci;");
      $database->close();
      return $database;
}

function createTable($dbname = DB_NAME, $table, $fields)
{
      $query = "CREATE TABLE IF NOT EXISTS $dbname.$table(
      $fields
      )ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;";
      $database = connectToDB("mysql");
      $database->query($query);
      $database->close();

}

function createStudentsTable()
{
      $fields = "
      id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      name varchar(255) NOT NULL,
      class_id INT NOT NULL
      ";
      createTable(DB_NAME, 'students', $fields);
}
function createSubjectsTable()
{
      $fields = "
      id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      name varchar(255) NOT NULL
      ";
      createTable(DB_NAME, 'subjects', $fields);
}
function createClassesTable()
{
      $fields = "
      id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      name varchar(255) NOT NULL,
      year INT NOT NULL
      ";
      createTable(DB_NAME, 'classes', $fields);
}
function createMarksTable()
{
      $fields = "
      id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      student_id INT NOT NULL,
      subject_id INT NOT NULL,
      mark INT NOT NULL,
      date varchar(255) NOT NULL
      ";
      createTable(DB_NAME, 'marks', $fields);
}


function insertValues($table, $fields, $values)
{
      $database = connectToDB("school");
      $database->query("INSERT INTO $table($fields) VALUES ($values)");
      $result = mysqli_affected_rows($database);
      $database->close();
      return $result;   
}

function createValues()
{
      $date = date("Y-m-d_His");

      // generate subjects
      foreach (SUBJECTS as $subject) {
            insertValues("subjects", "name", "'$subject'");
      }

      // generate classes
      foreach (CLASSES as $class) {
            $className = str_split($class, 2);
            $year = $className[0] == 11 ? "2026" : "2025";
            insertValues("classes", "name, year", "'$class','$year'");
      }
      // students + marks
      $class_id = 0;
      $student_id = 0;
      foreach (CLASSES as $class) {
            $timestamp = rand(strtotime("Jan 01 2015"), strtotime("Nov 01 2016"));
            $random_Date = date("Y-m-d", $timestamp);
            $class_id++;
            $studentCount = rand(10, 15);
            for ($i = 0; $i < $studentCount; $i++) {
                  $student_id++;
                  $lastName = NAMES['lastnames'][rand(0, count(NAMES['lastnames']) - 1)];
                  $gender = rand(1, 2) == 1 ? "men" : "women";
                  $firstName = NAMES['firstnames'][$gender][rand(0, count(NAMES['firstnames'][$gender]) - 1)];
                  InsertValues("students", "name, class_id", "'$lastName $firstName', $class_id");
                  for ($j = 1; $j < count(SUBJECTS) + 1; $j++) {
                        // $gradesCount = rand(3, 5);
                        for ($k = 0; $k < rand(3, 5); $k++) {
                              $date = str_split($class, 2)[0] == 11 ? date("Y-m-d", rand(strtotime("Sep 01 2023"), strtotime("June 15 2024"))) : date("Y-m-d", rand(strtotime("Sep 01 2024"), strtotime("June 15 2025")));
                              $mark = rand(1, 5);
                              InsertValues("marks", "student_id, subject_id, mark, date", "$student_id, $j, $mark, '$date'");
                        }
                  }
            }
      }
}

function query($query)
{
      $database = connectToDB("mysql");
      $result = $database->query($query);
      $database->close();
      return $result;
}

function getAllClasses()
{
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $result = $database->query("select * from $dbName.classes");
      $database->close();

      return $result;
}

function getAllStudents()
{
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $result = $database->query("select * from $dbName.students");
      $database->close();

      return $result;
}



function getClasses($year)
{
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $result = $database->query("select * from $dbName.classes where year = $year");
      $database->close();

      return $result;
}

function saveClassToDB($id, $name, $year)
{
      if ($id) {
            $dbName = DB_NAME;
            $database = connectToDB("mysql");
            $database->query("update $dbName.classes set name='$name', year='$year' where id = $id");
            $result = mysqli_affected_rows($database);
            $database->close();

            return $result; 
      }
      else {
            return insertValues("classes", "name, year", "'$name','$year'");
      }
}

function saveStudentToDB($id, $name, $class_id)
{
      $dbName = DB_NAME;
      $database = connectToDB("mysql");

      if ($id === null) {

            $database->query("insert into $dbName.students (name, class_id) values (?, ?)");
            $result = mysqli_affected_rows($database);
            $database->close();

            return $result;
     
      } else {
            $database->query("update $dbName.students set name='$name', class_id='$class_id' where id = $id");
            $result = mysqli_affected_rows($database);
            $database->close();

            return $result; 
      }
      
      return $result;
}

function deleteClassFromDB($id){
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $database->query("delete from $dbName.classes where id = $id");
      $result = mysqli_affected_rows($database);
      $database->close();

      return $result;
}

function deleteStudentFromDB($id){
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $database->query("delete from $dbName.students where id = $id");
      $result = mysqli_affected_rows($database);
      $database->close();

      return $result;
}

function getStudentsByClassName($className)
{
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $result = $database->query("
            select * 
            from $dbName.classes 
            inner join $dbName.students on $dbName.classes.id = $dbName.students.class_id  
            where $dbName.classes.name = '$className'
            order by $dbName.students.name ASC");

      $database->close();

      return $result;
}

function showAverageOfAClass($className)
{
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $result = $database->query("
            select ROUND(avg($dbName.marks.mark),2) as avg
            from $dbName.classes 
            inner join $dbName.students on $dbName.classes.id = $dbName.students.class_id  
            inner join $dbName.marks on $dbName.marks.student_id = $dbName.students.id  
            where $dbName.classes.name = '$className'");

      $database->close();

      return $result;
}


function showAverageOfAStudent($studentId)
{
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $result = $database->query("
            select ROUND(avg($dbName.marks.mark),2) as avg
            from $dbName.students 
            inner join $dbName.marks on $dbName.marks.student_id = $dbName.students.id  
            where $dbName.students.id = $studentId");

      $database->close();

      return $result;
}


function showAverageOfAStudentGroupedBySubject($studentId)
{
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $result = $database->query("
            select $dbName.marks.subject_id, $dbName.subjects.name, ROUND(avg($dbName.marks.mark),2) as avg
            from $dbName.students 
            inner join $dbName.marks on $dbName.marks.student_id = $dbName.students.id  
            inner join $dbName.subjects on $dbName.marks.subject_id = $dbName.subjects.id  
            where $dbName.students.id = $studentId
            group by $dbName.marks.subject_id, $dbName.subjects.name");

      $database->close();

      return $result;
}


function showAverageOfAClassGroupedBySubject($classId)
{
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $result = $database->query("
            select $dbName.marks.subject_id, $dbName.subjects.name, ROUND(avg($dbName.marks.mark),2) as avg
            from $dbName.classes 
            inner join $dbName.students on $dbName.classes.id = $dbName.students.class_id  
            inner join $dbName.marks on $dbName.marks.student_id = $dbName.students.id  
            inner join $dbName.subjects on $dbName.marks.subject_id = $dbName.subjects.id  
            where $dbName.classes.id = '$classId'
            group by $dbName.marks.subject_id, $dbName.subjects.name
            ");

      $database->close();

      return $result;
}


function showTop10StudentsOfAYearWithAverage($year)
{
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $result = $database->query("
            select $dbName.students.id, $dbName.students.name, ROUND(avg($dbName.marks.mark),2) as avg
            from $dbName.students 
            inner join $dbName.classes on $dbName.students.class_id = $dbName.classes.id
            inner join $dbName.marks on $dbName.marks.student_id = $dbName.students.id  
            where $dbName.classes.year = $year 
            group by $dbName.students.id, $dbName.students.name
            order by avg DESC
            LIMIT 10
            ");

      $database->close();

      return $result;
}


function showTop10StudentsWithAverage()
{
      $dbName = DB_NAME;
      $database = connectToDB("mysql");
      $result = $database->query("
            select $dbName.students.id, $dbName.students.name, ROUND(avg($dbName.marks.mark),2) as avg
            from $dbName.students 
            inner join $dbName.marks on $dbName.marks.student_id = $dbName.students.id  
            group by $dbName.students.id, $dbName.students.name
            order by avg DESC
            LIMIT 10
            ");

      $database->close();

      return $result;
}





function dd()
{
      foreach (func_get_args() as $x) {
            var_dump($x);
      }
      die;
}

?>