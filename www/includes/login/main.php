<?php
//------------------------------------------------------//
//                          INIT                        //
//------------------------------------------------------//

include('../functions.php');

$email = mysqli_real_escape_string($mysqli, $_POST['email']);
$pass = mysqli_real_escape_string($mysqli, $_POST['password']);
$pass_encrypted = hash('sha512', $pass.'PectGtma');
if(isset($_POST['redirect'])) $redirect_to = $_POST['redirect'];
else $redirect_to = '';
$time = time();

//------------------------------------------------------//
//                         EVENTS                       //
//------------------------------------------------------//
if($pass=='' || $email=='')
{
	//user doesn't exist and exit
	if($redirect_to=='') header("Location: ".get_app_info('path')."/login?e=1");
	else header("Location: ".get_app_info('path')."/login?e=1&redirect=$redirect_to");
	exit;
}
else
{
	$q = 'SELECT id, tied_to, app, auth_enabled, auth_key FROM login WHERE username = "'.$email.'" && password = "'.$pass_encrypted.'" ORDER BY id ASC LIMIT 1';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$userID = $row['id'];
			$tied_to = $row['tied_to'];
			$auth_enabled = $row['auth_enabled'];
			$_SESSION['auth_key'] = $row['auth_key'];
			$_SESSION['restricted_to_app'] = $row['app'];
			$_SESSION['userID'] = $userID;
	    }
		
		//If 2FA enabled
		if($auth_enabled)
		{
			$_SESSION['cookie'] = hash('sha512', $userID.$email.$pass_encrypted.'PectGtma');
			if($tied_to=='')
			{
				if($redirect_to=='') header("Location: ".get_app_info('path').'/two-factor');
				else header("Location: ".get_app_info('path').'/two-factor?redirect='.$redirect_to);
			}
			else
			{
				if($redirect_to=='') header("Location: ".get_app_info('path')."/two-factor?redirect=app?i=".$_SESSION['restricted_to_app']);
				else header("Location: ".get_app_info('path')."/two-factor?redirect=".$redirect_to);
			}
		}	
		//set cookie and log in
		else if(setcookie('logged_in', hash('sha512', $userID.$email.$pass_encrypted.'PectGtma'), time()+31556926, '/', get_app_info('cookie_domain')))
		{
			if($tied_to=='')
			{
				if($redirect_to=='') header("Location: ".get_app_info('path'));
				else header("Location: ".get_app_info('path').'/'.$redirect_to);
			}
			else
			{
				if($redirect_to=='') header("Location: ".get_app_info('path')."/app?i=".$_SESSION['restricted_to_app']);
				else header("Location: ".get_app_info('path')."/".$redirect_to);
			}
		}
	}
	else
	{
		//user doesn't exist and exit
		if($redirect_to=='') header("Location: ".get_app_info('path')."/login?e=2");
		else header("Location: ".get_app_info('path')."/login?e=2&redirect=$redirect_to");
		exit;
	}
}
?>