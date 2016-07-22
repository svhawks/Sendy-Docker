<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$app_id = mysqli_real_escape_string($mysqli, $_POST['id']);
	$filename = mysqli_real_escape_string($mysqli, $_POST['filename']);
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//delete file
	if(unlink('../../uploads/logos/'.$filename))
	{
		//Remove filename from database
		$q = 'UPDATE apps SET brand_logo_filename = \'\' WHERE id = '.$app_id;
		$r = mysqli_query($mysqli, $q);
		if ($r) echo true;
	}
?>