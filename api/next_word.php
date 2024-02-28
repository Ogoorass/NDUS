<?php


if (!isset($_GET['listID'])) {
  exit(1);
}

$listID = $_GET['listID'];


// check for cookie userID
if (isset($_COOKIE['userID']) && strlen($_COOKIE['userID']) == 13) {
  $userID = $_COOKIE['userID'];
} else {
  $userID = uniqid();  
  setcookie('userID', $userID, time()+86400*30 /* 30 days */, '/NDUS');
} 

require_once '/var/www/config/connect.php';

$conn = @new mysqli($host, $ndus_db_user, $ndus_db_passwd, $ndus_db_name);

if ($conn->connect_errno!=0)
{
    echo "Error: ".$conn->connect_errno;
    exit(1);
}



$query_question = "SELECT `questions`.`question` FROM `{$userID}` 
INNER JOIN `questions` ON `{$userID}`.`question_id`=`questions`.`id` 
WHERE `questions`.`id_list` = ?
ORDER BY `{$userID}`.`question_order` ASC LIMIT 1";


 
// error when the table does not exists
try {
  $stmt_question = $conn->prepare($query_question);
  $stmt_question->bind_param('i', $listID);
  if (!$stmt_question->execute()) {
    echo 'something went wrong';
    exit(1); 
  }
  $result_question = $stmt_question->get_result();
}

catch (mysqli_sql_exception $e) {
  
  // create table
  $query = "CREATE TABLE IF NOT EXISTS `{$userID}` AS SELECT * FROM user_template";
  if (!$conn->query($query)) {
    echo 'something went wrong';
    exit(1);
  }

  $query = "ALTER TABLE `{$userID}` ADD PRIMARY KEY (id);";
  if (!$conn->query($query)) {
    echo 'something went wrong';
    exit(1);
  }

  $query = "ALTER TABLE `{$userID}` MODIFY COLUMN id INT AUTO_INCREMENT";
  if (!$conn->query($query)) {
    echo 'something went wrong';
    exit(1);
  }

  $stmt_question = $conn->prepare($query_question);
  $stmt_question->bind_param('i', $listID);
  if(!$stmt_question->execute()) {
    echo 'something went wrong';
    exit(1);
  }
  $result_question = $stmt_question->get_result();
}

if ($result_question->num_rows < 1) {

  // no result so the session in not started
  // so fill in the table
  $query = "SELECT id FROM questions WHERE id_list =?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $listID);
  if (!$stmt->execute()) {
    echo 'something wnt wrong';
    exit(1);
  }

  $result = $stmt->get_result();
  $result_arr = $result->fetch_all(MYSQLI_ASSOC);
  shuffle($result_arr);
  $order = 0;

  foreach ($result_arr as $row) {
    $query = "INSERT INTO `{$userID}` (question_id, question_order) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $row['id'], $order);
    if (!$stmt->execute()) {
      echo 'something went wrong';
      exit(1);
    }
    $order+=1;
  }

  if (!$stmt_question->execute()) {
    echo 'something went wrong';
    exit(1); 
  }

  $result_question = $stmt_question->get_result();
}



// success
$question = $result_question->fetch_all(MYSQLI_ASSOC)[0]['question'];
echo $question;



exit(0);

/*

if ($stmt->execute())
{
  echo 'executing sql<br>';

  $result = $stmt->get_result();
    
  $howmany = $result->num_rows;
  if ($howmany > 0)
  {
     echo "1";
  }
  else
  {
    echo 'database error<br>';
  }


}
else
{
    echo "database error";
}

$conn->close();
// userID is database id for user's session
// there will be one database for each userID containing all the session of the user
//
// check if session is active
// start session if necessary
//
// output lates word
 */
?>
