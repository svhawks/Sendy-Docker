<?php 
	include('includes/config.php');
	//--------------------------------------------------------------//
	function dbConnect() { //Connect to database
	//--------------------------------------------------------------//
	    // Access global variables
	    global $mysqli;
	    global $dbHost;
	    global $dbUser;
	    global $dbPass;
	    global $dbName;
	    global $dbPort;
	    
	    // Attempt to connect to database server
	    if(isset($dbPort)) $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
	    else $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
	
	    // If connection failed...
	    if ($mysqli->connect_error) {
	        fail();
	    }
	    
	    global $charset; mysqli_set_charset($mysqli, isset($charset) ? $charset : "utf8");
	    
	    return $mysqli;
	}
	//--------------------------------------------------------------//
	function fail() { //Database connection fails
	//--------------------------------------------------------------//
	    print 'Database error';
	    exit;
	}
	// connect to database
	dbConnect();
?>
<?php 
	include('includes/helpers/geo/geoip.inc');
	include('includes/helpers/short.php');
	
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	//----------------------------------------------------------------//
	
	//get variable
	$i = mysqli_real_escape_string($mysqli, $_GET['i']);
	$i_array = explode('/', $i);
	$campaign_id = short($i_array[0], true);
	$userID = short($i_array[1], true);
	if(array_key_exists(2, $i_array)) $ares = $i_array[2];
	else $ares = '';
	//get user's client
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	//get user's ip address & country code
	if (getenv("HTTP_CLIENT_IP")) {
		$ip = getenv("HTTP_CLIENT_IP");
	} elseif (getenv("HTTP_X_FORWARDED_FOR")) {
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	} else {
		$ip = getenv("REMOTE_ADDR");
	}
	$ip_array = explode(',', $ip);
	if(array_key_exists(1, $ip_array)) $ip = trim($ip_array[0]);
	
	$gi = geoip_open("includes/helpers/geo/GeoIP.dat",GEOIP_STANDARD);
	$country = geoip_country_code_by_addr($gi, $ip);
	geoip_close($gi);
	$time = time();
	
	//if this is an autoresponder email,
	$val = '';
	if(count($i_array)==3 && $i_array[2]=='a')
		$q = 'SELECT opens FROM ares_emails WHERE id = '.$campaign_id;
	else
		$q = 'SELECT opens FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$opens = $row['opens'];
			
			if($opens=='')
				$val = $userID.':'.$country;
			else
			{
				$opens .= ','.$userID.':'.$country;
				$val = $opens;
			}
	    }  
	}
	
	//Set open
	//if this is an autoresponder email,
	if(count($i_array)==3 && $i_array[2]=='a')
		$q = 'UPDATE ares_emails SET opens = "'.$val.'" WHERE id = '.$campaign_id;
	else
		$q = 'UPDATE campaigns SET opens = "'.$val.'" WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r){}
	
	//Just in case this user is set to bounced because Amazon can't deliver it the first time.
	//If user opens the newsletter, it means user did not bounce, so we set bounced to 0
	$q = 'SELECT email FROM subscribers WHERE id = '.$userID.' AND bounced = 1';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$email = stripslashes($row['email']);
			
			$q = 'UPDATE subscribers SET bounced = 0, timestamp = '.$time.' WHERE email = "'.$email.'" AND last_campaign = '.$campaign_id;
			$r = mysqli_query($mysqli, $q);
			if ($r){}
	    }  
	}
	
	//----------------------------------------------------------------//
	header("Location: ".APP_PATH."/img/to.png");
	return;
?>