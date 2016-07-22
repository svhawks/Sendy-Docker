<?php 
	ini_set('display_errors', 0);
	include('includes/config.php');
	include('includes/helpers/locale.php');
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
	        fail("<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\"/><link rel=\"Shortcut Icon\" type=\"image/ico\" href=\"/img/favicon.png\"><title>"._('Can\'t connect to database')."</title></head><style type=\"text/css\">body{background: #ffffff;font-family: Helvetica, Arial;}#wrapper{background: #f2f2f2;width: 300px;height: 110px;margin: -140px 0 0 -150px;position: absolute;top: 50%;left: 50%;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;}p{text-align: center;line-height: 18px;font-size: 12px;padding: 0 30px;}h2{font-weight: normal;text-align: center;font-size: 20px;}a{color: #000;}a:hover{text-decoration: none;}</style><body><div id=\"wrapper\"><p><h2>"._('Can\'t connect to database')."</h2></p><p>"._('There is a problem connecting to the database. Please try again later.')."</p></div></body></html>");
	    }
	    
	    global $charset; mysqli_set_charset($mysqli, isset($charset) ? $charset : "utf8");
	    
	    return $mysqli;
	}
	//--------------------------------------------------------------//
	function fail($errorMsg) { //Database connection fails
	//--------------------------------------------------------------//
	    echo $errorMsg;
	    exit;
	}
	// connect to database
	dbConnect();
