<?php 
	include('../config.php');
	include('../helpers/class.phpmailer.php');
	include('../helpers/ses.php');
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
	//Init
	$from_email = isset($_POST['from_email']) ? mysqli_real_escape_string($mysqli, $_POST['from_email']) : '';
	$aws_key = isset($_POST['aws_key']) ? $_POST['aws_key'] : '';
	$aws_secret = isset($_POST['aws_secret']) ? $_POST['aws_secret'] : '';
	$ses_endpoint = isset($_POST['ses_endpoint']) ? $_POST['ses_endpoint'] : '';
	$bounce_simulator_email = 'bounce@simulator.amazonses.com';
	$complaint_simulator_email = 'complaint@simulator.amazonses.com';
	
	//Send email to bounce mailbox simulator
	$mail = new PHPMailer();
	$mail->IsAmazonSES();
	$mail->AddAmazonSESKey($aws_key, $aws_secret);
	$mail->CharSet	  =	"UTF-8";
	$mail->From       = $from_email;
	$mail->FromName   = 'Sendy';
	$mail->Subject = 'Test bounce setup';
	$mail->AltBody = 'Test';
	$mail->MsgHTML('Test');
	$mail->AddAddress($bounce_simulator_email, '');
	$mail->Send();
	
	//Send email to complaint mailbox simulator
	$mail2 = new PHPMailer();
	$mail2->IsAmazonSES();
	$mail2->AddAmazonSESKey($aws_key, $aws_secret);
	$mail2->CharSet	  =	"UTF-8";
	$mail2->From       = $from_email;
	$mail2->FromName   = 'Sendy';
	$mail2->Subject = 'Test complaint setup';
	$mail2->AltBody = 'Test';
	$mail2->MsgHTML('Test');
	$mail2->AddAddress($complaint_simulator_email, '');
	$mail2->Send();
	
	//Wait 10 seconds for emails to reach Amazon and for Amazon to process them
	sleep(10);
	
	//Then check if bounces and complaints SNS notifications have been setup
	$q = 'SELECT bounce_setup, complaint_setup FROM apps WHERE from_email = "'.$from_email.'"';
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$bounce_setup = $row['bounce_setup'];
			$complaint_setup = $row['complaint_setup'];
			$bounce_n_complaint_setup = $bounce_setup && $complaint_setup ? true : false;
			
			//If true, set email forwardinig to false
			if($bounce_n_complaint_setup)
			{
				$ses = new SimpleEmailService($aws_key, $aws_secret, $ses_endpoint);
				$ses->setIdentityFeedbackForwardingEnabled($from_email, 'false');
			}
	    }  
	}
	else
	{
		$q2 = 'SELECT bounce_setup, complaint_setup FROM campaigns WHERE from_email = "'.$from_email.'"';
		$r2 = mysqli_query($mysqli, $q2);
		if (mysqli_num_rows($r2) > 0)
		{
		    while($row = mysqli_fetch_array($r2))
		    {
				$bounce_setup = $row['bounce_setup'];
				$complaint_setup = $row['complaint_setup'];
				$bounce_n_complaint_setup = $bounce_setup && $complaint_setup ? true : false;
				
				//If true, set email forwardinig to false
				if($bounce_n_complaint_setup)
				{
					$ses = new SimpleEmailService($aws_key, $aws_secret);
					$ses->setIdentityFeedbackForwardingEnabled($from_email, 'false');
				}
		    }  
		}
	}
	
	echo $bounce_n_complaint_setup;
	
?>