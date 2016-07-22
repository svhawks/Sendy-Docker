<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../helpers/class.phpmailer.php');?>
<?php include('../helpers/short.php');?>
<?php
	
//variables
$campaign_id = mysqli_real_escape_string($mysqli, $_POST['campaign_id']);
$email_list_array = $_POST['email_list'];
$app = mysqli_real_escape_string($mysqli, $_POST['app']);
$offset = isset($_POST['offset']) ? mysqli_real_escape_string($mysqli, $_POST['offset']) : '';
$cron = $_POST['cron'];
$email_list = implode(",", $email_list_array);
$total_recipients = $_POST['total_recipients'];
$time = time();

//get user details
$q = 'SELECT name, username, send_rate, timezone FROM login WHERE id = '.get_app_info('main_userID');
$r = mysqli_query($mysqli, $q);
if ($r)
{
    while($row = mysqli_fetch_array($r))
    {
		$my_name = $row['name'];
		$my_email = $row['username'];
		$send_rate = $row['send_rate'];
		$user_timezone = $row['timezone'];
    }  
}

//get smtp settings
$q = 'SELECT smtp_host, smtp_port, smtp_ssl, smtp_username, smtp_password FROM apps WHERE id = '.mysqli_real_escape_string($mysqli, $_POST['app']).' AND userID = '.get_app_info('main_userID');
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0)
{
    while($row = mysqli_fetch_array($r))
    {
		$smtp_host = $row['smtp_host'];
		$smtp_port = $row['smtp_port'];
		$smtp_ssl = $row['smtp_ssl'];
		$smtp_username = $row['smtp_username'];
		$smtp_password = $row['smtp_password'];
    }  
}

//Check if monthly quota needs to be updated
$q = 'SELECT allocated_quota, current_quota FROM apps WHERE id = '.$app;
$r = mysqli_query($mysqli, $q);
if($r) 
{
	while($row = mysqli_fetch_array($r)) 
	{
		$allocated_quota = $row['allocated_quota'];
		$current_quota = $row['current_quota'];
		$updated_quota = $current_quota + $total_recipients;
	}
}
//Update quota if a monthly limit was set
if($allocated_quota!=-1)
{
	//if so, update quota
	$q = 'UPDATE apps SET current_quota = '.$updated_quota.' WHERE id = '.$app;
	mysqli_query($mysqli, $q);
}

//if CRON is setup, schedule email to send in the next 5 mins
if($cron)
{
	$the_date = '0';
	$timezone = '0';
	
	//Get number of recipients to send to
	$q = 'SELECT id FROM subscribers WHERE list in ('.$email_list.') AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1 GROUP BY email';
	$to_send = mysqli_num_rows(mysqli_query($mysqli, $q));
	
	//schedule email to send in the next 5 mins
	$q = 'UPDATE campaigns SET sent = "'.$time.'", to_send = '.$to_send.', send_date = "'.$the_date.'", lists = "'.$email_list.'", timezone = "'.$timezone.'" WHERE id = '.$campaign_id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		echo 'cron_send';
		exit;
	}
}
else
{
	ignore_user_abort(true);
	header("Content-Length: 0");
	header("Connection: close");
	flush();
	echo true;
}

//check if user wants to continue sending to the rest of the recipients because sending was incomplete
if($offset!='')
{
	//get currently unsubscribed
	$uc = 'SELECT id FROM subscribers WHERE unsubscribed = 1 AND last_campaign = '.$campaign_id;
    $currently_unsubscribed = mysqli_num_rows(mysqli_query($mysqli, $uc));
    //get currently bounced
	$bc = 'SELECT id FROM subscribers WHERE bounced = 1 AND last_campaign = '.$campaign_id;
    $currently_bounced = mysqli_num_rows(mysqli_query($mysqli, $bc));
    //get currently complaint
	$cc = 'SELECT id FROM subscribers WHERE complaint = 1 AND last_campaign = '.$campaign_id;
    $currently_complaint = mysqli_num_rows(mysqli_query($mysqli, $cc));
	//calculate offset (offset should exclude currently unsubscribed, bounced or complaint)
	$the_offset = ' OFFSET '.($offset-($currently_unsubscribed+$currently_bounced+$currently_complaint));
	$email_list = $_POST['email_list'];
}
else $the_offset = '';