?>
<?php
	include_once('includes/helpers/short.php');
	
	//vars
	$time = time();
	$join_date = round(time()/60)*60;
	$already_subscribed = false;
	$feedback = '';
	
	//get variable
	if(isset($_GET['i']))
	{
		$i = mysqli_real_escape_string($mysqli, $_GET['i']);
		$i_array = explode('/', $i);
		$email = trim($i_array[0]);
		$email = str_replace(" ", "+", $email);
        $email = str_replace("%20", "+", $email);
		$list_id = short($i_array[1], true);
		if(array_key_exists(2, $i_array)) $name = $i_array[2];
		if(array_key_exists(3, $i_array)) $return_boolean = $i_array[3];
		else $return_boolean = '';
		
		//Set language
		$q = 'SELECT login.language FROM lists, login WHERE lists.id = '.$list_id.' AND login.app = lists.app';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $language = $row['language'];
		set_locale($language);
		
		//check if email needs to be decrypted
		$validator = new EmailAddressValidator;
		if (!$validator->check_email_address($email)) $email = short($email, true);
		
		//check if email is valid
		$validator = new EmailAddressValidator;
		if ($validator->check_email_address($email)) {}
		else
		{
			if($return_boolean=='true')
			{
				echo 'Invalid email address.';
				exit;
			}
			else
			    $feedback = _('Email address is invalid.');
		}
	}
	else if(isset($_POST['email']))//email posted from subscribe form or API
	{		
		//parameters
		$email = mysqli_real_escape_string($mysqli, trim($_POST['email'])); //compulsory
		$name = strip_tags(mysqli_real_escape_string($mysqli, $_POST['name'])); //optional
		$list_id = strip_tags(short(mysqli_real_escape_string($mysqli, $_POST['list']), true)); //compulsory
		$return_boolean = strip_tags(mysqli_real_escape_string($mysqli, $_POST['boolean'])); //compulsory
		$ignore_opt_in = strip_tags(mysqli_real_escape_string($mysqli, $_POST['ignore_opt_in'])); //optional
		
		//Set language
		$q = 'SELECT login.language FROM lists, login WHERE lists.id = '.$list_id.' AND login.app = lists.app';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $language = $row['language'];
		set_locale($language);
		
		//check if no data passed
		if($email=='' || $list_id=='')
		{
			if($return_boolean=='true')
			{
				echo 'Some fields are missing.';
				exit;
			}
			else
				$feedback = _('Some fields are missing.');
		}
		else
		{
			//check if email is valid
			$validator = new EmailAddressValidator;
			if ($validator->check_email_address($email)) {}
			else
			{
				if($return_boolean=='true')
				{
					echo 'Invalid email address.';
					exit;
				}
				else
				    $feedback = _('Email address is invalid.');
			}
		}
		
	}
	else if($_GET['i']=='')
	{
		exit;
	}
	
	if($feedback!=_('Some fields are missing.') && $feedback!=_('Email address is invalid.'))
	{
		//check if list is double opt in
		$q = 'SELECT opt_in, subscribed_url, thankyou, thankyou_subject, thankyou_message, custom_fields FROM lists WHERE id = '.$list_id;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$opt_in = $row['opt_in'];
				$subscribed_url = $row['subscribed_url'];
				$thankyou = $row['thankyou'];
				$thankyou_subject = stripslashes($row['thankyou_subject']);
				$thankyou_message = stripslashes($row['thankyou_message']);
				$custom_fields = $row['custom_fields'];
		    }

		    if( isset($ignore_opt_in) && ($ignore_opt_in == 'true') )
		    {
				$opt_in = 0;
			}
			
		    //get custom fields list and format it for db insert
		    $cf_vals = '';
			$custom_fields_array = explode('%s%', $custom_fields);
			foreach($custom_fields_array as $cf)
			{
				$cf_array = explode(':', $cf);
				foreach ($_POST as $key => $value)
				{
					//if custom field matches POST data but IS NOT name, email, list or submit
					if(str_replace(' ', '', $cf_array[0])==$key && ($key!='name' && $key!='email' && $key!='list' && $key!='submit'))
					{
						//if custom field format is Date
						if($cf_array[1]=='Date')
						{
							$date_value1 = strtotime($value);
							$date_value2 = strftime("%b %d, %Y 12am", $date_value1);
							$value = strtotime($date_value2);
							$cf_vals .= $value;
						}
						//else if custom field format is Text
						else
							$cf_vals .= addslashes($value);
					}
				}
				$cf_vals .= '%s%';
			}
		}
		
		//check if user is in this list
		$q = 'SELECT id, userID, custom_fields, unsubscribed, confirmed FROM subscribers WHERE email = "'.$email.'" AND list = '.$list_id;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
			while($row = mysqli_fetch_array($r))
		    {
		    	$subscriber_id = $row['id'];
				$userID = $row['userID'];
				$custom_values = $row['custom_fields'];
				$unsubscribed = $row['unsubscribed'];
				$confirmed = $row['confirmed'];
		    } 
		    
		    //get custom fields values
		    $j = 0;
		    $cf_value = '';
		    $custom_values_array = explode('%s%', $custom_values);
		    foreach($custom_fields_array as $cf_fields)
			{
				$k = 0;
				$cf_fields_array = explode(':', $cf_fields);
				foreach ($_POST as $key => $value)
				{
					//if custom field matches POST data but IS NOT name, email, list or submit
					if(str_replace(' ', '', $cf_fields_array[0])==$key && ($key!='name' && $key!='email' && $key!='list' && $key!='submit'))
					{
						//if user left field empty
						if($value=='')
						{
							$cf_value .= '';
						}
						else
						{
							//if custom field format is Date
							if($cf_fields_array[1]=='Date')
							{
								$date_value1 = strtotime($value);
								$date_value2 = strftime("%b %d, %Y 12am", $date_value1);
								$value = strtotime($date_value2);
								$cf_value .= $value;
							}
							//else if custom field format is Text
							else
								$cf_value .= strip_tags($value);
						}
					}
					else
					{
						$k++;
					}
				}
				if(count($_POST)==$k) $cf_value .= $custom_values_array[$j];			
				$cf_value .= '%s%';
				$j++;
			}
		    
			//if so, update subscriber
			if($opt_in) 
			{
				if(!isset($_POST['name']))
					$q = 'UPDATE subscribers SET unsubscribed = 0, last_campaign = NULL, timestamp = '.$time.', confirmed = '.$confirmed.', custom_fields = "'.substr($cf_value, 0, -3).'" WHERE email = "'.$email.'" AND list = '.$list_id;
				else
					$q = 'UPDATE subscribers SET unsubscribed = 0, last_campaign = NULL, timestamp = '.$time.', confirmed = '.$confirmed.', name = "'.$name.'", custom_fields = "'.substr($cf_value, 0, -3).'" WHERE email = "'.$email.'" AND list = '.$list_id;
			}
			else
			{
				if(!isset($_POST['name']))
					$q = 'UPDATE subscribers SET unsubscribed = 0, last_campaign = NULL, timestamp = '.$time.', confirmed = 1, custom_fields = "'.substr($cf_value, 0, -3).'" WHERE email = "'.$email.'" AND list = '.$list_id;
				else
					$q = 'UPDATE subscribers SET unsubscribed = 0, last_campaign = NULL, timestamp = '.$time.', confirmed = 1, name = "'.$name.'", custom_fields = "'.substr($cf_value, 0, -3).'" WHERE email = "'.$email.'" AND list = '.$list_id;
			}
			$r = mysqli_query($mysqli, $q);
			if ($r)
			{
				if(!$unsubscribed && $confirmed) $already_subscribed = true;
				if(!$already_subscribed)
				{
					if($opt_in && $confirmed!=1)
						$feedback = '<span style="font-size: 20px;padding:10px;float:left;margin-top:-18px;">'._('Thank you, a confirmation email has been sent to you.').'</span>';
					else
						$feedback = _('You\'re subscribed!');
				}
				else
				{
					if($return_boolean=='true')
					{
						echo 'Already subscribed.';
						exit;
					}
					else
					{
						if($confirmed==0)
							$feedback = '<span style="font-size: 20px;padding:10px;float:left;margin-top:-18px;">'._('A confirmation email has already been sent to you.').'</span>';
						else
						    $feedback = _('You\'re already subscribed!');
					}
				}
			}
		}
		else
		{
			$q = 'SELECT userID FROM lists WHERE id = '.$list_id;
			$r = mysqli_query($mysqli, $q);
			if ($r && mysqli_num_rows($r) > 0)
			{
			    while($row = mysqli_fetch_array($r)) $userID = stripslashes($row['userID']);
			    
			    //if not, insert user into list
			    if($opt_in) //if double opt in,
					$q = 'INSERT INTO subscribers (userID, email, name, custom_fields, list, timestamp, confirmed) VALUES ('.$userID.', "'.$email.'", "'.$name.'", "'.substr($cf_vals, 0, -3).'", '.$list_id.', '.$time.', 0)';
				else
					$q = 'INSERT INTO subscribers (userID, email, name, custom_fields, list, timestamp, join_date) VALUES ('.$userID.', "'.$email.'", "'.$name.'", "'.substr($cf_vals, 0, -3).'", '.$list_id.', '.$time.', '.$join_date.')';
				$r = mysqli_query($mysqli, $q);
				if ($r){
					if($opt_in)
						$feedback = '<span style="font-size: 20px;padding:10px;float:left;margin-top:-18px;">'._('Thank you, a confirmation email has been sent to you.').'</span>';
					else
						$feedback = _('You\'re subscribed!');
				}
				
				$subscriber_id = mysqli_insert_id($mysqli);
			}
			else
			{
				echo 'Invalid list ID.';
				exit;
			}
		}
		
		if(!$already_subscribed)
		{
			//get AWS creds
			$q = 'SELECT s3_key, s3_secret FROM login WHERE id = '.$userID;
			$r = mysqli_query($mysqli, $q);
			if ($r)
			{
			    while($row = mysqli_fetch_array($r))
			    {
					$s3_key = $row['s3_key'];
					$s3_secret = $row['s3_secret'];
			    }
			}
			
			//get from name and from email
			$q2 = 'SELECT app FROM lists WHERE id = '.$list_id;
			$r2 = mysqli_query($mysqli, $q2);
			if ($r2)
			{
			    while($row = mysqli_fetch_array($r2))
			    {
					$app = $row['app'];
			    }  
			    $q3 = 'SELECT from_name, from_email, reply_to, smtp_host, smtp_port, smtp_ssl, smtp_username, smtp_password, allocated_quota FROM apps WHERE id = '.$app;
				$r3 = mysqli_query($mysqli, $q3);
				if ($r3)
				{
				    while($row = mysqli_fetch_array($r3))
				    {
						$from_name = $row['from_name'];
						$from_email = $row['from_email'];
						$reply_to = $row['reply_to'];
						$smtp_host = $row['smtp_host'];
						$smtp_port = $row['smtp_port'];
						$smtp_ssl = $row['smtp_ssl'];
						$smtp_username = $row['smtp_username'];
						$smtp_password = $row['smtp_password'];
						$allocated_quota = $row['allocated_quota'];
				    }  
				}
			}
			
			//send confirmation email if list is double opt in
			if($opt_in && $confirmed!=1)
			{			
				$confirmation_link = APP_PATH.'/confirm?e='.short($subscriber_id).'&l='.short($list_id);
				
				$q = 'SELECT confirmation_subject, confirmation_email FROM lists WHERE id = '.$list_id;
				$r = mysqli_query($mysqli, $q);
				if ($r && mysqli_num_rows($r) > 0)
				{
				    while($row = mysqli_fetch_array($r))
				    {
						$confirmation_subject = stripslashes($row['confirmation_subject']);
						$confirmation_email = stripslashes($row['confirmation_email']);
				    }  
				}
				
				if($confirmation_subject=='')
					$confirmation_subject = _('Confirm your subscription to').' '.$from_name;
				
				if(strlen(trim(preg_replace('/\xc2\xa0/',' ', $confirmation_email))) == 0 || trim($confirmation_email)=='<p><br></p>' || $output = trim(str_replace(array("\r\n", "\r", "\n", "	"), '', $confirmation_email))=="<html><head><title></title></head><body></body></html>")
					$confirmation_email = '<p>'._('Hi!').'</p>
	
<p>'._('Thanks for subscribing to our email list.').'</p>

<p>'._('Please confirm your subscription by clicking the link below').':</p>

<p>'._('Confirm').': '.$confirmation_link.'</p>

<p>'._('Thank you').',<br/>
'.$from_name.'</p>';
				else
					$confirmation_email = str_replace('[confirmation_link]', $confirmation_link, $confirmation_email);
	
				include('includes/helpers/class.phpmailer.php');
				$mail = new PHPMailer();	
				if($s3_key!='' && $s3_secret!='')
				{
					$mail->IsAmazonSES();
					$mail->AddAmazonSESKey($s3_key, $s3_secret);
				}
				else if($smtp_host!='' && $smtp_port!='' && $smtp_ssl!='' && $smtp_username!='' && $smtp_password!='')
				{
					$mail->IsSMTP();
					$mail->SMTPDebug = 0;
					$mail->SMTPAuth = true;
					$mail->SMTPSecure = $smtp_ssl;
					$mail->Host = $smtp_host;
					$mail->Port = $smtp_port; 
					$mail->Username = $smtp_username;  
					$mail->Password = $smtp_password;
				}
				$mail->CharSet	  =	"UTF-8";
				$mail->From       = $from_email;
				$mail->FromName   = $from_name;
				$mail->Subject = $confirmation_subject;
				$mail->MsgHTML($confirmation_email);
				$mail->AddAddress($email, '');
				$mail->AddReplyTo($reply_to, $from_name);
				$mail->Send();
				
				//Update quota if a monthly limit was set
				if($allocated_quota!=-1)
				{
					//if so, update quota
					$q4 = 'UPDATE apps SET current_quota = current_quota+1 WHERE id = '.$app;
					mysqli_query($mysqli, $q4);
				}
			}
			else //if single opt in, check if we need to send a thank you email
			{
				if($thankyou && $confirmed!=1)
				{
					include('includes/helpers/class.phpmailer.php');
					$mail = new PHPMailer();	
					if($s3_key!='' && $s3_secret!='')
					{
						$mail->IsAmazonSES();
						$mail->AddAmazonSESKey($s3_key, $s3_secret);
					}
					else if($smtp_host!='' && $smtp_port!='' && $smtp_ssl!='' && $smtp_username!='' && $smtp_password!='')
					{
						$mail->IsSMTP();
						$mail->SMTPDebug = 0;
						$mail->SMTPAuth = true;
						$mail->SMTPSecure = $smtp_ssl;
						$mail->Host = $smtp_host;
						$mail->Port = $smtp_port; 
						$mail->Username = $smtp_username;  
						$mail->Password = $smtp_password;
					}
					$mail->CharSet	  =	"UTF-8";
					$mail->From       = $from_email;
					$mail->FromName   = $from_name;
					$mail->Subject = $thankyou_subject;
					$mail->AltBody = '';
					$mail->MsgHTML($thankyou_message);
					$mail->AddAddress($email, '');
					$mail->AddReplyTo($reply_to, $from_name);
					$mail->Send();
					
					//Update quota if a monthly limit was set
					if($allocated_quota!=-1)
					{
						//if so, update quota
						$q4 = 'UPDATE apps SET current_quota = current_quota+1 WHERE id = '.$app;
						mysqli_query($mysqli, $q4);
					}
				}
			}
		}
	}

if($return_boolean=='true'):
	echo true;
	exit;
else:
	//if user sets a redirection URL
	if($subscribed_url != ''):
		$subscribed_url = str_replace('%e', $email, $subscribed_url);
		$subscribed_url = str_replace('%l', short($list_id), $subscribed_url);
		header("Location: ".$subscribed_url);
	else:
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="Shortcut Icon" type="image/ico" href="<?php echo APP_PATH;?>/img/favicon.png">
		<title><?php echo _('Subscribed');?></title>
	</head>
	<style type="text/css">
		body{
			background: #ffffff;
			font-family: Helvetica, Arial;
		}
		#wrapper 
		{
			background: #f2f2f2;
			
			width: 300px;
			height: 70px;
			
			margin: -140px 0 0 -150px;
			position: absolute;
			top: 50%;
			left: 50%;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
		}
		p{
			text-align: center;
		}
		h2{
			font-weight: normal;
			text-align: center;
		}
		a{
			color: #000;
		}
		a:hover{
			text-decoration: none;
		}
	</style>
	<body>
		<div id="wrapper">
			<h2><?php echo $feedback;?></h2>
		</div>
	</body>
</html>
<?php endif;?>
<?php endif;?>