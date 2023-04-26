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
    <meta charset="UTF-8"/>
    <link rel="stylesheet" type="text/css" href="goals.css">
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	
	<?php include('goals.css'); ?>
    <title>Goals</title>
</head>
<!-- NO CSS HERE -->
<body>

<div class="evlogo">
<img src="logo.png" class="logo" alt="EscapeVape Logo"/>
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

<h2>S.M.A.R.T. Goals</h2>
<p> Make a S.M.A.R.T. goal to succeed on your journey! </p>

<table>
  <tr>
    <th>S. (Specific) </th>
    <th>M. (Measureable) </th>
    <th>A. (Achievable)</th>
    <th>R. (Realistic) </th>
    <th>T. (Timely) </th>
  </tr>
  <tr>
    <td>Some vaping habits may be triggered by emotions, social environments, or personal experiences. Consider developing a goal for each <span class="special">specifc</span> type of vaping.</td>
    <td>Before developing a goal, you must be honest with yourself about how often that you vape. Although 'vape less' is a goal, it cannot be referred back to once completed. Ensure your goal is <span class="special">measurable.</span></td>
    <td>Remind yourself that the goal must be <span class="special">achievable </span>so that you don't lose motivation during your journey. Consider using our Tools to help you stay on track and celebrate small wins.</td>
    <td>Verify that your goals will be attainable. You must be <span class="special">realistic </span> with what you will be able to accomplish and what may hold you back, causing you to be discouraged and giving up.</td>
    <td>Include potential dates that you would like to have some goals accomplished by. Giving yourself a <span class="special">timely </span>goal can withhold you from putting it off.</td>
  </tr>
</table>
<br>

<?php
require ('../connection.php');
	$id = $userData['oauth_uid'];
	settype($id, "string");
	
	if (isset($_POST["submitGoal"])) {
		$goal = $_POST["goal"];
		$goal = str_replace("'", "\'", $goal);
		$sql = "INSERT INTO goals (goalContent, completed, userID)
		VALUES ('$goal', 'N', '$id')";
		mysqli_query($conn, $sql);
		mysqli_close($conn);
	}

?>

<h2> Now it's your turn to apply these characteristics:</h2>
<div class="setting">
<form action="goals.php" method="post">	
    <label for="goal">Enter your S.M.A.R.T. goal(s) here: </label>
    <input type="text" id="goal" name="goal" placeholder="Your goal.."> </input>
    <input type="submit" name="submitGoal" value="Submit"> </input>
</form>

<br>
<h2> My Current Goals </h2>
<br>
<?php
	$sql = "SELECT goalContent FROM goals WHERE completed = 'N' AND userID = '$id'";
	$result = mysqli_query($conn, $sql);
	$goals = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$goals[] = $row;
	}
?>
	<form action="goals.php" method="post">
<?php
	$output;
	foreach ($goals as $each) {
		$content = $each["goalContent"];
		echo $content . "<input style='float: right;' type='radio' name='goal' value='$content'>" . '<br><br><br>';
	}
?>
	<div style="float:right;"> <input type="submit", name="completeGoal", value="Completed"/> </div> </form>
<?php
	if (isset($_POST["completeGoal"])) {
		$completed = $_POST["goal"];
		$update = "UPDATE goals SET completed = 'Y' WHERE goalContent = '$completed' AND userID = '$id'";
		mysqli_query($conn, $update);	
	}
?>
	<br>
	<br>
	<br>
	<br>
	<h2> My Completed Goals </h2>
	<br>
<?php
	$completedGoals = "SELECT goalContent FROM goals where completed = 'Y' and userID = '$id'";
	$result2 = mysqli_query($conn, $completedGoals);
	$goals2 = [];
	while ($row = mysqli_fetch_assoc($result2)) {
		$goals2[] = $row;
	}
	foreach ($goals2 as $each) {
		$content = $each["goalContent"];
		echo $content;
	}
?>	

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