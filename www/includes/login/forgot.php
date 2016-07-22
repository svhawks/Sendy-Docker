<?php
//------------------------------------------------------//
//                          INIT                        //
//------------------------------------------------------//

include('../functions.php');
include('../helpers/class.phpmailer.php');
require_once('../helpers/ses.php');
require_once('../helpers/EmailAddressValidator.php');

$email = mysqli_real_escape_string($mysqli, $_POST['email']);
$email_domain_array = explode('@', $email);
$email_domain = $email_domain_array[1];
$new_pass = ran_string(8, 8, true, false, true);
$pass_encrypted = hash('sha512', $new_pass.'PectGtma');

//------------------------------------------------------//
//                         EVENTS                       //
//------------------------------------------------------//

//Get 'main user' login email address
$r = mysqli_query($mysqli, 'SELECT username FROM login ORDER BY id ASC LIMIT 1');
if ($r) while($row = mysqli_fetch_array($r)) $main_user_email_address = $row['username'];

$q = 'SELECT id, name, company, s3_key, s3_secret, ses_endpoint FROM login WHERE username = "'.$email.'" LIMIT 1';
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0)
{
	while($row = mysqli_fetch_array($r))
    {
    	$uid = $row['id'];
		$company = stripslashes($row['company']);
		$name = stripslashes($row['name']);
		$aws_key = stripslashes($row['s3_key']);
		$aws_secret = stripslashes($row['s3_secret']);
		$ses_endpoint = stripslashes($row['ses_endpoint']);
    } 
    
    //Change user's password to the new one
    $q = 'UPDATE login SET password = "'.$pass_encrypted.'" WHERE id = '.$uid;
    $r = mysqli_query($mysqli, $q);
    if ($r)
    {
    	//send a message to let them know
    	$plain_text = $name.',
'._('Your password has been reset, here\'s your new one').':

'._('Password').': '.$new_pass.'

'._('Remember to change it immediately once you log back in.');

        $message = '
	    <p>'.$name.',</p>
	    <p>'._('Your password has been reset, here\'s your new one').':</p>
	    <p><strong>'._('Password').'</strong>: '.$new_pass.'</p>
	    <p>'._('Remember to change it immediately once you log back in.').'</p>
	    ';
	    
	    //send email to me
		$mail = new PHPMailer();
		if($aws_key!='' && $aws_secret!='')
		{
			//Initialize ses class
			$ses = new SimpleEmailService($aws_key, $aws_secret, $ses_endpoint);
			
			//Check if user's AWS keys are valid
			$testAWSCreds = $ses->getSendQuota();
			if($testAWSCreds)
			{			
				//Check if login email is verified in Amazon SES console
				$v_addresses = $ses->ListIdentities();
				$verifiedEmailsArray = array();
				$verifiedDomainsArray = array();
				foreach($v_addresses['Addresses'] as $val){
					$validator = new EmailAddressValidator;
					if ($validator->check_email_address($val)) array_push($verifiedEmailsArray, $val);
					else array_push($verifiedDomainsArray, $val);
				}
				$veriStatus = true;
				$getIdentityVerificationAttributes = $ses->getIdentityVerificationAttributes($email);
				foreach($getIdentityVerificationAttributes['VerificationStatus'] as $getIdentityVerificationAttribute) 
					if($getIdentityVerificationAttribute=='Pending') $veriStatus = false;
				
				//If login email address is in Amazon SES console,
				if(in_array($email, $verifiedEmailsArray) || in_array($email_domain, $verifiedDomainsArray))
				{
					//and the email address is 'Verified'
					if($veriStatus)
					{
						//Send password reset email via Amazon SES
						$mail->IsAmazonSES();
						$mail->AddAmazonSESKey($aws_key, $aws_secret);
					}
				}
			}
		}
		$mail->CharSet	  =	"UTF-8";
		$mail->From       = $email;
		$mail->FromName   = $company;
		$mail->Subject = '['.$company.'] '._('Your new password');
		$mail->AltBody = $plain_text;
		$mail->MsgHTML($message);
		$mail->AddAddress($email, $company);
		$mail->Send();
    }
    
    echo $email == $main_user_email_address ? 'main_user' : true;
    exit;
}
else
{
	echo _('Email does not exist.');
	exit;
}
?>