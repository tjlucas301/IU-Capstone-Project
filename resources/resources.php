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
    <title>Resources</title>
    <link rel="stylesheet" type="text/css" href="resources.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<?php include('resources.css'); ?>
	
	
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
<main>
	<article>
		<div class="spotify">
			<section>
				<p>Motivation:</p>
				<iframe style="border-radius:12px" src="https://open.spotify.com/embed/show/1ydp02loxb8RNv280AC7SA?utm_source=generator&theme=0" width="100%" height="232" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>
			</section>
			<section>
				<p>Meditation:</p>
				<iframe style="border-radius:12px" src="https://open.spotify.com/embed/episode/4baaKuL9ozsSgrxPGENYyG?utm_source=generator&theme=0" width="100%" height="232" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>
			</section>
			<section>
				<p>Refocus:</p>
				<iframe style="border-radius:12px" src="https://open.spotify.com/embed/episode/4Qwn9pbUOKYTsefvJ4v4kf?utm_source=generator&theme=0" width="100%" height="232" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>
			</section>
		</div>
		
	<?php
	require ('../connection.php');
	$id = $userData['oauth_uid'];
	settype($id, "string");
	
	if (isset($_POST["submitNote"])) {
		$note = $_POST["note"];
		$note = str_replace("'", "\'", $note);
		$date = date("Y-m-d H:i:s");
		$sql = "INSERT INTO notes (content, curDate, userID)
		VALUES ('$note', '$date', '$id')";
		mysqli_query($conn, $sql);
		mysqli_close($conn);
	}
	
	?>
	
	<form action="resources.php" method="post">
	<h4>Personal notes:</h4>
		<h5> Write down any internal thoughts, feelings or emotions that you feel are worthy of acknowledging </h5>
		<textarea id="personal" name="note" rows="4" cols="50"> </textarea>
		<br>
		<input type="submit" name="submitNote" value="Submit note"> </input>
	</form>
	
	<?php
	$query = "SELECT curDate from notes WHERE userID = '$id'";
	$result = mysqli_query($conn, $query);
	
	if (isset($_POST["submitSelect"])) {
		$selection = $_POST["journal"];
		$sql = "SELECT content FROM notes WHERE curDate = '$selection' and userID = '$id'";
		$result2 = mysqli_query($conn, $sql);
		$display = mysqli_fetch_assoc($result2);
		echo $display["content"];
	}
	?>
	
	<form action="resources.php" method="post">	
		<h5> Select date to view past note </h5>
		<?php 
			echo "<select name='journal' id='journal'>";
			
			while ($row = mysqli_fetch_assoc($result)) {
				$name = $row['curDate'];
				echo '<option value="'.$name.'">'.substr($name, 0, 10).'</option>';
			}
			echo "</select>";
		?>
		<input type="submit" name="submitSelect" value="Retrieve note"><br>
		<br>
	</form>
	
	
	</article>	
	
    <?php
	if(isset($_POST['submit1'])) {
    $first = "SELECT description FROM alternativeAct WHERE alternativeID = 1";
    $result = mysqli_query($conn, $first);
    $row = $result->fetch_assoc();
	}
    
	if(isset($_POST['submit2'])){
    $second = "SELECT description FROM alternativeAct WHERE alternativeID = 2";
    $result = mysqli_query($conn, $second);
    $row = $result->fetch_assoc();
	}
   
	if(isset($_POST['submit3'])) {
    $third = "SELECT description FROM alternativeAct WHERE alternativeID = 3";
    $result = mysqli_query($conn, $third);
    $row = $result->fetch_assoc();
	}
    ?>
	
<form action="resources.php" method="post">
	<div class="alt">
		<h3> Try the following actions instead of vaping: </h3>
		<p> Bored at home and feel the urge to vape? <input class="option" type="submit" name="submit1" value="Try this"> </p>
		<p> Can't stop craving nicotine? <input class="option" type="submit" name="submit2" value="Try this"> </p>
		<p> Looking for something to preoccupy yourself? <input class="option" type="submit" name="submit3" value="Try this"> </p>
		<h4><?php echo $row["description"];?> </h4>
</form>
<br>
<?php
	require ('../connection.php');
	if(isset($_POST['submit4'])) {
    $first = "SELECT description FROM alternativePur WHERE alternativeID = 1";
    $result = mysqli_query($conn, $first);
    $row2 = $result->fetch_assoc();
	}
    
	if(isset($_POST['submit5'])){
    $second = "SELECT description FROM alternativePur WHERE alternativeID = 2";
    $result = mysqli_query($conn, $second);
    $row2 = $result->fetch_assoc();
	}
   
	if(isset($_POST['submit6'])) {
    $third = "SELECT description FROM alternativePur WHERE alternativeID = 3";
    $result = mysqli_query($conn, $third);
    $row2 = $result->fetch_assoc();
	}
    ?>
	
<form action="resources.php" method="post">
		<h3> Spend your money on this instead of vape products: </h3>
		<p> Save your money up and put it towards this: <input class="option" type="submit" name="submit4" value="Try this"> </p>
		<p> A month's worth of vape products?  Spend it on this instead: <input class="option" type="submit" name="submit5" value="Try this"> </p>
		<p> You could buy this every day with the money you spend on vaping: <input class="option" type="submit" name="submit6" value="Try this"> </p>
		<h4><?php echo $row2["description"];?> </h4>
<br>
</div>
</main>
</form>
</body>

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

</html>