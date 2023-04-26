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
    <link rel="stylesheet" type="text/css" href="profile.css">
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<?php include('profile.css'); ?>
    <title>Profile</title>
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
	<a href="https://cgi.luddy.indiana.edu/~team41/home/goals.php" class="w3-bar-item w3-button">Goals</a>
	
</div>

<body>

<?php
$img = '<img class="img" src="'.$userData['picture'].'">';
echo $img;
?>

<div class="content">
<?php
$profile .= '<p><b>Name:</b> '.$userData['first_name'].' '.$userData['last_name'].'</p>';
$profile .= '<p><b>Email:</b> '.$userData['email'].'</p>';
$profile .= '<p><b>User ID:</b> '.$userData['oauth_uid'].'</p>';
echo $profile;
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