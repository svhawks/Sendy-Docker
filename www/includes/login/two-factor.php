<?php
//------------------------------------------------------//
//                          INIT                        //
//------------------------------------------------------//

include('../functions.php');
require_once('../helpers/two-factor/lib/otphp.php');
$totp_secret_key = $_SESSION['auth_key'];
$totp = new \OTPHP\TOTP($totp_secret_key);

if(isset($_POST['redirect'])) $redirect_to = $_POST['redirect'];
else $redirect_to = '';

//Init OTP
$otp = $totp->now();

//Get POSTed OTP code from form
if(is_numeric($_POST['otp_code'])) $otp_code = $_POST['otp_code'];
else
{
	header("Location: ".get_app_info('path').'/two-factor?e=1&redirect='.$redirect_to);
	exit;
}

//If OTP code is correct
if($totp->verify($otp_code))
{
	//set cookie and log in
	if(setcookie('logged_in', $_SESSION['cookie'], time()+31556926, '/', get_app_info('cookie_domain')))
	{
		if($redirect_to=='') header("Location: ../../");
		else header("Location: ../../".$redirect_to);
	}
}
else
{
	//user doesn't exist and exit
	if($redirect_to=='') header("Location: ../../two-factor?e=2");
	else header("Location: ../../two-factor?e=2&redirect=".$redirect_to);
	exit;
}

?>