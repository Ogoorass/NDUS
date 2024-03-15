<?php

if (!isset($_GET['listID'])) {
  throw new Exception("listID not set");
}

$listID = $_GET['listID'];

$userID = "";
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
    exit("Error: ".$conn->connect_errno);
}

$user_table_questoins = $userID.'-questions';
$user_table_answers = $userID.'-answers';


$query_question = "SELECT `questions`.`question`, `{$user_table_questoins}`.`id` FROM `{$user_table_questoins}` 
INNER JOIN `questions` ON `{$user_table_questoins}`.`question_id`=`questions`.`id` 
WHERE `questions`.`list_id` = ?";


main:
// error when the table does not exists
// so create table if not exists
try {
  $stmt_question = $conn->prepare($query_question);
  $stmt_question->bind_param('i', $listID);
  if (!$stmt_question->execute()) {
    exit('question query error!'); 
  }
  $result_question = $stmt_question->get_result();
}

catch (mysqli_sql_exception $e) {

  // make sure that the error is table does not exists
  if ($e->getSqlState() != '42S02') {
    throw $e;
  }

  // create table
  $query_create = "CREATE TABLE IF NOT EXISTS `{$user_table_questoins}` (
    id int NOT NULL AUTO_INCREMENT,
    question_id int,
    PRIMARY KEY (id)
  )";

  if (!$conn->query($query_create)) {
    throw new Exception('create users database error!');
  }

  // add user to table
  $query_add_user = "INSERT INTO `users` (`name`, `expiry date`) VALUES (?, ?)";
  $stmt_add_user = $conn->prepare($query_add_user);
  $expiry_date = new DateTime();
  $expiry_date->add(new DateInterval('P30D')); // plus 30 days i guess
  $stmt_add_user->bind_param('ss', $userID, $expiry_date->format("Y-m-d"));

  if (!$stmt_add_user->execute()) {
    throw new Exception('add user query error!');
  }

  // redo excepted query
  goto main;
}

// session in not started
if ($result_question->num_rows < 1) {
  
  // so fill in the table
  $query_questions = "SELECT id FROM questions WHERE list_id =?";
  $stmt_questions = $conn->prepare($query_questions);
  $stmt_questions->bind_param("i", $listID);
  if (!$stmt_questions->execute()) {
    throw new Exception('get question query error!');
  }

  $result_questions = $stmt_questions->get_result();
  $result_arr = $result_questions->fetch_all(MYSQLI_ASSOC);
  shuffle($result_arr);

  foreach ($result_arr as $row) {
    $query_insert = "INSERT INTO `{$user_table_questoins}` (question_id) VALUES (?)";
    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bind_param('i', $row['id']);
    if (!$stmt_insert->execute()) {
      throw new Exception('insert question error!');
    }
  }

  

  //delete all answers from last session
  //if table dont exists throws an exception
  try {
    $query_del_last_session = "DELETE `{$user_table_answers}` FROM `{$user_table_answers}` 
      JOIN `questions` ON `questions`.`id` = `{$user_table_answers}`.`question_id` 
      WHERE `questions`.`list_id` = ?;";

    $stmt_del_last_session = $conn->prepare($query_del_last_session);
    $stmt_del_last_session->bind_param('i', $listID);

    if (!$stmt_del_last_session->execute()) {
      exit('delete last session answers query error!');
    }
  }
  catch (mysqli_sql_exception $e) {
    // if error code is table does not exists ignore
    if ($e->getSqlState() != '42S02') {
      throw $e;
    }
  }

  // redo question query
  goto main;
}

// success
$questions = $result_question->fetch_all(MYSQLI_ASSOC);
shuffle($questions);
$question = $questions[0];

echo json_encode($question);

?>
