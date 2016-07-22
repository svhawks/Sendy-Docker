<?php 
	ini_set('display_errors', 0);
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
	//get variable
	$t = mysqli_real_escape_string($mysqli, $_GET['t']);
	if($t=='') exit;
	
	//get html text from campaign
	$q = "SELECT html_text FROM template WHERE id = '$t'";
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$html = $row['html_text'];	
			
			//tags
			preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $html, $matches_var, PREG_PATTERN_ORDER);
			preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $html, $matches_val, PREG_PATTERN_ORDER);
			preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $html, $matches_all, PREG_PATTERN_ORDER);
			$matches_var = $matches_var[1];
			$matches_val = $matches_val[1];
			$matches_all = $matches_all[1];
			for($i=0;$i<count($matches_var);$i++)
			{   
				$field = $matches_var[$i];
				$fallback = $matches_val[$i];
				$tag = $matches_all[$i];
				//for each match, replace tag with fallback
				$html = str_replace($tag, $fallback, $html);
			}

			//set web version links
			$html = str_replace('<webversion', '<a href="'.APP_PATH.'/template-preview?t='.$t.'"', $html);
			$html = str_replace('</webversion>', '</a>', $html);
			
			//set unsubscribe links
			$html = str_replace('<unsubscribe', '<a href="'.APP_PATH.'/template-preview?t='.$t.'"', $html);
			$html = str_replace('</unsubscribe>', '</a>', $html);
			
			//convert date tags
			convert_date_tags();
					
			echo $html;
			exit;
	    }  
	}
	
	//convert date tags
	function convert_date_tags()
	{
		global $timezone;
		global $html;
		global $sent;
		global $send_date;
		if($timezone!='') date_default_timezone_set($timezone);
		$today = $sent == '' ? time() : $sent;
		$today = $send_date !='' && $send_date !=0 ? $send_date : $today;
		$currentdaynumber = strftime('%d', $today);
		$currentday = strftime('%A', $today);
		$currentmonthnumber = strftime('%m', $today);
		$currentmonth = strftime('%B', $today);
		$currentyear = strftime('%Y', $today);
		$unconverted_date = array('[currentdaynumber]', '[currentday]', '[currentmonthnumber]', '[currentmonth]', '[currentyear]');
		$converted_date = array($currentdaynumber, $currentday, $currentmonthnumber, $currentmonth, $currentyear);
		$html = str_replace($unconverted_date, $converted_date, $html);
	}
?>