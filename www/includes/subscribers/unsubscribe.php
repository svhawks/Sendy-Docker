<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$subscriber_id = mysqli_real_escape_string($mysqli, $_POST['subscriber_id']);
	$action = $_POST['action'];
	$time = time();
	
	if($action=='unsubscribe')
		$q = 'UPDATE subscribers SET unsubscribed = 1, timestamp = '.$time.' WHERE id = '.$subscriber_id.' AND userID = '.get_app_info('main_userID');
	else if($action=='resubscribe')
		$q = 'UPDATE subscribers SET unsubscribed = 0, timestamp = '.$time.' WHERE id = '.$subscriber_id.' AND userID = '.get_app_info('main_userID');
	else if($action=='confirm')
		$q = 'UPDATE subscribers SET confirmed = 1, timestamp = '.$time.' WHERE id = '.$subscriber_id.' AND userID = '.get_app_info('main_userID');
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		echo true; 
	}
	
?>