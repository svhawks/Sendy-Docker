<?php ini_set('display_errors', 0);?>
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
	//init
	$lid = isset($_POST['list_id']) && is_numeric($_POST['list_id']) ? (int)mysqli_real_escape_string($mysqli, $_POST['list_id']) : 0;
	$userID = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
	$server_path_array = explode('progress.php', $_SERVER['SCRIPT_FILENAME']);
	$server_path = str_replace('includes/list/', '', $server_path_array[0]);
	if(file_exists($server_path.'/uploads/csvs/'.$userID.'-'.$lid.'.csv'))
	{
		$csv_file = $server_path.'/uploads/csvs/'.$userID.'-'.$lid.'.csv';
		$linecount = count(file($csv_file));
	}
	else
		$linecount = 0;
	
	//Get subscriber count
	$q = "SELECT COUNT(*) FROM subscribers WHERE list = '$lid' AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1";
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		while($row = mysqli_fetch_array($r)) $count = $row['COUNT(*)'];
		
		//Get prev_count and currently_processing
		$q = 'SELECT prev_count, currently_processing FROM lists WHERE id = '.$lid;
		$r = mysqli_query($mysqli, $q);
		if ($r) 
		{
			while($row = mysqli_fetch_array($r)) 
			{
				$prev_count = $row['prev_count'];
				$currently_processing = $row['currently_processing'];
			}
		}
		
		//If import is completed
		if($linecount==0)
		{
			//Show count without percentage
			echo $count;
		}
		//else, showing progress
		else
		{
			$percentage = $currently_processing ? ($count-$prev_count) / $linecount * 100 : 0;
			echo $count.' <span style="color:#488846;">('.round($percentage).'%)</span> <img src="img/loader.gif" style="width:16px;"/>';
		}
	}
?>