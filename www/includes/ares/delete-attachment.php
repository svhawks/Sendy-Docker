<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$filename = $_POST['filename'];
	$ares_id = $_POST['ares_id'];
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//delete file
	if(unlink('../../uploads/attachments/a'.$ares_id.'/'.$filename))
		echo true;
?>