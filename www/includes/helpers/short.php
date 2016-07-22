<?php
//Get API key
$q_api = 'SELECT api_key FROM login ORDER BY id ASC LIMIT 1';
$r_api = mysqli_query($mysqli, $q_api);
if ($r_api) while($row = mysqli_fetch_array($r_api)) $api_key = $row['api_key'];

//Email validator
include_once 'EmailAddressValidator.php';

//2 way encrypt function
function short($in, $to_num = false)
{
	global $api_key;
	$encryptionMethod = "AES-256-CBC";
	
	//check if variable is an email
	$validator = new EmailAddressValidator;
	$is_email = $validator->check_email_address($in) ? true : false;
	
	if($to_num)
	{
		if(version_compare(PHP_VERSION, '5.3.0') >= 0) //openssl_decrypt requires at least 5.3.0
		{
			$decrypted = str_replace('892', '/', $in);
			$decrypted = str_replace('763', '+', $decrypted);
			
			if(function_exists('openssl_encrypt')) 
			{
				$decrypted = version_compare(PHP_VERSION, '5.3.3') >= 0 ? openssl_decrypt($decrypted, $encryptionMethod, $api_key, 0, '3j9hwG7uj8uvpRAT') : openssl_decrypt($decrypted, $encryptionMethod, $api_key, 0);
				if(!$decrypted) return $is_email ? $in : intval($in, 36);
			}
			else return $is_email ? $in : intval($in, 36);
			
			return $decrypted=='' ? intval($in, 36) : $decrypted;
		}
		else return $is_email ? $in : intval($in, 36);
	}
	else
	{	
		if(version_compare(PHP_VERSION, '5.3.0') >= 0) //openssl_encrypt requires at least 5.3.0
		{
			if(function_exists('openssl_encrypt')) 
			{
				$encrypted = version_compare(PHP_VERSION, '5.3.3') >= 0 ? openssl_encrypt($in, $encryptionMethod, $api_key, 0, '3j9hwG7uj8uvpRAT') : openssl_encrypt($in, $encryptionMethod, $api_key, 0);
				if(!$encrypted) return $is_email ? $in : base_convert($in, 10, 36);
			}
			else return $is_email ? $in : base_convert($in, 10, 36);
			
			$encrypted = str_replace('/', '892', $encrypted);
			$encrypted = str_replace('+', '763', $encrypted);
			$encrypted = str_replace('=', '', $encrypted);
			
			return $encrypted;
		}
		else return $is_email ? $in : base_convert($in, 10, 36);
	}
}
?>