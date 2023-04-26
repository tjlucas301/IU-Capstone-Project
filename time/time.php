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
    <title>Time</title>
	<link rel="stylesheet" type="text/css" href="time.css">
	<link rel="stylesheet" href="../css/chartstyle.css">
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">

	<?php include('time.css'); ?>
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

<?php
require ('../connection.php');
	$id = $userData['oauth_uid'];
	settype($id, "string");
	$query = "SELECT MAX(dateUsed) from vapeLog WHERE oauth_uid = '$id'";
	$result = mysqli_query($conn, $query); 
	$row = mysqli_fetch_assoc($result);
	$date = $row["MAX(dateUsed)"];
	$lastVape = date_create($date);
	$curDate = date_create(date("Y-m-d H:i:s"));
	$timeSince = date_diff($lastVape ,$curDate);
	echo "<p class='mycss'> It has been " . $timeSince->format("%d days and %h hours") . " since you last vaped. <br> Please reflect below on what time of the day you find yourself vaping the most.</p>";

?>

<div id='content'>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>

<div class="chart" >
<canvas id="canvas"> </canvas>

<?php
$id = $userData['oauth_uid'];
settype($id, "string");

$morning = "select count(*) from vapeLog where oauth_uid = '$id' and time(dateUsed) >= '05:00:00' and time(dateUsed) < '12:00:00'";
$afternoon = "select count(*) from vapeLog where oauth_uid = '$id' and time(dateUsed) >= '12:00:00' and time(dateUsed) < '18:00:00'";
$evening = "select count(*) from vapeLog where oauth_uid = '$id' and time(dateUsed) >= '18:00:00' and time(dateUsed) < '21:00:00'";
$night = "select count(*) from vapeLog where oauth_uid = '$id' and time(dateUsed) >= '21:00:00'";

$result1 = mysqli_query($conn, $morning);
$result2 = mysqli_query($conn, $afternoon);
$result3 = mysqli_query($conn, $evening);
$result4 = mysqli_query($conn, $night);

$row1 = mysqli_fetch_assoc($result1);
$row2 = mysqli_fetch_assoc($result2);
$row3 = mysqli_fetch_assoc($result3);
$row4 = mysqli_fetch_assoc($result4);

$count = [];
$count[] = $row1["count(*)"];
$count[] = $row2["count(*)"];
$count[] = $row3["count(*)"];
$count[] = $row4["count(*)"];

mysqli_close();
?>

<script type="text/javascript">
var xArray = ["Morning", "Afternoon", "Evening", "Night"];
var yArray = <?php echo json_encode($count); ?>;

var barColors = ["red", "green", "blue", "orange", "yellow", "purple", "brown"];

</script>

<script>
new Chart("canvas", {
  type: "bar",
  data: {
    labels: xArray,
    datasets: [{
      backgroundColor: barColors,
      data: yArray
    }]
  },
  options: {
	layout: {
		padding: 20
	},
	responsive: true,
	maintainAspectRatio: false,
    legend: {display: false},
	scales: {
		xAxes: [{
			display: true,
			ticks: {
				font: {
					size: 15,
					family: 'Georgia'
				}
			}
		}],
		yAxes: [{
			display: true,
			scaleLabel: {
				display: true,
				labelString: 'Instances'
			},
			ticks: {
				font: {
					size: 15,
					family: 'Georgia'
				},
				beginAtZero: true,
				stepSize: 1
			}
		}]
	},
    title: {
      display: true,
      text: "When am I vaping?",
	  font: {
		  size: 20,
		  family: 'Georgia'
	  }
    }
  }
});
</script>
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