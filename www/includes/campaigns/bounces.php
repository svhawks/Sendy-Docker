<?php 
	include('../config.php');
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
	if (!isset($HTTP_RAW_POST_DATA)) $HTTP_RAW_POST_DATA = file_get_contents('php://input');
	$data = json_decode($HTTP_RAW_POST_DATA);
	$time = time();
	$bounce_simulator_email = 'bounce@simulator.amazonses.com';
	
	//Confirm SNS subscription
	if($data->Type == 'SubscriptionConfirmation')
	{		
		file_get_contents_curl($data->SubscribeURL);
	}
	else
	{
		//detect bounces
		$obj = json_decode($data->Message);
		$notificationType = $obj->{'notificationType'};
		$bounceType = $obj->{'bounce'}->{'bounceType'};
		$problem_email = $obj->{'bounce'}->{'bouncedRecipients'};
		$problem_email = get_email($problem_email[0]->{'emailAddress'});
		$from_email = get_email($obj->{'mail'}->{'source'});
		
		//check if email is valid, if not, exit
		if(!filter_var($problem_email,FILTER_VALIDATE_EMAIL)) exit;
		
		if($notificationType=='Bounce')
		{
			//Update Bounce status
			if($problem_email==$bounce_simulator_email) 
			{
				if(filter_var($from_email,FILTER_VALIDATE_EMAIL))
				{
					mysqli_query($mysqli, 'UPDATE apps SET bounce_setup=1 WHERE from_email = "'.$from_email.'"');
					mysqli_query($mysqli, 'UPDATE campaigns SET bounce_setup=1 WHERE from_email = "'.$from_email.'"');
				}
			}
			
			//Update database of
			if($bounceType == 'Transient')
				$q = 'UPDATE subscribers SET bounce_soft = bounce_soft+1 WHERE email = "'.$problem_email.'"';
			else
				$q = 'UPDATE subscribers SET bounced = 1, timestamp = '.$time.' WHERE email = "'.$problem_email.'"';
			$r = mysqli_query($mysqli, $q);
			if ($r)
			{
				//check if recipient has soft bounced 3 times
				if($bounceType == 'Transient')
				{
					$q2 = 'SELECT bounce_soft FROM subscribers WHERE email = "'.$problem_email.'" LIMIT 1';
					$r2 = mysqli_query($mysqli, $q2);
					if ($r2 && mysqli_num_rows($r2) > 0)
					{
					    while($row = mysqli_fetch_array($r2))
					    {
							$bounce_soft = $row['bounce_soft'];
					    }  
					    
					    //if soft bounced 3 times or more, set as hard bounce
					    if($bounce_soft >= 3)
					    {
						    $q = 'UPDATE subscribers SET bounced = 1, timestamp = '.$time.' WHERE email = "'.$problem_email.'"';
						    $r = mysqli_query($mysqli, $q);
						    if($r){}
					    }
					}
				}
			}
		}
	}
	
	//--------------------------------------------------------------//
	function file_get_contents_curl($url) 
	//--------------------------------------------------------------//
	{
		//Get server path
		$server_path_array = explode('includes/campaigns/bounces.php', $_SERVER['SCRIPT_FILENAME']);
	    $server_path = $server_path_array[0];
	    $ca_cert_bundle = $server_path.'certs/cacert.pem';
	    
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_CAINFO, $ca_cert_bundle);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	//--------------------------------------------------------------//
	function get_email($string) 
	//--------------------------------------------------------------//
	{
	    foreach(preg_split('/\s/', $string) as $token) 
	    {
	        $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
	        if ($email !== false) $emails[] = $email;
	    }
	    return $emails[0];
	}
?>