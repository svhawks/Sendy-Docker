<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$campaign_id = mysqli_real_escape_string($mysqli, $_POST['campaign_id']);
	$campaign_title = mysqli_real_escape_string($mysqli, $_POST['campaign_title']);
	
	//Update campaign title
	$q = 'UPDATE campaigns SET label = "'.$campaign_title.'" WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r) echo true;
	else echo false;
?>