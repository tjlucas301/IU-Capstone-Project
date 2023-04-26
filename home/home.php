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

		// Render user profile data
		
?>
<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8"/>
    <link rel="stylesheet" type="text/css" href="home.css">
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	
	<?php include('home.css'); ?>
    <title>Home</title>
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
<main>
    <article> 
      <div class="moodcontainer">
    	<h1> Welcome to <span class="title">Escape</span><span class="color">Vape</span> </h1>
    	<p> How do you feel today about your progress towards quitting vaping? <p>
      </div>
	  
    	<div class="imgcontainer">
			<form id = "mood_submit" action = "home.php" method="post">
			
			<div class="images">
				<section>
					<input type = "radio" id="very_happy" name="mood_select" value= "very_happy"><span> Very happy</span>
			  		<label for="very_happy"> <img src="very_happy.png"></label>
				</section>

				<section>
					<input type = "radio" id="happy" name="mood_select" value= "happy"><span> Happy</span>
					<label for="happy"> <img src="happy.png"> </label>
				</section>

				<section>
					<input type = "radio" id="neutral" name="mood_select" value= "neutral" checked><span > Neutral</span>
					<label for="neutral"> <img src="neutral.png"> </label>
				</section>

				<section>
					<input type = "radio" id="sad" name="mood_select" value= "sad"><span > Sad</span>
					<label for="sad"> <img src="sad.png"></label>
				</section>

				<section>
					<input type = "radio" id="very_sad" name="mood_select" value= "very_sad"><span > Very sad</span>
					<label for="very_sad"> <img src="very_sad.png"></label>
				</section>
			</div>
			<input type="submit" name="moodSubmit" value="Submit" style="background-color:#F2FBE0"required>
			</form>
	  	</div>
    </article>
</main>


<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>

<div style="margin: auto; width: 800px; height: 400px;">
<canvas id="canvas" width="80" height="40"> </canvas>

<?php
require ('../connection.php');
$id = $userData['oauth_uid'];
settype($id, "string");

if(isset($_POST['moodSubmit']))
{
	$moodSelected = $_POST['mood_select'];
	$sql = "INSERT INTO moodLog (moodSelected, oauth_uid)
	VALUES ('$moodSelected', '$id')";
	mysqli_query($conn, $sql);
    mysqli_close($conn);
}          
?>

<?php
$sql = "SELECT timeStamp, moodSelected FROM moodLog WHERE oauth_uid = '$id'";
$result = mysqli_query($conn, $sql);

$new_array = [];
while ($row = mysqli_fetch_assoc($result)){
	$new_array[] = $row;
}

$timeStamp = [];
$moodSelected = [];
foreach($new_array as $one){
	$moodSelected[] = $one["moodSelected"];
	$timeStamp[] = $one["timeStamp"];
}

$shortTime = [];
foreach($timeStamp as $one) {
	$shortTime[] = substr($one, 5, 5);
}

$moodKey = [];
foreach($moodSelected as $one) {
	if ($one == "very_sad") {
		$moodKey[] = -2;
	}
	elseif ($one == "sad") {
		$moodKey[] = -1;
	}
	elseif ($one == "neutral") {
		$moodKey[] = 0;
	}
	elseif ($one == "happy") {
		$moodKey[] = 1;
	}
	elseif ($one == "very_happy") {
		$moodKey[] = 2;
	}
}


?>

<script type="text/javascript">
var xArray = <?php echo json_encode($shortTime); ?>;
var yArray = <?php echo json_encode($moodKey); ?>


new Chart("canvas", {
  type: 'line',
  data: {
	  labels: xArray,
	  datasets: [{
		  data: yArray,
		  label: "Mood",
		  borderColor: "#3cba9f",
		  fill: false
	  }]
  },
  options: {
	  title: {
		  display: true,
		  text: "How I've Been Feeling Recently:",
		  fontSize: 25,
	  },
	  legend: {
		  display: false,
	  },
	  maintainAspectRatio: false,
	  scales: {
		  xAxes: [{
			  display: true,
			  scaleLabel: {
				display: true,
				labelString: 'Date',
				fontSize: 20
			  },
			ticks: {
				font: {
					size: 20,
					family: 'Georgia'
				}
			}
			}],
		  yAxes: [{
			display: true,
			scaleLabel: {
				display: true,
				labelString: 'Mood Level from Very Sad to Very Happy',
				fontSize: 17
			},
			ticks: {
				display: false,
				stepSize: 1
				
			}
		}]
		}
	}
});


</script>
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
