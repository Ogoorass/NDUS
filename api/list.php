<?php 

require_once '/var/www/config/connect.php';

$conn = @new mysqli($host, $ndus_db_user, $ndus_db_passwd, $ndus_db_name);

if ($conn->connect_errno!=0)
{
    exit("Error: ".$conn->connect_errno);
}

$query_list = "SELECT * FROM `list`;";

$result_lsit = $conn->query($query_list);

if ($result_lsit) {
  echo json_encode($result_lsit->fetch_all(MYSQLI_ASSOC));
} else {
  throw new Exception("No result!");
}

?>