//select campaign to send email
$q = 'SELECT from_name, from_email, reply_to, title, label, plain_text, html_text, query_string, to_send, recipients FROM campaigns WHERE id = '.$campaign_id.' AND userID = '.get_app_info('main_userID');
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0)
{
    while($row = mysqli_fetch_array($r))
    {
    	$from_name = stripslashes($row['from_name']);
    	$from_email = stripslashes($row['from_email']);
    	$reply_to = stripslashes($row['reply_to']);
		$title = stripslashes($row['title']);
		$campaign_title = $row['label']=='' ? $title : stripslashes(htmlentities($row['label'],ENT_QUOTES,"UTF-8"));
		$plain_text = stripslashes($row['plain_text']);
		$html = stripslashes($row['html_text']);
		$query_string = stripslashes($row['query_string']);
		$to_send = stripslashes($row['to_send']);
		$current_recipient_count = $row['recipients'];
    }  
}

//if sending campaign for the first time (not resend)
if($offset=='')
{
	//update campaign
	$q = 'UPDATE campaigns SET sent = "'.$time.'" WHERE id = '.$campaign_id.' AND userID = '.get_app_info('main_userID');
	$r = mysqli_query($mysqli, $q);
	if ($r){}	
	
	//Insert web version link
	if(strpos($html, '</webversion>')==true)
	{
		mysqli_query($mysqli, 'INSERT INTO links (campaign_id, link) VALUES ('.$campaign_id.', "'.get_app_info('path').'/w/'.short($campaign_id).'")');
	}
	
	//Insert into links
	$links = array();
	//extract all links from HTML
	preg_match_all('/href=["\']([^"\']+)["\']/i', $html, $matches, PREG_PATTERN_ORDER);
	$matches = array_unique($matches[1]);
	foreach($matches as $var)
	{    
		$var = $query_string!='' ? ((strpos($var,'?') !== false) ? $var.'&'.$query_string : $var.'?'.$query_string) : $var;
		if(substr($var, 0, 1)!="#" && substr($var, 0, 6)!="mailto" && substr($var, 0, 3)!="tel" && substr($var, 0, 3)!="sms" && substr($var, 0, 13)!="[unsubscribe]" && substr($var, 0, 12)!="[webversion]" && !strpos($var, 'fonts.googleapis.com'))
		{
	    	array_push($links, $var);
	    }
	}
	//extract unique links
	for($i=0;$i<count($links);$i++)
	{
	    $q = 'INSERT INTO links (campaign_id, link) VALUES ('.$campaign_id.', "'.$links[$i].'")';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	
	//Get and update number of recipients to send to
	$q = 'SELECT id FROM subscribers WHERE list in ('.$email_list.') AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1 GROUP BY email';
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    $to_send = mysqli_num_rows($r);		
		$q2 = 'UPDATE campaigns SET to_send = '.$to_send.', to_send_lists = "'.$email_list.'" WHERE id = '.$campaign_id;
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2){} 
	}
}
else
{
	//Reset timeout_check in case user clicks "Resume" button even though cron was used to send this campaign.
	//This should remove possibility of sending an email multiple times to the same recipient(s).
	mysqli_query($mysqli, 'UPDATE campaigns SET timeout_check = NULL WHERE id = '.$campaign_id.' AND userID = '.get_app_info('main_userID'));
}

