<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$campaign_id = mysqli_real_escape_string($mysqli, $_POST['campaign_id']);
	$app_id = mysqli_real_escape_string($mysqli, $_POST['on-brand']);
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//get campaign's data
	$q = 'SELECT from_name, from_email, reply_to, title, label, plain_text, html_text, query_string, bounce_setup, complaint_setup FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$from_name = $row['from_name'];
			$from_email = $row['from_email'];
			$reply_to = $row['reply_to'];
			$title = stripslashes($row['title']);
			$label = stripslashes($row['label']);
			$plain_text = stripslashes($row['plain_text']);
			$html_text = stripslashes($row['html_text']);
			$query_string = stripslashes($row['query_string']);
			$bounce_setup = $row['bounce_setup'];
			$complaint_setup = $row['complaint_setup'];
	    }  
	}
	
	//Insert into database
	$q3 = 'INSERT INTO campaigns (userID, app, from_name, from_email, reply_to, title, label, plain_text, html_text, query_string, bounce_setup, complaint_setup, wysiwyg) VALUES ('.get_app_info('main_userID').', '.$app_id.', "'.$from_name.'", "'.$from_email.'", "'.$reply_to.'", "'.addslashes($title).'", "'.addslashes($label).'", "'.addslashes($plain_text).'", "'.addslashes($html_text).'", "'.addslashes($query_string).'", '.$bounce_setup.', '.$complaint_setup.', 1)';
	$r3 = mysqli_query($mysqli, $q3);
	if ($r3)
	     header("Location: ".get_app_info('path')."/app?i=".$app_id);
	else
		echo 'Error duplicating.';
?>