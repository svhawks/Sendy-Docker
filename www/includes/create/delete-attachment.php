<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$filename = $_POST['filename'];
	$campaign_id = $_POST['campaign_id'];
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//delete file
	if(unlink('../../uploads/attachments/'.$campaign_id.'/'.$filename))
		echo true;
?>