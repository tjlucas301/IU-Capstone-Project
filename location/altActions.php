<!DOCTYPE html>
<html>
<body>
<h1> Alternative Actions <h1>

<?php require ('../connection.php');

$result = mysqli_query($conn, "SELECT altName, description FROM alternatives;");
$one = mysqli_fetch_array($result);
$two = mysqli_fetch_array($result);

//while ($row = mysqli_fetch_array($result)) {
//	
//	print($row['altName'] . " - " . $row['description']);
//}


?>

</body>
</html>