//Replace links in newsletter and put tracking image
$q = 'SELECT id, name, email, list, custom_fields FROM subscribers WHERE list in ('.$email_list.') AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1 AND userID = '.get_app_info('main_userID').' GROUP BY email ORDER BY id ASC LIMIT 18446744073709551615'.$the_offset;
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0)
{
	$subscriber_id = '';
	$email = '';
	$subscriber_list = '';
	
    while($row = mysqli_fetch_array($r))
    {
    	//prevent execution timeout
    	set_time_limit(0);
    	
    	$subscriber_id = $row['id'];
		$name = trim($row['name']);
		$email = trim($row['email']);
		$subscriber_list = $row['list'];
		$custom_values = $row['custom_fields'];
		
		$html_treated = $html;
		$plain_treated = $plain_text;
		$title_treated = $title;
		
		//replace new links on HTML code
		$q2 = 'SELECT id, link FROM links WHERE campaign_id = '.$campaign_id;
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2 && mysqli_num_rows($r2) > 0)
		{			
		    while($row2 = mysqli_fetch_array($r2))
		    {
		    	$linkID = $row2['id'];
				if($query_string!='')
		    	{
			    	$link = (strpos($row2['link'],'?'.$query_string) !== false) ? str_replace('?'.$query_string, '', $row2['link']) : str_replace('&'.$query_string, '', $row2['link']);
		    	}
		    	else $link = $row2['link'];
				
				//replace new links on HTML code
		    	$html_treated = str_replace('href="'.$link.'"', 'href="'.get_app_info('path').'/l/'.short($subscriber_id).'/'.short($linkID).'/'.short($campaign_id).'"', $html_treated);
		    	$html_treated = str_replace('href=\''.$link.'\'', 'href="'.get_app_info('path').'/l/'.short($subscriber_id).'/'.short($linkID).'/'.short($campaign_id).'"', $html_treated);
		    	
		    	//replace new links on Plain Text code
		    	$plain_treated = str_replace($link, get_app_info('path').'/l/'.short($subscriber_id).'/'.short($linkID).'/'.short($campaign_id), $plain_treated);
		    }  
		}
		
		//tags for subject
		preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $title_treated, $matches_var, PREG_PATTERN_ORDER);
		preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $title_treated, $matches_val, PREG_PATTERN_ORDER);
		preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $title_treated, $matches_all, PREG_PATTERN_ORDER);
		$matches_var = $matches_var[1];
		$matches_val = $matches_val[1];
		$matches_all = $matches_all[1];
		for($i=0;$i<count($matches_var);$i++)
		{   
			$field = $matches_var[$i];
			$fallback = $matches_val[$i];
			$tag = $matches_all[$i];
			
			//if tag is Name
			if($field=='Name')
			{
				if($name=='')
					$title_treated = str_replace($tag, $fallback, $title_treated);
				else
					$title_treated = str_replace($tag, $row[strtolower($field)], $title_treated);
			}
			else //if not 'Name', it's a custom field
			{
				//if subscriber has no custom fields, use fallback
				if($custom_values=='')
					$title_treated = str_replace($tag, $fallback, $title_treated);
				//otherwise, replace custom field tag
				else
				{					
					$q5 = 'SELECT custom_fields FROM lists WHERE id = '.$subscriber_list;
					$r5 = mysqli_query($mysqli, $q5);
					if ($r5)
					{
					    while($row2 = mysqli_fetch_array($r5)) $custom_fields = $row2['custom_fields'];
					    $custom_fields_array = explode('%s%', $custom_fields);
					    $custom_values_array = explode('%s%', $custom_values);
					    $cf_count = count($custom_fields_array);
					    $k = 0;
					    
					    for($j=0;$j<$cf_count;$j++)
					    {
						    $cf_array = explode(':', $custom_fields_array[$j]);
						    $key = str_replace(' ', '', $cf_array[0]);
						    
						    //if tag matches a custom field
						    if($field==$key)
						    {
						    	//if custom field is empty, use fallback
						    	if($custom_values_array[$j]=='')
							    	$title_treated = str_replace($tag, $fallback, $title_treated);
						    	//otherwise, use the custom field value
						    	else
						    	{
						    		//if custom field is of 'Date' type, format the date
						    		if($cf_array[1]=='Date')
							    		$title_treated = str_replace($tag, strftime("%a, %b %d, %Y", $custom_values_array[$j]), $title_treated);
						    		//otherwise just replace tag with custom field value
						    		else
								    	$title_treated = str_replace($tag, $custom_values_array[$j], $title_treated);
						    	}
						    }
						    else
						    	$k++;
					    }
					    if($k==$cf_count)
					    	$title_treated = str_replace($tag, $fallback, $title_treated);
					}
				}
			}
		}
		
		//tags for HTML
		preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $html_treated, $matches_var, PREG_PATTERN_ORDER);
		preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $html_treated, $matches_val, PREG_PATTERN_ORDER);
		preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $html_treated, $matches_all, PREG_PATTERN_ORDER);
		$matches_var = $matches_var[1];
		$matches_val = $matches_val[1];
		$matches_all = $matches_all[1];
		for($i=0;$i<count($matches_var);$i++)
		{   
			$field = $matches_var[$i];
			$fallback = $matches_val[$i];
			$tag = $matches_all[$i];
			
			//if tag is Name
			if($field=='Name')
			{
				if($name=='')
					$html_treated = str_replace($tag, $fallback, $html_treated);
				else
					$html_treated = str_replace($tag, $row[strtolower($field)], $html_treated);
			}
			else //if not 'Name', it's a custom field
			{
				//if subscriber has no custom fields, use fallback
				if($custom_values=='')
					$html_treated = str_replace($tag, $fallback, $html_treated);
				//otherwise, replace custom field tag
				else
				{					
					$q5 = 'SELECT custom_fields FROM lists WHERE id = '.$subscriber_list;
					$r5 = mysqli_query($mysqli, $q5);
					if ($r5)
					{
					    while($row2 = mysqli_fetch_array($r5)) $custom_fields = $row2['custom_fields'];
					    $custom_fields_array = explode('%s%', $custom_fields);
					    $custom_values_array = explode('%s%', $custom_values);
					    $cf_count = count($custom_fields_array);
					    $k = 0;
					    
					    for($j=0;$j<$cf_count;$j++)
					    {
						    $cf_array = explode(':', $custom_fields_array[$j]);
						    $key = str_replace(' ', '', $cf_array[0]);
						    
						    //if tag matches a custom field
						    if($field==$key)
						    {
						    	//if custom field is empty, use fallback
						    	if($custom_values_array[$j]=='')
							    	$html_treated = str_replace($tag, $fallback, $html_treated);
						    	//otherwise, use the custom field value
						    	else
						    	{
						    		//if custom field is of 'Date' type, format the date
						    		if($cf_array[1]=='Date')
							    		$html_treated = str_replace($tag, strftime("%a, %b %d, %Y", $custom_values_array[$j]), $html_treated);
						    		//otherwise just replace tag with custom field value
						    		else
								    	$html_treated = str_replace($tag, $custom_values_array[$j], $html_treated);
						    	}
						    }
						    else
						    	$k++;
					    }
					    if($k==$cf_count)
					    	$html_treated = str_replace($tag, $fallback, $html_treated);
					}
				}
			}
		}
		//tags for Plain text
		preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $plain_treated, $matches_var, PREG_PATTERN_ORDER);
		preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $plain_treated, $matches_val, PREG_PATTERN_ORDER);
		preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $plain_treated, $matches_all, PREG_PATTERN_ORDER);
		$matches_var = $matches_var[1];
		$matches_val = $matches_val[1];
		$matches_all = $matches_all[1];
		for($i=0;$i<count($matches_var);$i++)
		{   
			$field = $matches_var[$i];
			$fallback = $matches_val[$i];
			$tag = $matches_all[$i];
			
			//if tag is Name
			if($field=='Name')
			{
				if($name=='')
					$plain_treated = str_replace($tag, $fallback, $plain_treated);
				else
					$plain_treated = str_replace($tag, $row[strtolower($field)], $plain_treated);
			}
			else //if not 'Name', it's a custom field
			{
				//if subscriber has no custom fields, use fallback
				if($custom_values=='')
					$plain_treated = str_replace($tag, $fallback, $plain_treated);
				//otherwise, replace custom field tag
				else
				{					
					$q5 = 'SELECT custom_fields FROM lists WHERE id = '.$subscriber_list;
					$r5 = mysqli_query($mysqli, $q5);
					if ($r5)
					{
					    while($row2 = mysqli_fetch_array($r5)) $custom_fields = $row2['custom_fields'];
					    $custom_fields_array = explode('%s%', $custom_fields);
					    $custom_values_array = explode('%s%', $custom_values);
					    $cf_count = count($custom_fields_array);
					    $k = 0;
					    
					    for($j=0;$j<$cf_count;$j++)
					    {
						    $cf_array = explode(':', $custom_fields_array[$j]);
						    $key = str_replace(' ', '', $cf_array[0]);
						    
						    //if tag matches a custom field
						    if($field==$key)
						    {
						    	//if custom field is empty, use fallback
						    	if($custom_values_array[$j]=='')
									$plain_treated = str_replace($tag, $fallback, $plain_treated);
						    	//otherwise, use the custom field value
						    	else
						    	{
						    		//if custom field is of 'Date' type, format the date
						    		if($cf_array[1]=='Date')
										$plain_treated = str_replace($tag, strftime("%a, %b %d, %Y", $custom_values_array[$j]), $plain_treated);
						    		//otherwise just replace tag with custom field value
						    		else
										$plain_treated = str_replace($tag, $custom_values_array[$j], $plain_treated);
						    	}
						    }
						    else
						    	$k++;
					    }
					    if($k==$cf_count)
					    	$plain_treated = str_replace($tag, $fallback, $plain_treated);
					}
				}
			}
		}
		
		//set web version links
    	$html_treated = str_replace('<webversion', '<a href="'.get_app_info('path').'/w/'.short($subscriber_id).'/'.short($subscriber_list).'/'.short($campaign_id).'" ', $html_treated);
    	$html_treated = str_replace('</webversion>', '</a>', $html_treated);
    	$html_treated = str_replace('[webversion]', get_app_info('path').'/w/'.short($subscriber_id).'/'.short($subscriber_list).'/'.short($campaign_id), $html_treated);
    	$plain_treated = str_replace('[webversion]', get_app_info('path').'/w/'.short($subscriber_id).'/'.short($subscriber_list).'/'.short($campaign_id), $plain_treated);
    	
    	//set unsubscribe links
    	$html_treated = str_replace('<unsubscribe', '<a href="'.get_app_info('path').'/unsubscribe/'.short($email).'/'.short($subscriber_list).'/'.short($campaign_id).'" ', $html_treated);
    	$html_treated = str_replace('</unsubscribe>', '</a>', $html_treated);
    	$html_treated = str_replace('[unsubscribe]', get_app_info('path').'/unsubscribe/'.short($email).'/'.short($subscriber_list).'/'.short($campaign_id), $html_treated);
    	$plain_treated = str_replace('[unsubscribe]', get_app_info('path').'/unsubscribe/'.short($email).'/'.short($subscriber_list).'/'.short($campaign_id), $plain_treated);
    	
    	//Email tag
		$html_treated = str_replace('[Email]', $email, $html_treated);
		$plain_treated = str_replace('[Email]', $email, $plain_treated);
		$title_treated = str_replace('[Email]', $email, $title_treated);
    	
    	//convert date tags
		date_default_timezone_set($timezone!='' ? $timezone : $user_timezone);
		$today = time();
		$currentdaynumber = strftime('%d', $today);
		$currentday = strftime('%A', $today);
		$currentmonthnumber = strftime('%m', $today);
		$currentmonth = strftime('%B', $today);
		$currentyear = strftime('%Y', $today);
		$unconverted_date = array('[currentdaynumber]', '[currentday]', '[currentmonthnumber]', '[currentmonth]', '[currentyear]');
		$converted_date = array($currentdaynumber, $currentday, $currentmonthnumber, $currentmonth, $currentyear);
		$html_treated = str_replace($unconverted_date, $converted_date, $html_treated);
		$plain_treated = str_replace($unconverted_date, $converted_date, $plain_treated);
		$title_treated = str_replace($unconverted_date, $converted_date, $title_treated);
    	
    	//add tracking 1 by 1px image
		$html_treated .= '<img src="'.get_app_info('path').'/t/'.short($campaign_id).'/'.short($subscriber_id).'" alt="" style="width:1px;height:1px;"/>';
		
		//send email
		$mail = new PHPMailer();
		if(get_app_info('s3_key')!='' && get_app_info('s3_secret')!='')
		{
			//if there is an attachment, don't use curl_multi
			if(file_exists('../../uploads/attachments/'.$campaign_id))
				$mail->IsAmazonSES(false, $campaign_id, $subscriber_id, get_app_info('timezone'));
			//otherwise send with curl_multi
			else
				$mail->IsAmazonSES(true, $campaign_id, $subscriber_id, get_app_info('timezone'), $send_rate);
			$mail->AddAmazonSESKey(get_app_info('s3_key'), get_app_info('s3_secret'));
		}
		else if($smtp_host!='' && $smtp_port!='' && $smtp_username!='' && $smtp_password!='')
		{
			$mail->IsSMTP();
			$mail->SMTPDebug = 0;
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = $smtp_ssl;
			$mail->Host = $smtp_host;
			$mail->Port = $smtp_port; 
			$mail->Username = $smtp_username;  
			$mail->Password = $smtp_password;
		}
		$mail->CharSet	  =	"UTF-8";
		$mail->From       = $from_email;
		$mail->FromName   = $from_name;
		$mail->Subject = $title_treated;
		$mail->AltBody = $plain_treated;
		$mail->MsgHTML($html_treated);
		$mail->AddAddress($email, $name);
		$mail->AddReplyTo($reply_to, $from_name);
		$mail->AddCustomHeader('List-Unsubscribe: <'.APP_PATH.'/unsubscribe/'.short($email).'/'.short($subscriber_list).'/'.short($campaign_id).'>');
		//check if attachments are available for this campaign to attach
		if(file_exists('../../uploads/attachments/'.$campaign_id))
		{
			foreach(glob('../../uploads/attachments/'.$campaign_id.'/*') as $attachment){
				if(file_exists($attachment))
				    $mail->AddAttachment($attachment);
			}
		}
		$mail->Send();
		
		//increment recipient count if not using AWS or SMTP
		if(get_app_info('s3_key')=='' && get_app_info('s3_secret')=='')
		{
			//increment recipients number in campaigns table
			$q5 = 'UPDATE campaigns SET recipients = recipients+1 WHERE id = '.$campaign_id;
			mysqli_query($mysqli, $q5);
		}
    }  
    
    //====================== Send remaining in queue ======================//
    $headers = array();
    $date_value = date(DATE_RFC2822);
    $headers[] = "Date: {$date_value}";				
    $signature = base64_encode(hash_hmac("sha1", $date_value, get_app_info('s3_secret'), TRUE));				
    $headers[] = "X-Amzn-Authorization: AWS3-HTTPS "
        ."AWSAccessKeyId=".get_app_info('s3_key').","
        ."Algorithm=HmacSHA1,Signature={$signature}";
    $headers[] = "Content-Type: application/x-www-form-urlencoded";		
    
    $q4 = 'SELECT id, query_str, subscriber_id FROM queue WHERE campaign_id = '.$campaign_id.' AND sent = 0';
    $r4 = mysqli_query($mysqli, $q4);
    if ($r4 && mysqli_num_rows($r4) > 0)
    {
        while($row = mysqli_fetch_array($r4))
        {
        	$request_url = 'https://'.get_app_info('ses_endpoint');
    		$queue_id = $row['id'];
    		$query_str = $row['query_str'];
    		$subscriber_id = $row['subscriber_id'];
    		
    		//Get server path
			$server_path_array = explode('includes/create/send-now.php', $_SERVER['SCRIPT_FILENAME']);
		    $server_path = $server_path_array[0];
    		
    		//send remaining in queue
	        $cr = curl_init();
	        curl_setopt($cr, CURLOPT_URL, $request_url);
	        curl_setopt($cr, CURLOPT_POST, $query_str);
	        curl_setopt($cr, CURLOPT_POSTFIELDS, $query_str);
	        curl_setopt($cr, CURLOPT_HTTPHEADER, $headers);
	        curl_setopt($cr, CURLOPT_HEADER, TRUE);
	        curl_setopt($cr, CURLOPT_RETURNTRANSFER, TRUE);
	        curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($cr, CURLOPT_CAINFO, $server_path.'certs/cacert.pem');
	
	        // Make the request and fetch response.
	        $response = curl_exec($cr);
	        
	        //Get message ID from response
	        $messageIDArray = explode('<MessageId>', $response);
	        $messageIDArray2 = explode('</MessageId>', $messageIDArray[1]);
	        $messageID = $messageIDArray2[0];
	        
	        $response_http_status_code = curl_getinfo($cr, CURLINFO_HTTP_CODE);
	        
	        if ($response_http_status_code !== 200)
	        {
		        $q7 = 'SELECT errors FROM campaigns WHERE id = '.$campaign_id;
	        	$r7 = mysqli_query($mysqli, $q7);
	        	if ($r7)
	        	{
	        	    while($row = mysqli_fetch_array($r7))
	        	    {
	        			$errors = $row['errors'];
	        			
	        			if($errors=='')
							$val = $subscriber_id.':'.$response_http_status_code;
						else
						{
							$errors .= ','.$subscriber_id.':'.$response_http_status_code;
							$val = $errors;
						}
	        	    }  
	        	}
	
		        //update campaigns' errors column
		        $q6 = 'UPDATE campaigns SET errors = "'.$val.'" WHERE id = '.$campaign_id;
				mysqli_query($mysqli, $q6);
	        }
	        else
	        {
	        	//increment recipients number in campaigns table
				$q6 = 'UPDATE campaigns SET recipients = recipients+1 WHERE recipients < to_send AND id = '.$campaign_id;
				mysqli_query($mysqli, $q6);
				
		        $q5 = 'UPDATE queue SET sent = 1, query_str = NULL WHERE id = '.$queue_id;
				mysqli_query($mysqli, $q5);
				
				//update messageID of subscriber
				$q14 = 'UPDATE subscribers SET messageID = "'.$messageID.'" WHERE id = '.$subscriber_id;
				mysqli_query($mysqli, $q14);
	        }
        }  
    }
    else
	{
		$q12 = 'UPDATE campaigns SET to_send = (SELECT recipients) WHERE id = '.$campaign_id;
		$r12 = mysqli_query($mysqli, $q12);
		if ($r12) 
		{
			$q13 = 'SELECT recipients FROM campaigns WHERE id = '.$campaign_id;
			$r13 = mysqli_query($mysqli, $q13);
			if ($r13) while($row = mysqli_fetch_array($r13)) $current_recipient_count = $row['recipients'];
			$to_send = $current_recipient_count;
		}
	}
    //======================= /Send remaining in queue ======================//    
}
else
{
	$q12 = 'UPDATE campaigns SET to_send = '.$current_recipient_count.' WHERE id = '.$campaign_id;
	$r12 = mysqli_query($mysqli, $q12);
	if ($r12) $to_send = $current_recipient_count;
}


