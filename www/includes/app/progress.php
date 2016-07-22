<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 	
	//init
	$campaign_id = isset($_POST['campaign_id']) ? mysqli_real_escape_string($mysqli, $_POST['campaign_id']) : 0;
	
	//get send count
	$q = 'SELECT to_send, recipients FROM campaigns WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$to_send = $row['to_send'];
			$recipients = $row['recipients'];
	    }  
	}
	
	$percentage = $recipients / $to_send * 100;
	
	if($to_send == $recipients)
		echo $recipients;
	else
		echo $recipients.' <span style="color:#488846;">('.round($percentage).'%)</span> <img src="'.get_app_info('path').'/img/loader.gif" style="width:16px;"/>';
?>