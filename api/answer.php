<?php

if (
  !isset($_POST['answer']) ||
  !isset($_POST['userQuestionID']) ||
  !isset($_COOKIE['userID'])
  ) {
  exit(1);
}

$answer = $_POST['answer'];
$user_question_id = $_POST['userQuestionID'];
$userID = $_COOKIE['userID'];


require_once '/var/www/config/connect.php';

$conn = @new mysqli($host, $ndus_db_user, $ndus_db_passwd, $ndus_db_name);

if ($conn->connect_errno!=0)
{
  echo "Error: ".$conn->connect_errno;
  exit(1);
}

$user_table_questoins = $userID.'-questions';
$user_table_answers = $userID.'-answers';

// get list id
$query_list_id = "SELECT `questions`.`list_id` FROM `{$user_table_questoins}` 
  JOIN `questions` ON `questions`.`id` = `{$user_table_questoins}`.`question_id` 
  WHERE `{$user_table_questoins}`.`id` = ?;";

$stmt_list_id = $conn->prepare($query_list_id);
$stmt_list_id->bind_param('i', $user_question_id);

if (!$stmt_list_id->execute()) {
  echo 'list id query error!';
  exit(1);
}

$result_list_id = $stmt_list_id->get_result();
$listID = $result_list_id->fetch_assoc()['list_id'];


// check answer
$query_answer = "SELECT `questions`.`answer` FROM `{$user_table_questoins}` 
INNER JOIN `questions` ON  `questions`.`id` = `{$user_table_questoins}`.`question_id` 
WHERE `questions`.`answer` = ?";

$stmt_answer = $conn->prepare($query_answer);
$stmt_answer->bind_param('s', $answer);


if(!$stmt_answer->execute()) {
  echo 'something went wrong!';
  exit(1);
}

$result_answer = $stmt_answer->get_result();

if ($result_answer->num_rows > 0) {
  // if correct delete question from questions

  $status = TRUE;
  $query_delete = "DELETE FROM `{$user_table_questoins}` WHERE id = ?";
  $stmt_del = $conn->prepare($query_delete);
  $stmt_del->bind_param('i', $user_question_id);

  if (!$stmt_del->execute()) {
    echo 'something went wrong!';
    exit(1);
  }


} else {
  // if incorrect add attampt to answers

  $status = FALSE;

  $query_question_id = "SELECT `questions`.`id` FROM `{$user_table_questoins}` 
  INNER JOIN `questions` ON `questions`.`id` = `{$user_table_questoins}`.`question_id` 
  WHERE `{$user_table_questoins}`.`id` = ?";

  $stmt_question_id = $conn->prepare($query_question_id);
  $stmt_question_id->bind_param('i', $user_question_id);

  if (!$stmt_question_id->execute()) {
    echo 'question id query error';
    exit(1);
  }

  $result_question_id = $stmt_question_id->get_result();
  $question_id = $result_question_id->fetch_assoc()['id'];

  $query_attempt = "INSERT INTO `{$user_table_answers}` (question_id, answer) VALUES (?, ?)";

  try {
    $stmt_attempt = $conn->prepare($query_attempt);
    $stmt_attempt->bind_param('is', $question_id, $answer);
    if (!$stmt_attempt->execute()) {
      echo 'something went wrong!';
      exit(1);
    }
  }
  catch (mysqli_sql_exception $e) {
    // table doesn't exists
    $query_table_answer = "CREATE TABLE IF NOT EXISTS `{$user_table_answers}` (
      id int NOT NULL AUTO_INCREMENT,
      question_id int,
      answer text,
      PRIMARY KEY (id)
    )";

    if (!$conn->query($query_table_answer)) {
      echo 'answer table create error!';
      exit(1);
    }

    $stmt_attempt = $conn->prepare($query_attempt);
    $stmt_attempt->bind_param('is', $user_question_id, $answer);

    if (!$stmt_attempt->execute()) {
      echo 'answer attempt error';
      exit(1);
    }

  }
}


// check count of remaining questions 
$query_remaining = "SELECT COUNT(`{$user_table_questoins}`.`id`) AS 'count' FROM `{$user_table_questoins}` 
  JOIN `questions` ON `questions`.`id` = `{$user_table_questoins}`.`question_id`
  WHERE `questions`.`list_id` = ?";

$stmt_remaining = $conn->prepare($query_remaining);
$stmt_remaining->bind_param('i', $listID);

if (!$stmt_remaining->execute()) {
  echo 'remaining query error';
  exit(1);
}
$result_remaining = $stmt_remaining->get_result();

$left = $result_remaining->fetch_assoc()['count'];

$all_answers = NULL;
if ($left == 0) {
  // get all attempted answers
  $query_all_attempts = 
  "SELECT 
    `questions`.`question` AS 'question', 
    `{$user_table_answers}`.`answer` AS 'bad', 
    `questions`.`answer` AS 'good' 
  FROM `{$user_table_answers}` JOIN `questions` ON `questions`.`id` = `{$user_table_answers}`.`question_id`
  WHERE `questions`.`list_id` = ?";

  $stmt_all_attempts = $conn->prepare($query_all_attempts);
  $stmt_all_attempts->bind_param('i', $listID);

  if (!$stmt_all_attempts->execute()) {
    echo 'all attempts query error!';
    exit(1);
  }

  $result_all_attempts = $stmt_all_attempts->get_result();
  $all_answers = $result_all_attempts->fetch_all(MYSQLI_ASSOC);
}


echo json_encode([
    'isAnswerGood' => $status,
    'left' => $left,
    'last_answer' => $answer,
    'all_answers' => $all_answers
  ]);
?>