//=========================== Post processing ===========================//
$q8 = 'SELECT recipients FROM campaigns where id = '.$campaign_id;
$r8 = mysqli_query($mysqli, $q8);
if ($r8) while($row = mysqli_fetch_array($r8)) $no_of_recipients = $row['recipients'];
if($no_of_recipients >= $to_send)
{
    //tags for subject to me
	preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+),\s*fallback=/i', $title, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*)\]/i', $title, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._\-\:|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._\-\:|\/?<>~`"\'\s]*\])/i', $title, $matches_all, PREG_PATTERN_ORDER);
	$matches_var = $matches_var[1];
	$matches_val = $matches_val[1];
	$matches_all = $matches_all[1];
	for($i=0;$i<count($matches_var);$i++)
	{		
		$field = $matches_var[$i];
		$fallback = $matches_val[$i];
		$tag = $matches_all[$i];
		//for each match, replace tag with fallback
		$title = str_replace($tag, $fallback, $title);
	}
	$title = str_replace('[Email]', $from_email, $title);
	$title = str_replace($unconverted_date, $converted_date, $title);
	
    $title_to_me = '['._('Campaign sent').'] '.$title;
    
    $app_path = APP_PATH;
				    
    $message_to_me_plain = _('Your campaign has been successfully sent to')." $no_of_recipients "._('recipients')."!

"._('View report')." - $app_path/report?i=$app&c=$campaign_id";

    $message_to_me_html = "
    <div style=\"margin: -10px -10px; padding:50px 30px 50px 30px; height:100%;\">
		<div style=\"margin:0 auto; max-width:660px;\">
			<div style=\"float: left; background-color: #FFFFFF; padding:10px 30px 10px 30px; border: 1px solid #DDDDDD;\">
				<div style=\"float: left; max-width: 106px; margin: 10px 20px 15px 0;\">
					<img src=\"$app_path/img/email-icon.gif\" style=\"width: 100px;\"/>
				</div>
				<div style=\"float: left; max-width:470px;\">
					<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
						<strong style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 18px;\">"._('Your campaign has been sent')."!</strong>
					</p>	
					<div style=\"line-height: 21px; min-height: 100px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
						<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">"._('Your campaign has been successfully sent to')." $no_of_recipients "._('recipients')."!</p>
						<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px; margin-bottom: 25px; background-color:#EDEDED; padding: 15px;\">
							<strong>"._('Campaign').": </strong>$campaign_title<br/>
							<strong>"._('Recipients').": </strong>$no_of_recipients<br/>
							<strong>"._('View report').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"$app_path/report?i=$app&c=$campaign_id\">$app_path/report?i=$app&c=$campaign_id</a>
						</p>
						<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
    ";
	
	$q4 = 'UPDATE campaigns SET recipients = '.$to_send.' WHERE id = '.$campaign_id.' AND userID = '.get_app_info('main_userID');
	mysqli_query($mysqli, $q4);
	
	$q9 = 'DELETE FROM queue WHERE campaign_id = '.$campaign_id;
	mysqli_query($mysqli, $q9);
	
	$q11 = 'SELECT errors, to_send_lists FROM campaigns WHERE id = '.$campaign_id;
	$r11 = mysqli_query($mysqli, $q11);
	if ($r11) 
	{
		while($row = mysqli_fetch_array($r11))
		{
			$error_recipients_ids = $row['errors'];
			$tsl = $row['to_send_lists'];
		}
		
		if($error_recipients_ids=='')
		{
			$q10 = 'UPDATE subscribers SET bounce_soft = 0 WHERE list IN ('.$tsl.')';
			mysqli_query($mysqli, $q10);
		}
		else
		{
			$error_recipients_ids_array = explode(',', $error_recipients_ids);
			$eid_array = array();
			foreach($error_recipients_ids_array as $id_val)
			{
				$id_val_array = explode(':', $id_val);
				array_push($eid_array, $id_val_array[0]);
			}
			$error_recipients_ids = implode(',', $eid_array);
			$q10 = 'UPDATE subscribers SET bounce_soft = 0 WHERE list IN ('.$tsl.') AND id NOT IN ('.$error_recipients_ids.')';
			mysqli_query($mysqli, $q10);
		}
	}
	
	//send email to me
	$mail2 = new PHPMailer();	
	if(get_app_info('s3_key')!='' && get_app_info('s3_secret')!='')
	{
		$mail2->IsAmazonSES();
		$mail2->AddAmazonSESKey(get_app_info('s3_key'), get_app_info('s3_secret'));
	}
	else if($smtp_host!='' && $smtp_port!='' && $smtp_ssl!='' && $smtp_username!='' && $smtp_password!='')
	{
		$mail2->IsSMTP();
		$mail2->SMTPDebug = 0;
		$mail2->SMTPAuth = true;
		$mail2->SMTPSecure = $smtp_ssl;
		$mail2->Host = $smtp_host;
		$mail2->Port = $smtp_port; 
		$mail2->Username = $smtp_username;  
		$mail2->Password = $smtp_password;
	}
	$mail2->Timezone   = get_app_info('timezone');
	$mail2->CharSet	  =	"UTF-8";
	$mail2->From       = $from_email;
	$mail2->FromName   = $from_name;
	$mail2->Subject = $title_to_me;
	$mail2->AltBody = $message_to_me_plain;
	$mail2->MsgHTML($message_to_me_html);
	$mail2->AddAddress($from_email, $from_name); //send email to brand account owner
	$mail2->AddBCC($my_email, $my_name); //send email to main account owner
	$mail2->Send();	
	
	//quit
	exit;
}

//========================== /Post processing ===========================//

?>
