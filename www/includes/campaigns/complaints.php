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
	$complaint_simulator_email = 'complaint@simulator.amazonses.com';
	
	//Confirm SNS subscription
	if($data->Type == 'SubscriptionConfirmation')
	{
		file_get_contents_curl($data->SubscribeURL);
	}
	else
	{
		//detect complaints
		$obj = json_decode($data->Message);
		$notificationType = $obj->{'notificationType'};
		$problem_email = $obj->{'complaint'}->{'complainedRecipients'};
		$problem_email = get_email($problem_email[0]->{'emailAddress'});
		$from_email = get_email($obj->{'mail'}->{'source'});
		$messageId = $obj->{'mail'}->{'messageId'};
		
		//check if email is valid, if not, exit
		if(!filter_var($problem_email,FILTER_VALIDATE_EMAIL)) exit;
		
		if($notificationType=='Complaint')
		{			
			//Update complaint status
			if($problem_email==$complaint_simulator_email) 
			{
				if(filter_var($from_email,FILTER_VALIDATE_EMAIL))
				{
					mysqli_query($mysqli, 'UPDATE apps SET complaint_setup=1 WHERE from_email = "'.$from_email.'"');
					mysqli_query($mysqli, 'UPDATE campaigns SET complaint_setup=1 WHERE from_email = "'.$from_email.'"');
				}
			}
			
			//Get app ID of this complaint email
			$q = 'SELECT lists.app FROM lists, subscribers WHERE subscribers.messageID = "'.mysqli_real_escape_string($mysqli, $messageId).'" AND subscribers.list = lists.id';
			$r = mysqli_query($mysqli, $q);
			if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $app = $row['app'];
			
			//get comma separated lists belonging to this app
			$q = 'SELECT id FROM lists WHERE app = '.$app;
			$r = mysqli_query($mysqli, $q);
			if ($r)
			{
				$all_lists = '';
			    while($row = mysqli_fetch_array($r)) $all_lists .= $row['id'].',';
			    $all_lists = substr($all_lists, 0, -1);
			}
			
			//Mark as spam in ALL lists in the brand for this email
			$q = 'UPDATE subscribers SET unsubscribed = 0, bounced = 0, complaint = 1, timestamp = '.$time.' WHERE email = "'.$problem_email.'" AND list IN ('.$all_lists.')';
			mysqli_query($mysqli, $q);
		}
	}
	
	//--------------------------------------------------------------//
	function file_get_contents_curl($url) 
	//--------------------------------------------------------------//
	{
		//Get server path
		$server_path_array = explode('includes/campaigns/complaints.php', $_SERVER['SCRIPT_FILENAME']);
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