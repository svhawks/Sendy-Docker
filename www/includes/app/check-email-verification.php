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
	
	//Get email's domain
	$from_email_domain_array = explode('@', $from_email);
	$from_email_domain = $from_email_domain_array[1];
		
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//Check if from email is verified in SES console
	if(!get_app_info('is_sub_user') && get_app_info('s3_key')!='' && get_app_info('s3_secret')!='')
	{
		$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
		$v_addresses = $ses->ListIdentities();
		
		if(!$v_addresses)
		{
			//From email address or domain 'pending verification' in SES console
			echo 'api error';
		}
		else
		{
			$verifiedEmailsArray = array();
			$verifiedDomainsArray = array();
			foreach($v_addresses['Addresses'] as $val){
				$validator = new EmailAddressValidator;
				if ($validator->check_email_address($val)) array_push($verifiedEmailsArray, $val);
				else array_push($verifiedDomainsArray, $val);
			}
			
			$veriStatus = true;
			$getIdentityVerificationAttributes = $ses->getIdentityVerificationAttributes($from_email);
			foreach($getIdentityVerificationAttributes['VerificationStatus'] as $getIdentityVerificationAttribute) 
				if($getIdentityVerificationAttribute=='Pending') $veriStatus = false;
									
			if(!in_array($from_email, $verifiedEmailsArray) && !in_array($from_email_domain, $verifiedDomainsArray))
			{
				//Attempt to verify the email address, a verification email will be sent to the 'From email' address by Amazon SES
				if($auto_verify)
					$ses->verifyEmailAddress($from_email);
				
				echo 'unverified'; //From email address or domain IS NOT verified in SES console
			}
			else if(!$veriStatus)
				echo 'pending verification'; //From email address or domain is 'pending verification' in SES console
			else 
				echo 'verified'; //From email address or domain IS verified in SES console
		}
	}
?>