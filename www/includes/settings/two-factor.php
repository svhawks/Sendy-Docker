<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../helpers/two-factor/lib/otphp.php');?>
<?php 
	$userID = mysqli_real_escape_string($mysqli, $_POST['uid']);
	$enable = is_numeric($_POST['enable']) ? $_POST['enable'] : exit;
	$key = mysqli_real_escape_string($mysqli, $_POST['key']);
	if(is_numeric($_POST['otp'])) $otp_code = $_POST['otp'];
	else { echo 'not numeric'; exit;	}

	//Enable two factor authentication	
	if($enable==1)
	{
		$totp = new \OTPHP\TOTP($key);
		$otp = $totp->now();
		if($totp->verify($otp_code))
		{
			$q = 'UPDATE login SET auth_enabled = '.$enable.', auth_key = "'.$key.'" WHERE id = '.$userID;
			$r = mysqli_query($mysqli, $q);
			if ($r) echo 'confirmed';
			else echo 'not confirmed';
		}
		else echo 'incorrect';
	}
	//Disable two factor authentication	
	else if($enable==0)
	{
		$q = 'UPDATE login SET auth_enabled = 0, auth_key = NULL WHERE id = '.$userID;
		$r = mysqli_query($mysqli, $q);
		if ($r) echo true;
		else echo false;
	}
?>