<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$edit = isset($_GET['edit']) ? $_GET['edit'] : '';
	$template_id = isset($_GET['t']) ? mysqli_real_escape_string($mysqli, $_GET['t']) : '';	
	$template_name = addslashes(mysqli_real_escape_string($mysqli, $_POST['template_name']));
	$html = stripslashes($_POST['html']);
	if(trim($html)=='<html><head></head><body></body></html>') $html = '';
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	if($edit)
	{
		$q = 'UPDATE template SET template_name="'.$template_name.'", html_text="'.addslashes($html).'" WHERE id='.$template_id;
		$r = mysqli_query($mysqli, $q);
		if ($r) header('Location: '.get_app_info('path').'/templates?i='.get_app_info('app'));
		else show_error(_('Unable to save template'), '<p>'._('Please ensure you have granted FULL privileges to your MySQL user for your database.').'</p>');
	}
	else
	{	
		//Insert into campaigns
		$q = 'INSERT INTO template (userID, app, template_name, html_text) VALUES ('.get_app_info('main_userID').', '.get_app_info('app').', "'.$template_name.'", "'.addslashes($html).'")';
		$r = mysqli_query($mysqli, $q);
		if ($r) header('Location: '.get_app_info('path').'/templates?i='.get_app_info('app'));
		else show_error(_('Unable to create template'), '<p>'._('Please ensure you have granted FULL privileges to your MySQL user for your database.').'</p>');	
	}
?>