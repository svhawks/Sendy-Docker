<?php 
	include('../functions.php');
	include('../login/auth.php');
	
	//Init
	$file = $_FILES['upload']['tmp_name'];
	$file_name = $_FILES['upload']['name'];
	$extension_explode = explode('.', $file_name);
	$extension = $extension_explode[count($extension_explode)-1];
	$time = time();
	chmod("../../uploads",0777);
	
	//Check filetype
	$allowed = array("jpeg", "jpg", "gif", "png");
	if(in_array(strtolower($extension), $allowed)) //if file is an image, allow upload
	{
		//Upload file
		move_uploaded_file($file, '../../uploads/'.$time.'.'.$extension);
		
		//return result
		//echo 'Image uploaded successfully!';
		
		// Required: anonymous function reference number as explained above.
		$funcNum = $_GET['CKEditorFuncNum'] ;
		// Optional: instance name (might be used to load a specific configuration file or anything else).
		$CKEditor = $_GET['CKEditor'] ;
		// Optional: might be used to provide localized messages.
		$langCode = $_GET['langCode'] ;
		 
		// Check the $_FILES array and save the file. Assign the correct path to a variable ($url).
		$url = APP_PATH.'/uploads/'.$time.'.'.$extension;
		// Usually you will only assign something here if the file could not be uploaded.
		$message = '';
		
		echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
	}
	else exit;
?>