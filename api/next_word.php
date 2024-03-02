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

$user_table_questoins = $userID.'-questions';
$user_table_answers = $userID.'-answers';


$query_question = "SELECT `questions`.`question`, `{$user_table_questoins}`.`id` FROM `{$user_table_questoins}` 
INNER JOIN `questions` ON `{$user_table_questoins}`.`question_id`=`questions`.`id` 
WHERE `questions`.`list_id` = ?";


 
// error when the table does not exists
// so create table if not exists
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
  $query = "CREATE TABLE IF NOT EXISTS `{$user_table_questoins}` (
    id int NOT NULL AUTO_INCREMENT,
    question_id int,
    PRIMARY KEY (id)
  )";
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
  $query = "SELECT id FROM questions WHERE list_id =?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $listID);
  if (!$stmt->execute()) {
    echo 'something wnt wrong';
    exit(1);
  }

  $result = $stmt->get_result();
  $result_arr = $result->fetch_all(MYSQLI_ASSOC);
  shuffle($result_arr);

  foreach ($result_arr as $row) {
    $query = "INSERT INTO `{$user_table_questoins}` (question_id) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $row['id']);
    if (!$stmt->execute()) {
      echo 'something went wrong';
      exit(1);
    }
  }

  if (!$stmt_question->execute()) {
    echo 'something went wrong';
    exit(1); 
  }

  $result_question = $stmt_question->get_result();

  //delete all answers from last session
  $query_del_last_session = "DELETE `{$user_table_answers}` FROM `{$user_table_answers}` 
    JOIN `questions` ON `questions`.`id` = `{$user_table_answers}`.`question_id` 
    WHERE `questions`.`list_id` = ?;";

  $stmt_del_last_session = $conn->prepare($query_del_last_session);
  $stmt_del_last_session->bind_param('i', $listID);

  if (!$stmt_del_last_session->execute()) {
    echo 'delete last session answers query error!';
    exit(1);
  }
}


// success
$questions = $result_question->fetch_all(MYSQLI_ASSOC);
shuffle($questions);
$question = $questions[0];

echo json_encode($question);


?>
