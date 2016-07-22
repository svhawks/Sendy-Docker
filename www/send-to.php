<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/create/main.php');?>
<?php include('includes/helpers/short.php');?>
<?php include('includes/create/timezone.php');?>

<?php
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/send-to?i='.get_app_info('restricted_to_app').'&c='.$_GET['c'].'"</script>';
			exit;
		}
	}
	
	//Check if user is using Amazon SES to send emails
	$aws_keys_available = get_app_info('s3_key')!='' && get_app_info('s3_secret')!='' ? 'true' : 'false';
?>

<?php include('js/create/main.php');?>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/datepicker.js"></script>
<link rel="stylesheet" type="text/css" href="css/datepicker.css" />
<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span3">
    	<div>
	    	<p class="lead"><?php echo get_app_data('app_name');?></p>
    	</div>
    	
    	<div class="alert alert-success" id="test-send" style="display:none;">
		  <button class="close" onclick="$('.alert-success').hide();">×</button>
		  <strong><?php echo _('Email has been sent!');?></strong>
		</div>
		
		<div class="alert alert-error" id="test-send-error" style="display:none;">
		  <button class="close" onclick="$('.alert-error').hide();">×</button>
		  <strong><?php echo _('Sorry, unable to send. Please try again later!');?></strong>
		</div>
		
		<div class="alert alert-error" id="test-send-error2" style="display:none;">
		  <button class="close" onclick="$('.alert-error').hide();">×</button>
		  <p id="test-send-error2-msg"></p>
		</div>
		
		<?php
			//IDs
			$cid = isset($_GET['c']) && is_numeric($_GET['c']) ? mysqli_real_escape_string($mysqli, $_GET['c']) : exit;
			$aid = isset($_GET['i']) && is_numeric($_GET['i']) ? mysqli_real_escape_string($mysqli, $_GET['i']) : exit;
		
	    	//check if cron is set up and get main user's email address
	    	$q = 'SELECT username, cron FROM login WHERE id = '.get_app_info('main_userID');
	    	$r = mysqli_query($mysqli, $q);
	    	if ($r)
	    	{
	    	    while($row = mysqli_fetch_array($r))
	    	    {
	    			$cron = $row['cron'];
	    			$main_user_email = $row['username'];
	    	    }  
	    	}
	    	
	    	$timezone = get_app_info('timezone');
	    	
	    	//get scheduled settings
		    $q = 'SELECT send_date, timezone, from_email FROM campaigns WHERE id = '.$cid;
  			$r = mysqli_query($mysqli, $q);
  			if ($r)
  			{
  			    while($row = mysqli_fetch_array($r))
  			    {
  					$send_date = $row['send_date'];
  					if($row['timezone']!='')
						$timezone = $row['timezone'];
					$from_email = $row['from_email'];
					$from_email_domain_array = explode('@', $from_email);
					$from_email_domain = $from_email_domain_array[1];
  					date_default_timezone_set($timezone);
		    		$day = strftime("%d", $send_date);
		    		$month = strftime("%m", $send_date);
		    		$year = strftime("%Y", $send_date);
		    		$hour = strftime("%l", $send_date);
		    		$minute = strftime("%M", $send_date);
		    		$ampm = strtolower(strftime("%p", $send_date));
		    		$the_date = $month.'-'.$day.'-'.$year;
  					
  					if($send_date=='')
  					{
	  					$send_newsletter_now = '';
	  					$send_newsletter_text = _('Schedule this campaign?');
	  					$schedule_form_style = 'style="display:none; width:260px;"';
  					}
  					else
  					{
	  					$send_newsletter_now = 'style="display:none;"';
	  					$send_newsletter_text = '&larr; '._('Back');
	  					$schedule_form_style = 'style="width:260px;"';
  					}
  			    }  
  			}
  			
  			//Check if from email is verified in SES console
  			if(!get_app_info('is_sub_user') && get_app_info('s3_key')!='' && get_app_info('s3_secret')!='')
  			{
	  			require_once('includes/helpers/ses.php');
				$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
				$v_addresses = $ses->ListIdentities();
				
				if(!$v_addresses)
				{
					//Unable to commuincate with Amazon SES API
					echo '<div class="alert alert-danger">
							<p><strong>'._('Unable to communicate with Amazon SES API').'</strong></p>
							<p>'._('Visit your "Brands" page by clicking your company\'s name at the top left of the screen. Then check the instructions on the left sidebar on how to resolve this issue').'</p>
						</div>
						<script type="text/javascript">
							$(document).ready(function() {
								$("#real-btn").addClass("disabled");
								$("#test-send-btn").addClass("disabled");
								$("#schedule-btn").addClass("disabled");
								$("#real-btn").attr("disabled", "disabled");
								$("#test-send-btn").attr("disabled", "disabled");
								$("#schedule-btn").attr("disabled", "disabled");
								$("#email_list").attr("disabled", "disabled");
							});
						</script>';
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
					
					//$from_email_verification_status = $getIdentityVerificationAttributes['VerificationStatus'];
					
					if(!in_array($from_email, $verifiedEmailsArray) && !in_array($from_email_domain, $verifiedDomainsArray))
					{
						//Attempt to verify the email address, a verification email will be sent to the 'From email' address by Amazon SES
						$ses->verifyEmailAddress($from_email);
						
						//From email address or domain is not verified in SES console
						echo '<div class="alert alert-danger">
								<p><strong>'._('Unverified \'From email\'').': '.$from_email.'</strong></p>
								<p>'._('Your \'From email\' or its domain is not verified in your Amazon SES console. We have just sent your \'From email\' address to Amazon SES for verification. An email from Amazon is sent to your \'From email\' address with a confirmation link to complete the verification. Click the link to complete the verification, then refresh this page.').'</p>
							</div>
							<script type="text/javascript">
								$(document).ready(function() {
									$("#real-btn").addClass("disabled");
									$("#test-send-btn").addClass("disabled");
									$("#schedule-btn").addClass("disabled");
									$("#real-btn").attr("disabled", "disabled");
									$("#test-send-btn").attr("disabled", "disabled");
									$("#schedule-btn").attr("disabled", "disabled");
									$("#email_list").attr("disabled", "disabled");
								});
							</script>';
					}
					else if(!$veriStatus)
					{
						echo '
							<div class="alert alert-danger">
								<p><strong>\''.$from_email.'\' '._('or').' \''.$from_email_domain.'\' '._('is pending verification in your Amazon SES console').'</strong></p>
								<p>'._('Your \'From email\' or its domain is pending verification in your Amazon SES console. Please complete the verification then refresh this page to proceed.').'</p>
							</div>
							<script type="text/javascript">
								$(document).ready(function() {
									$("#real-btn").addClass("disabled");
									$("#test-send-btn").addClass("disabled");
									$("#schedule-btn").addClass("disabled");
									$("#real-btn").attr("disabled", "disabled");
									$("#test-send-btn").attr("disabled", "disabled");
									$("#schedule-btn").attr("disabled", "disabled");
									$("#email_list").attr("disabled", "disabled");
								});
							</script>';
					}
					else
					{
						//Set email feedback forwarding to false
						$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
						$ses->setIdentityFeedbackForwardingEnabled($from_email, 'false');
						$ses->setIdentityFeedbackForwardingEnabled($from_email_domain, 'false');
					}
				}
			}			
	    ?>
    	
    	<h2><?php echo _('Test send this campaign');?></h2><br/>
	    <form action="<?php echo get_app_info('path')?>/includes/create/test-send.php" method="POST" accept-charset="utf-8" class="form-vertical" id="test-form">	    
	    	<label class="control-label" for="test_email"><?php echo _('Test email(s)');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="test_email" name="test_email" placeholder="<?php echo _('Email addresses, separated by comma');?>" value="<?php echo get_app_data('test_email');?>">
	            </div>
	        </div>
	        <input type="hidden" name="cid" value="<?php echo $cid;?>">
	        <input type="hidden" name="webversion" value="<?php echo get_app_info('path');?>/w/<?php echo short($cid);?>">
	        <button type="submit" class="btn" id="test-send-btn"><i class="icon icon-envelope-alt"></i> <?php echo _('Test send this newsletter');?></button>
	    </form>
	    
	    <br/>
	    <h2><?php echo _('Define recipients');?></h2><br/>
		    <?php if(get_app_info('is_sub_user')):?>
			    <?php if(paid()):?>
				<form action="<?php echo get_app_info('path')?>/includes/create/send-now.php" method="POST" accept-charset="utf-8" class="form-vertical" id="real-form">
			    <?php else:?>
				<form action="<?php echo get_app_info('path')?>/payment" method="POST" accept-charset="utf-8" class="form-vertical" id="pay-form">
			    <?php endif;?>	    
			<?php else:?>
				<form action="<?php echo get_app_info('path')?>/includes/create/send-now.php" method="POST" accept-charset="utf-8" class="form-vertical" id="real-form">
			<?php endif;?>
	    	<div class="control-group">
            <label class="control-label" for="multiSelect"><?php echo _('Select email list(s)');?></label>
            <div class="controls">
              <select multiple="multiple" id="email_list" name="email_list[]" style="height:200px">
              	<?php 
	              	$q = 'SELECT * FROM lists WHERE app = '.get_app_info('app').' AND userID = '.get_app_info('main_userID').' ORDER BY name ASC';
	              	$r = mysqli_query($mysqli, $q);
	              	if ($r && mysqli_num_rows($r) > 0)
	              	{
	              	    while($row = mysqli_fetch_array($r))
	              	    {
	              			$list_id = stripslashes($row['id']);
	              			$list_name = stripslashes($row['name']);
	              			$list_selected = '';
	              			
	              			$q2 = 'SELECT lists FROM campaigns WHERE id = '.$cid;
	              			$r2 = mysqli_query($mysqli, $q2);
	              			if ($r2)
	              			{
	              			    while($row = mysqli_fetch_array($r2))
	              			    {
	              					$lists = $row['lists'];
	              					$lists_array = explode(',', $lists);
	              					if(in_array($list_id, $lists_array))
	              						$list_selected = 'selected';
	              			    }  
	              			}
	              			
	              			echo '<option value="'.$list_id.'" data-quantity="'.get_list_quantity($list_id).'" id="'.$list_id.'" '.$list_selected.'>'.$list_name.'</option>';
	              	    }  
	              	}
	              	else
	              	{
		              	echo '<option value="" onclick="window.location=\''.get_app_info('path').'/new-list?i='.$aid.'\'">'._('No list found, click to add one.').'</option>';
	              	}
              	?>
              </select>
            </div>
          </div>
	        <input type="hidden" name="cid" value="<?php echo $cid;?>">
	        <input type="hidden" name="uid" value="<?php echo $aid;?>">
	        <input type="hidden" name="path" value="<?php echo get_app_info('path');?>">
	        <input type="hidden" name="grand_total_val" id="grand_total_val">
	        <input type="hidden" name="cron" value="<?php echo $cron;?>">
	        <input type="hidden" name="total_recipients" id="total_recipients">
	        
	        <?php				
	        	//Get SES quota (array)
	        	if($aws_keys_available=='true')
	        	{
			    	require_once('includes/helpers/ses.php');
					$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
					$quotaArray = array();
					foreach($ses->getSendQuota() as $quota){
						array_push($quotaArray, $quota);
					}
					$ses_quota = round($quotaArray[0]);
					$ses_send_rate = round($quotaArray[1]);
				}
				
				//Update send_rate into database if user is using Amazon SES to send emails
				if($aws_keys_available=='true' && (get_app_info('send_rate')=='' || get_app_info('send_rate')==0)) mysqli_query($mysqli, 'UPDATE login SET send_rate = '.$ses_send_rate);
					
	        	//Get limits (SES or brand limits) depending if user is a main or sub user
				if(get_app_info('is_sub_user'))
				{
		        	//Brand limits
					$today_unix_timestamp = time();
					$brand_monthly_quota = get_app_data('allocated_quota');
					if($brand_monthly_quota!=-1)
					{
						//Check if limit needs to be reset					
						$day_today = strftime("%e", $today_unix_timestamp);
						$month_today = strftime("%b", $today_unix_timestamp);
						$year_today = strftime("%G", $today_unix_timestamp);
						$no_of_days_this_month = cal_days_in_month(CAL_GREGORIAN, strftime("%m", $today_unix_timestamp), $year_today);
						
						$brand_limit_resets_on = get_app_data('day_of_reset')>$no_of_days_this_month ? $no_of_days_this_month : get_app_data('day_of_reset');
						$brand_month_of_next_reset = get_app_data('month_of_next_reset');
						
						$date_today_unix = strtotime($day_today.' '.$month_today.' '.$year_today);
						$reset_year = $month_today=='Dec' ? $year_today+1 : $year_today;
						$date_on_reset_unix = strtotime($brand_limit_resets_on.' '.$brand_month_of_next_reset.' '.$reset_year);
						
						//If date of reset has already passed today's date, reset current limit to 0
						if($date_today_unix>=$date_on_reset_unix)
						{
							//If the day of reset hasn't passed today's 'day', month_of_next_reset should be this month
							if($brand_limit_resets_on>$day_today)
							{
								$month_next = $month_today;
							}
							//Otherwise, add one month to today's month as month_of_next_reset should be next month
							else
							{
								$month_next = strtotime('1 '.$month_today.' +1 month');
								$month_next = strftime("%b", $month_next);
							}
							
							//Reset current limit to 0 and set the month_of_next_reset to the next month
							$q = 'UPDATE apps SET current_quota = 0, month_of_next_reset = "'.$month_next.'" WHERE id = '.get_app_info('app');
							$r = mysqli_query($mysqli, $q);
							if($r) 
							{
								//Update new $brand_month_of_next_reset
								$brand_month_of_next_reset = $month_next;
							}
						}
						
						//Calculate day of reset for next month
						$month_next = strtotime('1 '.$brand_month_of_next_reset);
						$month_next = strftime("%m", $month_next);
						$no_of_days_next_month = cal_days_in_month(CAL_GREGORIAN, $month_next, $year_today);
						$brand_limit_resets_on = get_app_data('day_of_reset')>$no_of_days_next_month ? $no_of_days_next_month : get_app_data('day_of_reset');
						
						//Get sends left
						$brand_current_quota = get_app_data('current_quota');
						$brand_sends_left = $brand_monthly_quota - $brand_current_quota;
						$ses_sends_left = $brand_sends_left;
					}
					else $ses_sends_left = -1; //unlimited sending
				}
				else
				{
					if($aws_keys_available=='true') $ses_sends_left = round($quotaArray[0]-$quotaArray[2]);
				}
	    	?>
	        
	        <?php if(get_app_info('is_sub_user')):?>
	        
	        	<input type="hidden" id="ses_sends_left" value="<?php echo $ses_sends_left;?>"/>
	        	<input type="hidden" id="aws_keys_available" value="<?php echo $aws_keys_available;?>"/>
	        	<input type="hidden" id="is_sub_user" value="true"/>
	        	
		        <?php if(paid()):?>
		        
			        <?php if($brand_monthly_quota!=-1):?><strong><?php echo _('Monthly limit');?></strong>: <?php echo $brand_monthly_quota.' ('._('resets on').' '.$brand_month_of_next_reset.' '.$brand_limit_resets_on;?>)<br/><?php endif;?>
		        	<strong><?php echo _('Recipients');?></strong>: <span id="recipients">0</span> 
		        	<?php if($brand_monthly_quota!=-1) echo _('of').' '.$brand_sends_left._(' remaining')?><br/><br/>
		        	
		        	<!-- over limit msg -->
			    	<div class="alert alert-error" id="over-limit" style="display:none;">
					  <?php echo _('You can\'t send more than your monthly limit. Request for your limit to be raised by sending an email to').' <a href="mailto:'.$main_user_email.'">'.$main_user_email.'</a>';?> 
					</div>
			    	<!-- /over limit msg -->
			    	
			        <button type="submit" class="btn btn-inverse btn-large" id="real-btn" <?php echo $send_newsletter_now;?>><i class="icon-ok icon-white"></i> <?php echo _('Send newsletter now!');?></button>
			        
			        <!-- success msg -->
			        <div id="view-report" class="alert alert-success" style="margin-top: 20px; display:none;">
			    		<p><h3><?php echo _('Your campaign is now sending!');?></h3></p>
			    		<p><?php echo _('You can safely close this window, your campaign will continue to send.');?></p>
			    		<p><?php echo _('You will be notified by email once your campaign has completed sending.');?></p>
			    	</div>
			        <!-- /success msg -->
			    	
			        <p style="margin-top:10px; text-decoration:underline;">
			        	<?php if($cron):?>
			        	<a href="javascript:void(0)" id="send-later-btn"><?php echo $send_newsletter_text;?></a>
			        	<?php endif;?>
			        </p>
			        
		        <?php else:?>
			        <input type="hidden" name="paypal" value="<?php echo get_paypal();?>">
			        <div class="well" style="width:260px;">
			        	<?php if($brand_monthly_quota!=-1):?><strong><?php echo _('Monthly limit');?></strong>: <?php echo $brand_monthly_quota.' ('._('resets on').' '.$brand_month_of_next_reset.' '.$brand_limit_resets_on;?>)<br/><?php endif;?>
				        <strong><?php echo _('Recipients');?></strong>: <span id="recipients">0</span> 
				        <?php if($brand_monthly_quota!=-1) echo _('of').' '.$brand_sends_left._(' remaining')?><br/>
				        <strong><?php echo _('Delivery Fee');?></strong>: <?php echo get_fee('currency');?> <span id="delivery_fee"><?php echo get_fee('delivery_fee');?></span><br/>
				        <strong><?php echo _('Fee per recipient');?></strong>: <?php echo get_fee('currency');?> <span id="recipient_fee"><?php echo get_fee('cost_per_recipient');?></span><br/><br/>
				        <span class="grand_total"><strong><?php echo _('Grand total');?></strong>: <?php echo get_fee('currency');?> <span id="grand_total">0</span></span>
			        </div>
			        
			        <!-- over limit msg -->
			    	<div class="alert alert-error" id="over-limit" style="display:none;">
					  <?php echo _('You can\'t send more than your monthly limit. Request for your limit to be raised by sending an email to').' <a href="mailto:'.$main_user_email.'">'.$main_user_email.'</a>.';?> 
					</div>
			    	<!-- /over limit msg -->
			        
			        <button type="submit" class="btn btn-inverse btn-large" id="pay-btn" <?php echo $send_newsletter_now;?>><i class="icon-arrow-right icon-white"></i> <?php echo _('Proceed to pay for campaign');?></button>
			        <p style="margin-top:10px; text-decoration:underline;">
			        	<?php if($cron):?>
			        	<a href="javascript:void(0)" id="send-later-btn"><?php echo $send_newsletter_text;?></a>
			        	<?php endif;?>
			        </p>
			        
		        <?php endif;?>
		        
		    <?php else:?>
		    
		    	<strong><?php echo _('Recipients');?></strong>: <span id="recipients">0</span> <?php echo $aws_keys_available=='true' ? _('of') : '';?> <?php echo $aws_keys_available=='true' ? $ses_sends_left : ''; echo _(' remaining');?><br/>
		    	
		    	<?php if($aws_keys_available=='true'):?>
		    	<strong><?php echo _('SES sends left');?></strong>: <span id="sends_left"><?php echo $ses_sends_left.' of '.$ses_quota;?></span><br/>
		    	
			    	<?php if($ses_sends_left==0 && $ses_quota==0):?>
			    	<br/><p class="alert alert-danger"><?php echo _('Unable to get your SES quota from Amazon. Visit your "Brands" page by clicking your company\'s name at the top left of the screen. Then check the instructions on the left sidebar on how to resolve this issue');?></p>
			    	<?php endif;?>
		    	
		    	<?php endif;?>
		    	<br/>
		    	
		    	<?php 	
			    	if($aws_keys_available=='true')
			    	{				    						
						//Check bounces & complaints handling setup
						require_once('includes/helpers/sns.php');
						$aws_endpoint_array = explode('.', get_app_info('ses_endpoint'));
						$aws_endpoint = $aws_endpoint_array[1];
						$sns = new AmazonSNS(get_app_info('s3_key'), get_app_info('s3_secret'), $aws_endpoint);
						$bounces_topic_arn = '';
						$bounces_subscription_arn = '';
						$complaints_topic_arn = '';
						$complaints_subscription_arn = '';
						//Get protocol of endpoint
					    $protocol_array = explode(':', get_app_info('path'));
					    $protocol = $protocol_array[0];
						try 
						{
							//Get list of SNS topics and subscriptions
							$v_subscriptions = $sns->ListSubscriptions();
							foreach ($v_subscriptions as $subscription)
							{
								$TopicArn = $subscription['TopicArn'];
								$Endpoint = $subscription['Endpoint'];
								if($Endpoint==get_app_info('path').'/includes/campaigns/bounces.php' || $Endpoint==get_app_info('path').'includes/campaigns/bounces.php')
								{
									$bounces_topic_arn = $TopicArn;
									$bounces_subscription_arn = $Endpoint;
								}
								if($Endpoint==get_app_info('path').'/includes/campaigns/complaints.php' || $Endpoint==get_app_info('path').'includes/campaigns/complaints.php')
								{
									$complaints_topic_arn = $TopicArn;
									$complaints_subscription_arn = $Endpoint;
								}
							}
							
							//Create 'bounces' SNS topic
						    try {$bounces_topic_arn = $sns->CreateTopic('bounces');}
							catch (SNSException $e) {echo '<p class="error">'._('Error').' ($sns->CreateTopic(\'bounces\')): '.$e->getMessage().'. '._('Please try again by refreshing this page. If this error persist, visit your Amazon SNS console and delete all \'Topics\' and \'Subscriptions\' and try again.')."<br/><br/></p>";}
							
							//Create 'complaints' SNS topic
							try {$complaints_topic_arn = $sns->CreateTopic('complaints');}
							catch (SNSException $e) {echo '<p class="error">'._('Error').' ($sns->CreateTopic(\'complaints\')): '.$e->getMessage().'. '._('Please try again by refreshing this page. If this error persist, visit your Amazon SNS console and delete all \'Topics\' and \'Subscriptions\' and try again.')."<br/><br/></p>";}
						    
						    //If 'bounces' and 'complaints' SNS topics exists, create SNS subscriptions for them
						    if($bounces_topic_arn!='' && $complaints_topic_arn!='')
						    {
							    //Create 'bounces' SNS subscription
								try {$bounces_subscribe_endpoint = $sns->Subscribe($bounces_topic_arn, $protocol, get_app_info('path').'/includes/campaigns/bounces.php');}
								catch (SNSException $e) {echo '<p class="error">'._('Error').' ($sns->Subscribe(\'bounces\')): '.$e->getMessage().'. '._('Please try again by refreshing this page. If this error persist, visit your Amazon SNS console and delete all \'Topics\' and \'Subscriptions\' and try again.')."<br/><br/></p>";}
								
								//Create 'complaints' SNS subscription
								try {$complaints_subscribe_endpoint = $sns->Subscribe($complaints_topic_arn, $protocol, get_app_info('path').'/includes/campaigns/complaints.php');}
								catch (SNSException $e) {echo '<p class="error">'._('Error').' ($sns->Subscribe(\'complaints\')): '.$e->getMessage().'. '._('Please try again by refreshing this page. If this error persist, visit your Amazon SNS console and delete all \'Topics\' and \'Subscriptions\' and try again.')."<br/><br/></p>";}
						    }
						    else echo '<p class="error">'._('Error: Unable to create bounces and complaints SNS topics, please try again by refreshing this page.')."<br/><br/></p>";
						    
						    //Set SNS 'Notifications' for 'From email'
					        require_once('includes/helpers/ses.php');
							$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
							
							//Set 'bounces' Notification
							$ses->SetIdentityNotificationTopic($from_email,$bounces_topic_arn,'Bounce');
							$ses->SetIdentityNotificationTopic($from_email_domain,$bounces_topic_arn,'Bounce');
							
							//Set 'complaints' Notification
							$ses->SetIdentityNotificationTopic($from_email,$complaints_topic_arn,'Complaint');
							$ses->SetIdentityNotificationTopic($from_email_domain,$complaints_topic_arn,'Complaint');
							
							//Disable email feedback forwarding
							$ses->setIdentityFeedbackForwardingEnabled($from_email, 'false');
							$ses->setIdentityFeedbackForwardingEnabled($from_email_domain, 'false');
	
						} 
						catch (Exception $e) 
						{
							echo '
							<script type="text/javascript">
								$(document).ready(function() {
									$("#real-btn").addClass("disabled");
									$("#test-send-btn").addClass("disabled");
									$("#schedule-btn").addClass("disabled");
									$("#real-btn").attr("disabled", "disabled");
									$("#test-send-btn").attr("disabled", "disabled");
									$("#schedule-btn").attr("disabled", "disabled");
									$("#email_list").attr("disabled", "disabled");
								});
							</script>
							';
							
							if($e->getMessage()=='AuthorizationError'):
					
			    ?>
			    
							    <div class="alert alert-danger" id="amazon-sns-access">
								  <p><?php echo _('Sendy is unable to verify and setup bounces & complaints handling for your \'From email\' address. Here\'s what you need to do: ');?> </p>
								  <p>
									  <ol>
										  <li style="margin-bottom: 10px;">
										  		<?php echo _('Please attach <code>AmazonSNSFullAccess</code> policy in addition to \'AmazonSESFullAccess\' policy to your IAM credentials in your <a href="https://console.aws.amazon.com/iam/home#users" target="_blank">IAM console</a> (see Step 5.7 of the <a href="https://sendy.co/get-started" target="_blank">Get Started Guide</a>). ');?>
										  		<br/> <img src="http://d.pr/i/140L0+"/> 
										  </li>
										  <li><?php echo _('Then refresh this page.');?></li>
									  </ol>
								  </p>
								  <p><?php echo _('Once this is done, Sendy will be able to setup bounces & complaints handling automatically.');?></p>
								</div>
			    
			    <?php 		
				    		else:
				    		
					    		echo '<p class="error">'._('Error communicating with Amazon SNS API').': '.$e->getMessage().'</p>';
				    		
				    		endif; 
				    	}
					}
				?>
		    	
		    	<?php if($aws_keys_available=='true' && $ses_quota==200):?>
		    	<div class="alert" id="no-production-access">
				  <?php echo _('It looks like you are still in Amazon SES "Sandbox mode". You can only send to email addresses that you\'ve verified in your');?> <a href="https://console.aws.amazon.com/ses/home#verified-senders:email" target="_blank" style="text-decoration:underline"><?php echo _('Amazon SES console.');?></a> <?php echo _('If you try to send newsletters to emails NOT verified in your SES console, your recipient will not receive the newsletter.');?><br/><br/><a href="http://aws.amazon.com/ses/fullaccessrequest/" target="_blank" style="text-decoration:underline"><?php echo _('Request Amazon to raise your \'SES Sending Limits\' to get out of "Sandbox mode"');?></a> <?php echo _('to lift this restriction.');?><br/><br/><?php echo _('Please also make sure to select the same \'Region\' as what is set in your Sendy Settings (under \'Amazon SES region\') when requesting for \'SES Sending Limits\' increase.');?><br/>
				</div>
				<?php endif;?>
		    	
		    	<!-- over limit msg -->
		    	<div class="alert alert-error" id="over-limit" style="display:none;">
				  <?php echo _('You can\'t send more than your SES daily limit. Either wait till Amazon replenishes your daily limit in the next 24 hours, or');?> <a href="http://aws.amazon.com/ses/extendedaccessrequest" target="_blank" style="text-decoration:underline"><?php echo _('request for extended access');?></a>. 
				</div>
		    	<!-- /over limit msg -->
		    	
		    	<input type="hidden" id="ses_sends_left" value="<?php echo $aws_keys_available=='true' ? $ses_sends_left : 0;?>"/>
		    	<input type="hidden" id="aws_keys_available" value="<?php echo $aws_keys_available;?>"/>
		    	<button type="submit" class="btn btn-inverse btn-large" id="real-btn" <?php echo $send_newsletter_now;?>><i class="icon-ok icon-white"></i> <?php echo _('Send newsletter now!');?></button>
		    	
		    	<div id="view-report" class="alert alert-success" style="margin-top: 20px; display:none;">
		    		<p><h3><?php echo _('Your campaign is now sending!');?></h3></p>
		    		<p><?php echo _('You can safely close this window, your campaign will continue to send.');?></p>
		    		<p><?php echo _('You will be notified by email once your campaign has completed sending.');?></p>
		    	</div>
		    	
		    	<?php if(!$cron):?>
		    	<br/><br/>
		    	<div class="alert alert-info">
			    	<p><strong><?php echo _('Note');?>:</strong> <?php echo _('We recommend');?> <a href="#cron-instructions" data-toggle="modal" style="text-decoration:underline"><?php echo _('setting up CRON');?></a> <?php echo _('to send your newsletters');?>. <?php echo _('Newsletters sent via CRON have the added ability to automatically resume sending when your server times out. You\'ll also be able to schedule emails.');?></p>
			    	<p><?php echo _('You haven\'t set up CRON yet, but that\'s okay. You can still send newsletters right now. But keep in mind that you won\'t be able to navigate around Sendy until sending is complete. Also, you\'ll need to manually resume sending (with a click of a button) if your server times out.');?></p>
			    	<p><a href="#cron-instructions" data-toggle="modal" style="text-decoration:underline"><?php echo _('Setup CRON now');?> &rarr;</a></p>
		    	</div>
		    	<?php endif;?>
		    	
		    	<p style="margin-top:10px; text-decoration:underline;">
		    		<?php if($cron):?>
			    		<a href="javascript:void(0)" id="send-later-btn"><?php echo $send_newsletter_text;?></a>
		    		<?php else:?>
			        	<a href="#cron-instructions" data-toggle="modal"><?php echo $send_newsletter_text;?></a>
		        	<?php endif;?>
		    	</p>
		    	
		    <?php endif;?>
	        
	    </form>
	    
	    <?php if(!$cron):
		    $server_path_array = explode('send-to.php', $_SERVER['SCRIPT_FILENAME']);
		    $server_path = $server_path_array[0];
	    ?>
	    <div id="cron-instructions" class="modal hide fade">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h3><i class="icon icon-time" style="margin-top: 5px;"></i> <?php echo _('Add a cron job');?></h3>
            </div>
            <div class="modal-body">
            <p><?php echo _('To schedule campaigns or to make sending more reliable, add a');?> <a href="http://en.wikipedia.org/wiki/Cron" target="_blank" style="text-decoration:underline"><?php echo _('cron job');?></a> <?php echo _('with the following command.');?></p>
            <h3><?php echo _('Command');?></h3>
            <pre id="command">php <?php echo $server_path;?>scheduled.php > /dev/null 2>&amp;1</pre>
            <p><?php echo _('This command needs to be run every 5 minutes in order to check the database for any scheduled campaigns to send. You\'ll need to set your cron job with the following.');?><br/><em><?php echo _('(Note that adding cron jobs vary from hosts to hosts, most offer a UI to add a cron job easily. Check your hosting control panel or consult your host if unsure.)');?></em>.</p>
            <h3><?php echo _('Cron job');?></h3>
            <pre id="cronjob">*/5 * * * * php <?php echo $server_path;?>scheduled.php > /dev/null 2>&amp;1</pre>
            <p><?php echo _('Once added, wait around 5 minutes. If your cron job is functioning correctly, you\'ll see the scheduling options instead of this modal window when you click on "Schedule this campaign?".');?></p>
            </div>
            <div class="modal-footer">
              <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign"></i> <?php echo _('Okay');?></a>
            </div>
          </div>
          <script type="text/javascript">
          	$(document).ready(function() {
          		$("#command, #cronjob").click(function(){
					$(this).selectText();
				});
          	});
          </script>
        <?php endif;?>
	    
	    <div class="well" id="schedule-form-wrapper" <?php echo $schedule_form_style;?>>
	    	<?php if(get_app_info('is_sub_user')):?>
			    <?php if(paid()):?>
			    <form action="<?php echo get_app_info('path');?>/includes/create/send-later.php" method="POST" accept-charset="utf-8" id="schedule-form">
			    <input type="hidden" name="total_recipients2" id="total_recipients2">
		    	<?php else:?>
			    <form action="<?php echo get_app_info('path');?>/payment" method="POST" accept-charset="utf-8" id="schedule-form">
			    <input type="hidden" name="pay-and-schedule" value="true"/>
			    <input type="hidden" name="paypal2" value="<?php echo get_paypal();?>">
			    <input type="hidden" name="grand_total_val2" id="grand_total_val2">
			    <input type="hidden" name="total_recipients2" id="total_recipients2">
			    <?php endif;?>
			<?php else:?>
				<form action="<?php echo get_app_info('path');?>/includes/create/send-later.php" method="POST" accept-charset="utf-8" id="schedule-form">
				<input type="hidden" name="total_recipients2" id="total_recipients2">
		    <?php endif;?>
		    	<h3><i class="icon-ok icon-time" style="margin-top:5px;"></i> <?php echo _('Schedule this campaign');?></h3><br/>
	    		<input type="hidden" name="campaign_id" value="<?php echo $cid;?>"/>
	    		<input type="hidden" name="email_lists" id="email_lists"/>
	    		<input type="hidden" name="app" value="<?php echo $aid;?>"/>
	    		
	    		<label for="send_date"><?php echo _('Pick a date');?></label>
	    		<?php 
	    			if($send_date=='')
	    			{
		    			$tomorrow = time()+86400;
			    		$day = strftime("%d", $tomorrow);
			    		$month = strftime("%m", $tomorrow);
			    		$year = strftime("%Y", $tomorrow);
			    		$the_date = $month.'-'.$day.'-'.$year;
			    	}
	    		?>
	    		<div class="input-prepend date" id="datepicker" data-date="<?php echo $the_date;?>" data-date-format="mm-dd-yyyy">
	             <input type="text" name="send_date" value="<?php echo $the_date;?>" readonly><span class="add-on"><i class="icon-calendar" id="date-icon"></i></span>
	            </div>
	            <br/>
	            <label><?php echo _('Set a time');?></label>
	    		<select id="hour" name="hour" class="schedule-date">
	    		  <?php if($send_date!=''):?>
	    		  <option value="<?php echo $hour;?>"><?php echo $hour;?></option>
	    		  <?php endif;?>
				  <option>1</option> 
				  <option>2</option> 
				  <option>3</option> 
				  <option>4</option> 
				  <option>5</option> 
				  <option>6</option> 
				  <option>7</option> 
				  <option>8</option> 
				  <option>9</option> 
				  <option>10</option> 
				  <option>11</option> 
				  <option>12</option> 
				</select>
				<select id="min" name="min" class="schedule-date">
				  <?php if($send_date!=''):?>
				  <option value="<?php echo $minute;?>"><?php echo $minute;?></option>
				  <?php endif;?>
				  <option>00</option> 
				  <option>05</option> 
				  <option>10</option> 
				  <option>15</option> 
				  <option>20</option> 
				  <option>25</option> 
				  <option>30</option> 
				  <option>35</option> 
				  <option>40</option> 
				  <option>45</option> 
				  <option>50</option> 
				  <option>55</option> 
				</select>
				<select id="ampm" name="ampm" class="schedule-date">
				  <?php if($send_date!=''):?>
				  <option value="<?php echo $ampm;?>"><?php echo $ampm;?></option>
				  <?php endif;?>
				  <option>am</option> 
				  <option>pm</option> 
				</select>
				<br style="clear:both;"/>
				<br/>
	    		<label for="timezone"><?php echo _('Select a timezone');?></label>
	    		<select id="timezone" name="timezone">
				  <option value="<?php echo $timezone;?>"><?php echo $timezone;?></option> 
				  <?php get_timezone_list();?>
				</select>
				<br/><br/>
				<?php if(get_app_info('is_sub_user')):?>
			        <?php if(paid()):?>
					<button type="submit" class="btn btn-inverse btn-large" id="schedule-btn"><i class="icon-ok icon-time icon-white"></i> <?php echo _('Schedule campaign now');?></button>
					<?php else:?>
					<button type="submit" class="btn btn-inverse btn-large" id="schedule-btn"><i class="icon-arrow-right icon-white"></i> <?php echo _('Schedule and pay for campaign');?></button>
					<?php endif;?>
				<?php else:?>
			    	<button type="submit" class="btn btn-inverse btn-large" id="schedule-btn"><i class="icon-ok icon-time icon-white"></i> <?php echo _('Schedule campaign now');?></button>
				<?php endif;?>
	    	</form>
    	</div>
	    <div id="edit-newsletter"><a href="<?php echo get_app_info('path')?>/edit?i=<?php echo get_app_info('app')?>&c=<?php echo $cid;?>" title=""><i class="icon-pencil"></i> <?php echo _('Edit newsletter');?></a></div>
    </div>   
    
    <div class="span7">
    	<div>
	    	<h2><?php echo _('Newsletter preview');?></h2><br/>
	    	<blockquote><strong><?php echo _('From');?></strong> <span class="label"><?php echo get_saved_data('from_name');?> &lt;<?php echo get_saved_data('from_email');?>&gt;</span></blockquote>
	    	<blockquote><strong><?php echo _('Subject');?></strong> <span class="label"><?php echo get_saved_data('title');?></span></blockquote>
	    	<iframe src="<?php echo get_app_info('path');?>/w/<?php echo short($cid);?>?<?php echo time();?>" id="preview-iframe"></iframe>
    	</div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		var send_or_schedule = '';
		
		//schedule btn
		$("#schedule-btn").click(function(e){
			e.preventDefault(); 
			
			send_or_schedule = 'schedule';
			email_list = $('select#email_list').val();
			
			if(email_list == null)
			{
				$("#schedule-btn").effect("shake", { times:3 }, 60);
				$("#email_list").effect("shake", { times:3 }, 60);
			}
			else
			{					
				//Save & schedule the email to be sent later
				$("#total_recipients2").val($("#recipients").text());
				$("#schedule-form").submit();
			}
		});
		
		//send email for real
		$("#real-form").submit(function(e){
			e.preventDefault(); 
			
			send_or_schedule = 'send';
			
			if($("#email_list").val() == null)
			{
				$("#real-btn").effect("shake", { times:3 }, 60);
				$("#email_list").effect("shake", { times:3 }, 60);
			}
			else
			{
				<?php if($_SESSION[$_SESSION['license']] != hash('sha512', $_SESSION['license'].'2ifQ9IppVwYdOgSJoQhKOHAUK/oPwKZy')) :?>
				if(confirm("Hi! This is Ben, the indie developer of Sendy. Please consider supporting my tireless efforts in developing this software you are using by purchasing a copy of Sendy at sendy.co. I really appreciate your support. Thank you and God bless!")) window.location = "https://sendy.co"; else window.location = "https://sendy.co";
				<?php else:?>
				c = confirm("<?php echo addslashes(_('Have you double checked your selected lists? If so, let\'s go ahead and send this!'));?>");
				if(c) send_it();
				<?php endif;?>
			}
		});
		
		//send to PayPal
		$("#pay-form").submit(function(e){
			$("#total_recipients").val($("#recipients").text());
			if($('select#email_list').val() == null)
			{
				e.preventDefault(); 
				$("#pay-btn").effect("shake", { times:3 }, 60);
			}
			else
			{
				c = confirm('<?php echo addslashes(_('Have you double checked your selected lists? If so, proceed to pay for this campaign.'));?>');
					
				if(!c)
					e.preventDefault(); 
			}
		});
		
		function send_it()
		{
			$('#sns-loading').modal('hide');
			
			$("#total_recipients").val($("#recipients").text());
			
			var $form = $("#real-form"),
			campaign_id = $form.find('input[name="cid"]').val(),
			email_list = $form.find('select#email_list').val(),
			uid = $form.find('input[name="uid"]').val(),
			path = $form.find('input[name="path"]').val(),
			cron = $form.find('input[name="cron"]').val(),
			total_recipients = $form.find('input[name="total_recipients"]').val(),
			url = $form.attr('action');
			
			$("#real-btn").addClass("disabled");
			$("#real-btn").text("Your email is on the way!");
			$("#view-report").show();
			$("#edit-newsletter").hide();
				
			$.post(url, { campaign_id: campaign_id, email_list: email_list, app: uid, cron: cron, total_recipients: total_recipients },
			  function(data) {
			  	  
			  	  $("#test-send").css("display", "none");
			  	  $("#test-send-error").css("display", "none");
			  	  
			      if(data)
			      {
			      	if(data=='cron_send')
			      		window.location = path+"/app?i="+uid;
			      	else
			      		window.location = path+"/report?i="+uid+"&c="+campaign_id;
			      }
			  }
			);
		}
		
		$("#send-anyway").click(function(){
			if(send_or_schedule=='send') send_it();
			else $("#schedule-form").submit();
		});
	});
