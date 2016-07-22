<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$subscriber_id = mysqli_real_escape_string($mysqli, $_POST['subscriber_id']);
	
	$q = 'DELETE FROM subscribers WHERE id = '.$subscriber_id.' AND userID = '.get_app_info('main_userID');
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		echo true; 
	}
	
?>