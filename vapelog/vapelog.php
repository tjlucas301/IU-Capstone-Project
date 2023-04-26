<?php
// Include configuration file
require_once '../login_with_google_using_php/config.php';

// Include User library file
require_once '../login_with_google_using_php/User.class.php';

if(isset($_GET['code'])){
	$gClient->authenticate($_GET['code']);
	$_SESSION['token'] = $gClient->getAccessToken();
	header('Location: ' . filter_var(GOOGLE_REDIRECT_URL, FILTER_SANITIZE_URL));
}

if(isset($_SESSION['token'])){
	$gClient->setAccessToken($_SESSION['token']);
}

if($gClient->getAccessToken()){
	// Get user profile data from google
	$gpUserProfile = $google_oauthV2->userinfo->get();
	
	// Initialize User class
	$user = new User();
	
	// Getting user profile info
	$gpUserData = array();
	$gpUserData['oauth_uid']  = !empty($gpUserProfile['id'])?$gpUserProfile['id']:'';
	$gpUserData['first_name'] = !empty($gpUserProfile['given_name'])?$gpUserProfile['given_name']:'';
	$gpUserData['last_name']  = !empty($gpUserProfile['family_name'])?$gpUserProfile['family_name']:'';
	$gpUserData['email'] 	  = !empty($gpUserProfile['email'])?$gpUserProfile['email']:'';
	$gpUserData['gender'] 	  = !empty($gpUserProfile['gender'])?$gpUserProfile['gender']:'';
	$gpUserData['locale'] 	  = !empty($gpUserProfile['locale'])?$gpUserProfile['locale']:'';
	$gpUserData['picture'] 	  = !empty($gpUserProfile['picture'])?$gpUserProfile['picture']:'';
	
	// Insert or update user data to the database
    $gpUserData['oauth_provider'] = 'google';
    $userData = $user->checkUser($gpUserData);
	
	// Storing user data in the session
	$_SESSION['userData'] = $userData;
	
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
    <title>Vape Log</title>
    <link rel="stylesheet" type="text/css" href="vapelog.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <?php include('vapelog.css'); ?>
</head>

<body>

<div class="evlogo">
<img src="logo.png" class="logo" alt="EscapeVape Logo">
</div>

<div class="profile">
<?php 
if(!empty($userData)){
			$output .= '<div class="ac-data">';
			$output .= '<a href="https://cgi.luddy.indiana.edu/~team41/home/profile.php"><img src="'.$userData['picture'].'"></a>';
			$output .= '<p>Welcome, '.$userData['first_name'].'!'.'</p>';
			$output .= '<p>Logout from <a href="../login_with_google_using_php/logout.php">Google</a></p>';
			$output .= '</div>';
			echo $output;
		}else{
			$output = '<h3 style="color:red">Some problem occurred, please try again.</h3>';
			echo $output;
		}
?>
</div>

    <div class="w3-bar w3-border w3-light-grey w3-large">
			<a href="https://cgi.luddy.indiana.edu/~team41/home/home.php" class="w3-bar-item w3-button">Home</a>
			<a href="https://cgi.luddy.indiana.edu/~team41/vapelog/vapelog.php" class="w3-bar-item w3-button">Log your Vaping</a>
			<a href="https://cgi.luddy.indiana.edu/~team41/location/location.php" class="w3-bar-item w3-button">Where am I vaping?</a>
			<a href="https://cgi.luddy.indiana.edu/~team41/time/time.php" class="w3-bar-item w3-button">When am I vaping?</a>
			<a href="https://cgi.luddy.indiana.edu/~team41/resources/resources.php" class="w3-bar-item w3-button">Resources</a>
			<a href="https://cgi.luddy.indiana.edu/~team41/goals/goals.php" class="w3-bar-item w3-button">Goals</a>
		</div>
		
<div id="container">
<div class="content1">
 <h1>Log Vape</h1>
 <p> Please provide details about your vape usage below.</p>
 
<?php
require ('../connection.php');

$id = $userData['oauth_uid'];
settype($id, "string");
 
if(isset($_POST['submit1']))
{
	$date = $_POST['vapedate'];
	$num_times = $_POST['num_times'];
	$location = $_POST['location'];
	$sql = "INSERT INTO vapeLog (dateUsed, timesUsed, location, oauth_uid)
	VALUES ('$date', '$num_times', '$location', '$id')";
	mysqli_query($conn, $sql);
    mysqli_close($conn);
}
?>

<form action="vapelog.php" method="post">

<!-- vape DATE !--> 
<label for="vapedate">Vape Date: </label>
<input type="datetime-local" id="vapedate" name="vapedate" required>
<br><br>

<!-- num of TIMES !-->
<label for="num_times">Select the number of times you hit the vape during this time: </label>
<select name="num_times" id="num_times" required>
  <option value="1">1</option>
  <option value="2">2</option>
  <option value="3">3</option>
  <option value="4">4</option>
  <option value="5">5</option>
  <option value="6">6</option>
  <option value="7">7</option>
  <option value="8">8</option>
  <option value="9">9</option>
  <option value="8">10</option>
</select>
<br><br>

<p> Select the location that you hit the vape: </p> 
 <div class="options">
 <input type="radio" name='location' value="bar"> Bar
 <br>
 <input type="radio" name='location' value="class"> Class
 <br>
 <input type="radio" name='location' value="vehicle"> Vehicle
 <br>
 <input type="radio" name='location' value="home"> Home
 <br>
 <input type="radio" name='location' value="with friends/family"> With friends/family
 <br>
 <input type="radio" name='location' value="privately"> Privately 
 <br>
 <input type="radio" name='location' value="other" checked> Other 
 <br><br>
 </div>
 <br>
 <input type="submit" name="submit1" value="Submit" required>
 <br>
</form>
</div>

<div class='content2'>
 <h1>Vape Product Purchases</h1>

<form action="vapelog.php" method="post">

<label for="amtSpent">How much have you spent on vape products since you last submitted this form? ($)</label>
<br><br>
<input type="number" id="amtSpent" name="amtSpent" min="1" max="500" required>
<input type="submit" name="submit2" value="Submit" required>
<br><br>
</form>

<?php
$sql_sum = "SELECT sum(amtSpent) FROM purchaseLog WHERE oauth_uid = '$id'";
$result = mysqli_query($conn, $sql_sum);
$row = $result->fetch_assoc();
$output = "You've spent a total of $" . $row["sum(amtSpent)"] . " on vape products.";

if(isset($_POST['submit2']))
{
	$amt_spent = $_POST['amtSpent'];
	settype($amt_spent, "integer");
	$sql = "INSERT INTO purchaseLog (amtSpent, oauth_uid)
	VALUES ('$amt_spent', '$id')";
	mysqli_query($conn, $sql);
	$result = mysqli_query($conn, $sql_sum);
	$row = $result->fetch_assoc();
	$output = "You've spent a total of $" . $row["sum(amtSpent)"] . " on vape products.";
    mysqli_close($conn);
}
echo "<span style='color:#be1e2d;'>".$output."</span>";
?>
</div>
</div>

<?php
}else{
	// Get login url
	$authUrl = $gClient->createAuthUrl();
	// Render google login button
	$logo = '<img src="logo.png" alt="EscapeVape Logo"/>';
	echo "<p align=center> $logo </p>";
	
	$output = '<a href="'.filter_var($authUrl, FILTER_SANITIZE_URL).'"><img src="../login_with_google_using_php/images/google-sign-in-btn.png" alt=""/></a>';
	echo "<p align=center> $output </p>";
}
?>
<br>

</body>
</html>