</script>

<div id="sns-loading" class="modal hide fade">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h3><?php echo _('Checking bounces & complaints set up');?></h3>
</div>
<div class="modal-body">
    <div class="well" style="float:left;">
    	<img src="<?php echo get_app_info('path');?>/img/loader.gif" style="float:left; margin-right:5px; width: 16px;"/> 
    	<p style="float:right; width:450px;">
	    	<span id="please-wait-msg"><?php echo _('Please wait while we check if bounces & complaints have been set up. Checks are only done once per \'From email\'.');?></span>
	    </p>
    </div>
    <p style="float:left; clear:both;"><i><?php echo _('If this window does not disappear after 10 seconds, hit \'Esc\' and try again.');?></i></p>
</div>

</div>

<div id="sns-warning" class="modal hide fade">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h3><?php echo _('Important: Bounces or complaints were not set up');?></h3>
</div>
<div class="modal-body">
    <p class="alert alert-danger"><i class="icon icon-warning-sign"></i> <strong><?php echo _('We\'ve detected that bounces or complaints have not been setup.');?></strong></p> 
    <p><?php echo _('Not having bounces or complaints registered means future campaigns will continue to be sent to emails that bounced and recipients who have marked your emails as spam. This may lead to Amazon suspending your AWS account.');?></p>
    <div class="well">
    <p><strong><?php echo _('We highly recommend setting up bounces & complaints');?>:</strong></p>
    <p><?php echo _('Visit our Get Started Guide and complete steps 7 & 8');?> &rarr; <a href="https://sendy.co/get-started" target="_blank"><u>https://sendy.co/get-started</u></a>.</p>
    <p><?php echo _('Or troubleshoot with this FAQ');?> &rarr; <a href="https://sendy.co/troubleshooting#bounces-complaints-warning" target="_blank"><u>https://sendy.co/troubleshooting#bounces-complaints-warning</u></a>.</p></div>
</div>
<div class="modal-footer">
  <a href="#" class="btn btn-inverse" data-dismiss="modal"><?php echo _('Don\'t send');?></a>
  <a href="#" class="btn" data-dismiss="modal" id="send-anyway"><?php echo _('Send anyway');?></a>
</div>
</div>
<?php include('includes/footer.php');?>
