<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$id = mysqli_real_escape_string($mysqli, $_POST['id']);
	$app_name = mysqli_real_escape_string($mysqli, $_POST['app_name']);
	$from_name = mysqli_real_escape_string($mysqli, $_POST['from_name']);
	$from_email = mysqli_real_escape_string($mysqli, $_POST['from_email']);
	$reply_to = mysqli_real_escape_string($mysqli, $_POST['reply_to']);
	$allowed_attachments = mysqli_real_escape_string($mysqli, $_POST['allowed_attachments']);
	$currency = mysqli_real_escape_string($mysqli, $_POST['currency']);
	$delivery_fee = mysqli_real_escape_string($mysqli, $_POST['delivery_fee']);
	$cost_per_recipient = mysqli_real_escape_string($mysqli, $_POST['cost_per_recipient']);
	$smtp_host = mysqli_real_escape_string($mysqli, $_POST['smtp_host']);
	$smtp_port = mysqli_real_escape_string($mysqli, $_POST['smtp_port']);
	$smtp_ssl = mysqli_real_escape_string($mysqli, $_POST['smtp_ssl']);
	$smtp_username = mysqli_real_escape_string($mysqli, $_POST['smtp_username']);
	$smtp_password = mysqli_real_escape_string($mysqli, $_POST['smtp_password']);
	$login_email = mysqli_real_escape_string($mysqli, $_POST['login_email']);
	$language = mysqli_real_escape_string($mysqli, $_POST['language']);
	$choose_limit = mysqli_real_escape_string($mysqli, $_POST['choose-limit']);
	if($choose_limit=='custom')
	{
		$reset_on_day = mysqli_real_escape_string($mysqli, $_POST['reset-on-day']);
		$monthly_limit = $_POST['monthly-limit']=='' ? 0 : mysqli_real_escape_string($mysqli, $_POST['monthly-limit']);
		$current_limit = $_POST['current-limit']=='' ? 0 : mysqli_real_escape_string($mysqli, $_POST['current-limit']);
		$current_limit = ', current_quota = '.$current_limit;
		
		//Calculate month of next reset
		$today_unix_timestamp = time();
		$day_today = strftime("%e", $today_unix_timestamp);
		$month_today = strftime("%b", $today_unix_timestamp);
		$month_next = strtotime('1 '.$month_today.' +1 month');
		$month_next = strftime("%b", $month_next);
		if($day_today<$reset_on_day) $month_to_reset = $month_today;
		else $month_to_reset = $month_next;
		
		$q = 'SELECT month_of_next_reset FROM apps WHERE id = '.$id;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $monr = $row['month_of_next_reset'];
		if($monr=='') $month_of_next_reset = ', month_of_next_reset = "'.$month_to_reset.'"';
		else $month_of_next_reset = ''; //month_of_next_reset won't be changed when re-saving
	}
	else if($choose_limit=='unlimited')
	{
		$monthly_limit = -1;
		$reset_on_day = 1;
		$month_of_next_reset = ', month_of_next_reset = ""';
		$current_limit = '';
	}
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	if($smtp_password=='')
		$q = 'UPDATE apps SET app_name = "'.$app_name.'", from_name = "'.$from_name.'", from_email = "'.$from_email.'", reply_to = "'.$reply_to.'", allowed_attachments = "'.$allowed_attachments.'", currency = "'.$currency.'", delivery_fee = "'.$delivery_fee.'", cost_per_recipient = "'.$cost_per_recipient.'", smtp_host = "'.$smtp_host.'", smtp_port = "'.$smtp_port.'", smtp_ssl = "'.$smtp_ssl.'", smtp_username = "'.$smtp_username.'", allocated_quota = "'.$monthly_limit.'", day_of_reset = "'.$reset_on_day.'" '.$month_of_next_reset.' '.$current_limit.' WHERE id = '.$id.' AND userID = '.get_app_info('userID');
	else
		$q = 'UPDATE apps SET app_name = "'.$app_name.'", from_name = "'.$from_name.'", from_email = "'.$from_email.'", reply_to = "'.$reply_to.'", allowed_attachments = "'.$allowed_attachments.'", currency = "'.$currency.'", delivery_fee = "'.$delivery_fee.'", cost_per_recipient = "'.$cost_per_recipient.'", smtp_host = "'.$smtp_host.'", smtp_port = "'.$smtp_port.'", smtp_ssl = "'.$smtp_ssl.'", smtp_username = "'.$smtp_username.'", smtp_password = "'.$smtp_password.'", allocated_quota = "'.$monthly_limit.'", day_of_reset = "'.$reset_on_day.'" '.$month_of_next_reset.' '.$current_limit.' WHERE id = '.$id.' AND userID = '.get_app_info('userID');
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		//update email, language and company name in login
		$q2 = 'UPDATE login SET username = "'.$login_email.'", language = "'.$language.'", company = "'.$app_name.'" WHERE app = '.$id;
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2)
		{
			//Upload brand logo
			//Create /logos/ directory in /uploads/ if it doesn't exist
			if(!file_exists("../../uploads/logos")) 
			{
				//Create /csvs/ directory
				if(!mkdir("../../uploads/logos", 0777))
				{
					//Could not create directory '/logos/'. 
					//Please make sure permissions in /uploads/ folder is set to 777. 
					header("Location: ".get_app_info('path').'/edit-brand?i='.$id.'&e=1');
					exit;
				}
				else
				{
					//chmod uploaded file
					chmod("../../uploads/logos",0777);
				}
			}
			
			//Upload logo
			$file = $_FILES['logo']['tmp_name'];
			$file_name = $_FILES['logo']['name'];
			if($file_name!='') //if an image file was uploaded, upload the image
			{
				$extension_explode = explode('.', $file_name);
				$extension = $extension_explode[count($extension_explode)-1];
				$time = time();
				chmod("../../uploads",0777);
				
				//Check filetype
				$allowed = array("jpeg", "jpg", "gif", "png");
				if(in_array($extension, $allowed)) //if file is an image, allow upload
				{
					//Upload file
					if(!move_uploaded_file($file, '../../uploads/logos/'.$id.'.'.$extension))
					{
						//Could not upload brand logo image to '/logos/' folder. 
						//Please make sure permissions in /uploads/ folder is set to 777. 
						//Then remove the /logos/ folder in the /uploads/ folder and try again.
						header("Location: ".get_app_info('path').'/edit-brand?i='.$id.'&e=3');
					}
					else
					{
						//Update brand_logo_filename in database
						mysqli_query($mysqli, 'UPDATE apps SET brand_logo_filename = "'.$id.'.'.$extension.'" WHERE id = '.$id);
					}
				}
				else 
				{
					//Please upload only these image formats: jpeg, jpg, gif and png.
					header("Location: ".get_app_info('path').'/edit-brand?i='.$id.'&e=2');
					exit;
				}
			}
			
			header("Location: ".get_app_info('path'));
		}
	}
?>