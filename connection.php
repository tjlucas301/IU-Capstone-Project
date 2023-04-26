<?php

// connect to database
$dbhost = "db.luddy.indiana.edu";
$dbuser = "i494f21_team41";
$dbpass = "my+sql=i494f21_team41";
$db = "i494f21_team41";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
if (!conn) {
  die("Connection failed: " . mysqli_connect_error());
}

?>
