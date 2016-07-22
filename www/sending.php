<?php 
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
	$campaign_id = is_numeric($_GET['c']) ? $_GET['c'] : exit;
	$email_list = explode(',', $_GET['e']);
	$app = is_numeric($_GET['i']) ? $_GET['i'] : exit;
	$schedule = $_GET['s'];
	if(isset($_GET['cr'])) $cron = is_numeric($_GET['cr']) ? $_GET['cr'] : exit;
	$total_recipients = $_GET['recipients'];
	
	//Set language
	$q = 'SELECT login.language FROM campaigns, login WHERE campaigns.id = '.$campaign_id.' AND login.app = campaigns.app';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $language = $row['language'];
	set_locale($language);
	
	//check if sent
	$q = 'SELECT sent FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$sent = stripslashes($row['sent']);
	    }  
	}
	
	//Check if monthly quota needs to be updated
	$q = 'SELECT allocated_quota, current_quota FROM apps WHERE id = '.$app;
	$r = mysqli_query($mysqli, $q);
	if($r) 
	{
		while($row = mysqli_fetch_array($r)) 
		{
			$allocated_quota = $row['allocated_quota'];
			$current_quota = $row['current_quota'];
			$updated_quota = $current_quota + $total_recipients;
		}
	}
	//Update quota if a monthly limit was set
	if($allocated_quota!=-1)
	{
		//if so, update quota
		$q = 'UPDATE apps SET current_quota = '.$updated_quota.' WHERE id = '.$app;
		mysqli_query($mysqli, $q);
	}

//if scheduled
if($schedule=='true'):

	//get POST variables
	$the_date = mysqli_real_escape_string($mysqli, $_GET['date']);
	$timezone = mysqli_real_escape_string($mysqli, $_GET['timezone']);
	
	$q = 'UPDATE campaigns SET send_date = "'.$the_date.'", lists = "'.mysqli_real_escape_string($mysqli, $_GET['e']).'", timezone = "'.$timezone.'" WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if($r):
	?>
	<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="Shortcut Icon" type="image/ico" href="<?php echo APP_PATH;?>/img/favicon.png">
		<title><?php echo _('Your campaign has been scheduled');?></title>
	</head>
	<style type="text/css">
		body{
			background: #ffffff;
			font-family: Helvetica, Arial;
		}
		#wrapper 
		{
			background: #f2f2f2;
			
			width: 280px;
			height: 250px;
			
			margin: -190px 0 0 -185px;
			position: absolute;
			top: 50%;
			left: 50%;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
			padding: 10px 20px;
		}
		p{
			text-align: center;
			font-size: 12px;
			line-height: 16px;
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
		#sending{
			margin-left: 95px;
		}
	</style>
	<body>
		<div id="wrapper">
			<h2><?php echo _('Your campaign has been scheduled');?>!</h2>
			<img id="sending" src="<?php echo APP_PATH;?>/img/scheduled.jpg" />
			<p><?php echo _('You will be notified by email once your campaign has been sent.');?></p>
		</div>
	</body>
	</html>
	<?php endif;?>

<?php else:?>

	<!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<script type="text/javascript" src="<?php echo APP_PATH;?>/js/jquery-1.9.1.min.js"></script>
			<script type="text/javascript" src="<?php echo APP_PATH;?>/js/jquery-migrate-1.1.0.min.js"></script>
			<link rel="Shortcut Icon" type="image/ico" href="<?php echo APP_PATH;?>/img/favicon.png">
			<title><?php echo _('Now sending');?></title>
			<?php if($sent==''):?>
			<script type="text/javascript">
				$(document).ready(function() {
					list = [<?php 
						for($i=0;$i<count($email_list);$i++)
						{
							echo "'".htmlspecialchars(addslashes($email_list[$i]))."'";
							
							if($i<count($email_list)-1)
								echo ',';
						}
					?>];
					$.post("<?php echo APP_PATH;?>/includes/create/send-now.php", { campaign_id: <?php echo $campaign_id;?>, email_list: list, app: <?php echo $app;?>, cron: <?php echo $cron;?> },
					  function(data) {
					      if(data){}
					  }
					);
				});
			</script>
			<?php endif;?>
		</head>
		<style type="text/css">
			body{
			background: #ffffff;
			font-family: Helvetica, Arial;
		}
		#wrapper 
		{
			background: #f2f2f2;
			
			width: 350px;
			height: 250px;
			
			margin: -190px 0 0 -185px;
			position: absolute;
			top: 50%;
			left: 50%;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
			padding: 10px 20px;
		}
		p{
			text-align: center;
			font-size: 12px;
			line-height: 16px;
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
		#sending{
			margin-left: 130px;
		}
		</style>
		<body>
			<div id="wrapper">
				<h2><?php echo _('Your campaign is on the way!');?></h2>
				<img id="sending" src="<?php echo APP_PATH;?>/img/sending.jpg" />
				<p><?php echo _('You can close this window and your campaign will continue to send. You will be notified by email once your campaign has completed sending.');?></p>
			</div>
		</body>
	</html>
	
<?php endif;?>