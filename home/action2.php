<?php
$dbhost = "db.luddy.indiana.edu";
$dbuser = "i494f21_team41";
$dbpass = "my+sql=i494f21_team41";
$db = "i494f21_team41";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
if (!conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$id = $userData['oauth_uid'];
settype($id, "string");
settype($mood_select, "string");

if(isset($_post['submit']))
{
  $mood_select = $_post['mood_select']

  $query = "INSERT INTO moodLog (mood_selected, oauth_uid) Values ('$id','$mood_select')";
  mysqli_query($conn, $sql);
    mysqli_close($conn);
  }
?>
