<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$app = mysqli_real_escape_string($mysqli, $_POST['id']);
	$list_id = mysqli_real_escape_string($mysqli, $_POST['list']);
	$list_name = mysqli_real_escape_string($mysqli, $_POST['list_name']);
	
	//subscribe settings
	$opt_in = mysqli_real_escape_string($mysqli, $_POST['opt_in']);
	$subscribed_url = mysqli_real_escape_string($mysqli, $_POST['subscribed_url']);
	$confirm_url = mysqli_real_escape_string($mysqli, $_POST['confirm_url']);
	$thankyou = isset($_POST['thankyou_email']) ? mysqli_real_escape_string($mysqli, $_POST['thankyou_email']) : '';
	$thankyou_subject = addslashes(mysqli_real_escape_string($mysqli, $_POST['thankyou_subject']));
	$thankyou_message = addslashes($_POST['thankyou_message']);
	if(preg_replace('/\s+/', '', $thankyou_message)=='<html><head></head><body></body></html>') $thankyou_message = '';
	if($thankyou!='')
		$thankyou = 1;
	else
		$thankyou = 0;
	//unsubscribe settings
	$unsubscribe_all_list = mysqli_real_escape_string($mysqli, $_POST['unsubscribe_all_list']);
	$unsubscribed_url = mysqli_real_escape_string($mysqli, $_POST['unsubscribed_url']);
	$goodbye = isset($_POST['goodbye_email']) ? mysqli_real_escape_string($mysqli, $_POST['goodbye_email']) : '';
	$goodbye_subject = addslashes(mysqli_real_escape_string($mysqli, $_POST['goodbye_subject']));
	$goodbye_message = addslashes($_POST['goodbye_message']);
	if(preg_replace('/\s+/', '', $goodbye_message)=='<html><head></head><body></body></html>') $goodbye_message = '';
	$confirmation_subject = addslashes(mysqli_real_escape_string($mysqli, $_POST['confirmation_subject']));
	$confirmation_email = addslashes($_POST['confirmation_email']);
	if(preg_replace('/\s+/', '', $confirmation_email)=='<html><head></head><body></body></html>') $confirmation_email = '';
	if($goodbye!='')
		$goodbye = 1;
	else
		$goodbye = 0;
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	$q = 'UPDATE lists SET name = "'.$list_name.'", opt_in = '.$opt_in.', subscribed_url = "'.$subscribed_url.'", confirm_url = "'.$confirm_url.'", thankyou = '.$thankyou.', thankyou_subject = "'.$thankyou_subject.'", thankyou_message = "'.$thankyou_message.'", unsubscribe_all_list = '.$unsubscribe_all_list.', unsubscribed_url = "'.$unsubscribed_url.'", goodbye = '.$goodbye.', goodbye_subject = "'.$goodbye_subject.'", goodbye_message = "'.$goodbye_message.'", confirmation_subject = "'.$confirmation_subject.'", confirmation_email = "'.$confirmation_email.'" WHERE id = '.$list_id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		header("Location: ".get_app_info('path')."/list?i=".$app);
	}
?>