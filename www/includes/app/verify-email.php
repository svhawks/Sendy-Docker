<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	require_once('../helpers/ses.php');
	require_once('../helpers/EmailAddressValidator.php');
?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	//From email validation
	$from_email = isset($_POST['from_email']) ? $_POST['from_email'] : '';
	$auto_verify = $_POST['auto_verify']=='no' ? false : true;
	
	$validator = new EmailAddressValidator;
	if (!$validator->check_email_address($from_email)) 
	{
		echo 'Invalid email';
		exit;
	}
	
	$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
	if($ses->verifyEmailAddress($from_email))
		echo 'success';
	else
		echo 'failed';
		
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
?>