<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/settings/main.php');?>
<?php include('includes/create/timezone.php');?>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/settings/main.js?5"></script>
<div class="row-fluid">
	<div class="span2">
		
		<?php if(get_app_info('is_sub_user')):?>
		
		<?php include('includes/sidebar.php');?>
		
		<?php else:?>
		
		<h3><?php echo _('Amazon SES Quota');?></h3><br/>
		<div class="well">
			<?php
				if(get_app_info('s3_key')=='' && get_app_info('s3_secret')==''){}
				else
				{
					require_once('includes/helpers/ses.php');
					$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'), get_app_info('ses_endpoint'));
					
					//Get success or error codes from API call
					$testAWSCreds = $ses->getSendQuota();
					
					$quoteArray = array();
					
					foreach($ses->getSendQuota() as $quota){
						array_push($quoteArray, $quota);
					}
					
					$ses_send_rate = round($quoteArray[1]);
				}
			?>
			<?php if(get_app_info('s3_key')=='' && get_app_info('s3_secret')==''):?>
			
				<p><strong><?php echo _('Amazon SES is not set up as we can\'t find your AWS credentials in');?> <a href="<?php echo get_app_info('path');?>/settings" style="text-decoration: underline"><?php echo _('settings');?></a>.</strong></p>
				<p><strong><?php echo _('If you entered SMTP credentials when you create or edit a brand, emails will be sent via SMTP. Otherwise, emails will be sent via your server (not recommended).');?></strong></p>
				<p><a href="https://sendy.co/get-started" target="_blank"><?php echo _('View Get Started guide');?> &rarr;</a></p>
				
			<?php else:?>
			
				<p><strong><?php echo _('SES Region');?>:</strong> <span class="label"><?php echo get_app_info('ses_region');?></span></p>
				<p><strong><?php echo _('Max send in 24hrs');?>:</strong> <span class="label"><?php echo number_format(round($quoteArray[0]));?></span></p>
				<p><strong><?php echo _('Max send rate');?>:</strong> <span class="label"><?php echo number_format(round($quoteArray[1]));?> <?php echo _('per sec');?></span></p>
				<p><strong><?php echo _('Sent last 24hrs');?>:</strong> <span class="label"><?php echo number_format(round($quoteArray[2]));?></span></p>
				<p><strong><?php echo _('Sends left');?>:</strong> <span class="label"><?php echo number_format(round($quoteArray[0]-$quoteArray[2]));?></span></p>
				
				<?php if($testAWSCreds=='AccessDenied'):?>
				<br/>
				<span style="color:#BB4D47;"><p><strong><?php echo _('Error');?>: AccessDenied</strong></p><p><?php echo _('Your Sendy installation is unable to get your SES quota from Amazon because you did not attach "AmazonSESFullAccess" user policy to your IAM credentials. Please follow Step 5.5 and 5.6 of the <a href="https://sendy.co/get-started" target="_blank">Get Started Guide</a> to resolve this error.');?></p></span>
				
				<?php elseif($testAWSCreds=='RequestExpired'):?>
				<br/>
				<span style="color:#BB4D47;"><p><strong><?php echo _('Error');?>: RequestExpired</strong></p><p><?php echo _('Your Sendy installation is unable to get your SES quota from Amazon because your server clock is out of sync with NTP. To fix this, Amazon requires you to <strong>sync your server clock with NTP</strong>. Request your host to sync your server clock with NTP with the following command via SSH:');?></p><p><code>sudo /usr/sbin/ntpdate 0.north-america.pool.ntp.org 1.north-america.pool.ntp.org 2.north-america.pool.ntp.org 3.north-america.pool.ntp.org</code></p></span>
				
				<?php elseif($testAWSCreds=='InvalidClientTokenId'):?>
				<br/>
				<span style="color:#BB4D47;"><p><strong><?php echo _('Error');?>: InvalidClientTokenId</strong></p><p><?php echo _('Your Sendy installation is unable to get your SES quota from Amazon because the \'Amazon Web Services Credentials\' set in Sendy\'s main Settings are incorrect. You probably did not copy and pasted your IAM credentials fully or properly into the settings. Please re-do Step 5.2 to 5.6 of the <a href="https://sendy.co/get-started" target="_blank">Get Started Guide</a> carefully to resolve this error.');?></p></span>
				
				<?php elseif(number_format(round($quoteArray[0]))=='200'):?>
				<br/>
				<span style="color:#BB4D47;"><p><?php echo _('You\'re currently in Amazon SES\'s "Sandbox mode".');?></p><p><?php echo _('Please request Amazon to "<a href="http://aws.amazon.com/ses/fullaccessrequest/" target="_blank">raise your SES Sending Limits</a>" to be able to send to and from any email address as well as raise your daily sending quota from 200 to any number you need.');?></p><p><?php echo _('Please also make sure to select the same \'Region\' as what is set in your Sendy Settings (under \'Amazon SES region\') when requesting for \'SES Sending Limits\' increase.');?></p></span>
				
				<?php elseif(number_format(round($quoteArray[0]))=='0' && number_format(round($quoteArray[1]))=='0' && number_format(round($quoteArray[2]))=='0' && get_app_info('s3_key')!='' && get_app_info('s3_key')!=''):?>
				<br/>
				<span style="color:#BB4D47;"><p><strong><?php echo _('Error');?>: <?php echo $ses->getSendQuota();?></strong></p></span>
				
				<?php endif;?>
			
			<?php endif;?>
		</div>
		
		<?php endif;?>
		
	</div>
    <div class="span5">
    	<h2><?php echo _('Settings');?></h2><br/>
    	
    	<div class="alert alert-success" style="display:none;">
		  <button class="close" onclick="$('.alert-success').hide();">×</button>
		  <strong><?php echo _('Settings have been saved!');?></strong>
		</div>
		
		<div class="alert alert-error" id="alert-error1" style="display:none;">
		  <button class="close" onclick="$('.alert-error').hide();">×</button>
		  <strong><?php echo _('Sorry, unable to save. Please try again later!');?></strong>
		</div>
		
	    <form action="<?php echo get_app_info('path')?>/includes/settings/save.php" method="POST" accept-charset="utf-8" class="form-vertical" id="settings-form">
	    	
	    	<label class="control-label" for="company"><?php echo _('Company');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="company" name="company" placeholder="<?php echo _('Your company');?>" value="<?php echo get_user_data('company');?>">
	            </div>
	        </div>
	        
	    	<label class="control-label" for="personal_name"><?php echo _('Name');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="personal_name" name="personal_name" placeholder="<?php echo _('Your name');?>" value="<?php echo get_user_data('name');?>">
	            </div>
	        </div>
	        
	        <label class="control-label" for="email"><?php echo _('Email');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="email" name="email" placeholder="<?php echo _('Your email address');?>" value="<?php echo get_user_data('username');?>" autocomplete="off">
	            </div>
	        </div>
	        <div class="alert alert-error" id="alert-error2" style="display:none;">
			  <button class="close" onclick="$('.alert-error').hide();">×</button>
			  <span><i class="icon icon-warning-sign"></i> <?php echo _('This login email address is already in use by one of your brands. Please find the brand that uses this login email address and change it to something else so that you can save. Or use another email address.');?></span>
			</div>
	        
	        <label class="control-label" for="password"><?php echo _('Password (leave blank to not change it)');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="password" class="input-xlarge" id="password" name="password" placeholder="<?php echo _('Your password');?>" autocomplete="off">
	            </div>
	        </div>
	        
	        <div>
		        <div id="enable-2fa-btn" style="display: <?php echo get_user_data('auth_enabled') ? 'none' : 'block'; ?>">
			        <p><?php echo _('Two-factor authentication is currently <strong class="label">disabled</strong>');?></p>
			        <button id="enable-two-factor-btn">
			        	<span class="icon icon-key"></span> <?php echo _('Enable two-factor authentication');?>
			        </button>
			    </div>
			    <div id="disable-2fa-btn" style="display: <?php echo get_user_data('auth_enabled') ? 'block' : 'none'; ?>">
				    <p><?php echo _('Two-factor authentication is currently <strong class="label label-success">enabled</strong>');?></p>
			    	<button id="disable-two-factor-btn">
			    		<span class="icon icon-key"></span> <?php echo _('Disable two-factor authentication');?>
			    	</button>
			    	<p id="re-key"><a href="javascript:void(0)" id="regenerate-key"><span class="icon icon-refresh"></span> <?php echo _('Regenerate secret key');?></a></p>
			    </div>
		    </div>
		    <br/>
		    
		    <div id="two-factor-setup">
			    	<p>
				    	<strong><?php echo _('What is "Two-factor authentication"?');?></strong><br/>
				    	<?php echo _('Two-factor authentication provides an additional layer of security by requiring a one time password (OTP) in addition to email & password credentials. You can get OTP codes without an internet or cellular connection using a <a href="#two_factor_apps"data-toggle="modal" style="text-decoration:underline;">two-factor application</a>.');?>
			    	</p><br/>
				    <p><?php echo _('<strong>Step 1</strong>: Scan the following QR code (or enter the secret key) into your <a href="#two_factor_apps"data-toggle="modal" style="text-decoration:underline;">two-factor application</a>:');?></p>
				    <p><img src="" id="qr-code"/></p>
				    <p><strong><?php echo _('Secret key:');?></strong> <span id="secret-key"></span></p>
				    <br/>
				    <p><?php echo _('<strong>Step 2</strong>: Use your <a href="#two_factor_apps"data-toggle="modal" style="text-decoration:underline;">two-factor application</a> to generate an OTP code, then paste it below:');?></p>
				    <p><input type="text" class="input-xlarge" id="otp" name="otp" placeholder="<?php echo _('OTP code, eg. 123456');?>" autocomplete="off"></p>
				    <button id="confirm-two-factor-btn"><span class="icon icon-chevron-sign-right"></span> <?php echo _('Confirm and enable two-factor authentication');?></button>
				    <br/>
				    <br/>
				    <a href="javascript:void(0)" id="cancel-two-factor"><span class="icon icon-angle-left"></span> Cancel</a>
				    <br/>
		    </div>
	        <br/>
	        
	        <!-- Two factor authenticator apps link -->
			<div id="two_factor_apps" class="modal hide fade">
			<div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal">&times;</button>
			  <h3><i class="icon icon-mobile-phone" style="margin-top: 5px;"></i> <?php echo _('Two-factor authentication applications');?></h3>
			</div>
			<div class="modal-body">
			<p><?php echo _('There are many \'Two-factor authentication\' mobile apps available in the market for free that can be used with services that supports 2FA like Amazon, Gmail, Dropbox, Facebook etc. The most common is \'Google Authenticator\'. The following are links to Google Authenticator app for both iOS and Android:');?></p>
			<h3><?php echo _('Google Authenticator');?></h3>
			<div class="well">
				<p class="google-auth"><img src="img/google-authenticator.png" title="Google Authenticator" width="100" height="100"/></p><br/>
				<div class="google-auth-links">
					<p><span class="icon icon-apple"></span> <strong><?php echo _('iOS');?></strong> → <a href="https://itunes.apple.com/en/app/google-authenticator/id388497605?mt=8" target="_blank">https://itunes.apple.com/en/app/google-authenticator/id388497605?mt=8</a></p>
					<p><span class="icon icon-android"></span> <strong><?php echo _('Android');?></strong> → <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en" target="_blank">https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en</a></p>
				</div>
			</div>
			</div>
			<div class="modal-footer">
			  <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign"></i> <?php echo _('Okay');?></a>
			</div>
			</div>
			<!-- Two factor authenticator apps link -->
	        
	        <?php 
		        include('includes/helpers/two-factor/vendor/base32.php');
		        $base32 = new Base32();
		        $secret_key_ran = strtoupper(ran_string(10, 10, true, false, true));
		        $secret_key = $base32->encode($secret_key_ran);
		        $r = mysqli_query($mysqli, 'SELECT company FROM login WHERE id = '.get_app_info('main_userID'));
		        if ($r) while($row = mysqli_fetch_array($r)) $main_company = $row['company'];  
		        $issuer = get_app_info('is_sub_user') ? $main_company : 'Sendy';
		        $qrcode = 'http://chart.apis.google.com/chart?cht=qr&chs=200x200&chl=otpauth://totp/'.$issuer.':'.get_app_info('email').'?secret='.$secret_key.'&issuer='.$issuer;
	        ?>
	        
	        <script type="text/javascript">
		        //Two-factor
				$("#enable-two-factor-btn").click(function(e){
					e.preventDefault(); 
					show_two_factor_setup();
				});
				$("#confirm-two-factor-btn").click(function(e){
					e.preventDefault(); 
					confirm_otp();
				});
				$("#otp").keypress(function(e) {
				    if(e.keyCode == 13) {
						e.preventDefault();
						confirm_otp();
				    }
				});
				$("#disable-two-factor-btn").click(function(e){
					e.preventDefault(); 
					if(confirm("Are you sure you want to disable two-factor authentication?"))
					{
						$.post("<?php echo get_app_info("path");?>/includes/settings/two-factor.php", { enable: 0, otp: 0, uid: <?php echo get_app_info('userID');?> },
						  function(data) {
						      if(data)
						      {
							      $("#disable-2fa-btn").slideUp();
							      $("#enable-2fa-btn").slideDown();
							      $("#two-factor-setup").slideUp();
						      } else alert("<?php echo _('Unable to save. Please try again.');?>");
						  }
						);
					}
				});
				$("#regenerate-key").click(function(e){
					show_two_factor_setup();
				});
				$("#cancel-two-factor").click(function(e){
					e.preventDefault(); 
					$("#two-factor-setup").slideUp();
				});
				function confirm_otp()
				{
					$.post("<?php echo get_app_info("path");?>/includes/settings/two-factor.php", { enable: 1, key: "<?php echo $secret_key;?>", otp: $("#otp").val(), uid: <?php echo get_app_info('userID');?> },
					  function(data) {
					      if(data)
					      {
						      if(data=="confirmed")
						      {
						      	$("#two-factor-setup").slideUp();
						      	$("#disable-2fa-btn").slideDown();
						      	$("#enable-2fa-btn").slideUp();
						      	$("#re-key").hide();
						      }
						      else if(data=="not confirmed")
						        alert("<?php echo _('OTP is correct, but unable to save.');?>");
						      else if(data=="incorrect")
						      	alert("<?php echo _('OTP code is incorrect. Please try again.');?>");
						      else if(data=="not numeric")
						      	alert("<?php echo _('Please enter a valid, numeric OTP code.');?>");
					      } else alert("<?php echo _('Unable to save. Please try again.');?>");
					  }
					);
				}
				function show_two_factor_setup()
				{
					$("#two-factor-setup").slideDown();
					$("#qr-code").attr("src", "<?php echo $qrcode;?>");
					$("#secret-key").text("<?php echo $secret_key;?>");
					$("#otp").val("");
					$("#otp").focus();
				}
	        </script>
	        
	        <label for="timezone"><?php echo _('Select your timezone');?></label>
    		<select id="timezone" name="timezone">
			  <option value="<?php echo get_user_data('timezone');?>"><?php echo get_user_data('timezone');?></option> 
			  <?php get_timezone_list();?>
			</select>
			
			<br/><br/>
			
			<label for="language"><?php echo _('Select your language');?></label>
    		<select id="language" name="language">
			  <option value="<?php echo get_user_data('language');?>"><?php echo get_user_data('language');?></option>
			  <?php 
					if($handle = opendir('locale')) 
					{
						$i = -1;						
					    while (false !== ($file = readdir($handle))) 
					    {
					    	if($file!='.' && $file!='..' && substr($file, 0, 1)!='.')	
					    	{
					    		if(get_user_data('language')!=$file)
							    	echo '<option value="'.$file.'">'.$file.'</option>';
						    }
							
							$i++;
					    }
					    closedir($handle);
					}
			  ?>
			</select>
	        
	        <br/><br/>
			<?php if(!get_app_info('is_sub_user')): //if not sub user ?>
			<hr/>
			<h3><?php echo _('Amazon Web Services Credentials');?></h3><br/>
			
	        <label class="control-label" for="aws_key"><?php echo _('AWS Access Key ID');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="aws_key" name="aws_key" placeholder="<?php echo _('AWS Access Key ID');?>" value="<?php echo get_user_data('s3_key');?>">
	            </div>
	        </div>
	        
	        <label class="control-label" for="aws_secret"><?php echo _('AWS Secret Access Key');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="aws_secret" name="aws_secret" placeholder="<?php echo _('AWS Secret Acesss Key');?>" value="<?php echo get_user_data('s3_secret');?>">
	            </div>
	        </div>
	        <br/>
	        
			<h3><?php echo _('Amazon SES region');?></h3><br/>	  
			<p style="width: 280px;"><?php echo _('Select your Amazon SES region. Please select the same region as what\'s set in your Amazon SES console in the region selection drop down menu at the top right.');?></p>      
	        <label for="ses_endpoint"><?php echo _('Your Amazon SES region');?></label>
	        <?php 
		        if(get_user_data('ses_endpoint')=='email.us-east-1.amazonaws.com') $endpoint_name = 'N. Virginia';
		        else if(get_user_data('ses_endpoint')=='email.us-west-2.amazonaws.com') $endpoint_name = 'Oregon';
		        else if(get_user_data('ses_endpoint')=='email.eu-west-1.amazonaws.com') $endpoint_name = 'Ireland';
	        ?>
    		<select id="ses_endpoint" name="ses_endpoint">
			  <option value="<?php echo get_user_data('ses_endpoint');?>"><?php echo $endpoint_name;?></option> 
			  <?php if($endpoint_name == 'N. Virginia'):?>
			  <option value="email.us-west-2.amazonaws.com">Oregon</option>
			  <option value="email.eu-west-1.amazonaws.com">Ireland</option>
			  <?php elseif($endpoint_name == 'Oregon'):?>
			  <option value="email.us-east-1.amazonaws.com">N. Virginia</option> 
			  <option value="email.eu-west-1.amazonaws.com">Ireland</option>
			  <?php elseif($endpoint_name == 'Ireland'):?>
			  <option value="email.us-east-1.amazonaws.com">N. Virginia</option> 
			  <option value="email.us-west-2.amazonaws.com">Oregon</option>
			  <?php endif;?>
			</select>
	        <br/><br/><br/>
	        
	        <?php if(get_app_info('s3_key')!='' && get_app_info('s3_secret')!=''):?>
	        <h3><?php echo _('Sending rate');?></h3><br/>
			<p style="width: 280px;"><?php echo _('Sendy sends bulks of emails in parallel per second according to your Amazon SES send rate. Depending on the capability of your server, your server may have trouble processing huge bulks of emails efficiently if your SES send rate is very high. Lowering your send rate may yield better sending speed and stability.');?></p> 
	        <label class="control-label" for="aws_key"><?php echo _('Adjust sending rate'); echo ' '; echo '('; echo _('Max'); echo ': '; echo $ses_send_rate; echo ')';?></label>
	    	<div class="control-group">
	            <div class="input-prepend input-append">
	              <input type="text" class="input-xlarge" id="send_rate" name="send_rate" value="<?php echo get_user_data('send_rate')=='' || get_user_data('send_rate')==0 ? $ses_send_rate : get_user_data('send_rate');?>" style="width: 80px;"><span class="add-on"><?php echo _('emails per second');?></span>
	              <input type="hidden" id="ses_send_rate" name="ses_send_rate" value="<?php echo $ses_send_rate;?>">
	            </div>
	        </div>
	        <br/>
	        <?php endif;?>
	        
	        <hr/>
	        <h3><?php echo _('PayPal account');?></h3><br/>
	        <p style="width: 280px;"><?php echo _('If you charge your client(s) a fee for sending newsletters, they\'ll pay to this PayPal account. Also, don\'t forget to turn <strong>Auto Return</strong> ON in your PayPal account under <strong>Profile > My sellings tools > Website preferences</strong> so that your client will be automatically re-directed to the sending script after payment. Just use your website\'s URL for the <strong>Return URL</strong> to be able to save.');?></p>
	        <label class="control-label" for="paypal"><?php echo _('PayPal email address');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="paypal" name="paypal" placeholder="<?php echo _('PayPal account email address');?>" value="<?php echo get_user_data('paypal');?>">
	            </div>
	        </div>
	        
	        <?php else:?>
	        <h3><?php echo _('Sending preferences');?></h3><br/>
	        
	        <label class="control-label" for="from_name"><?php echo _('From name');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="from_name" name="from_name" placeholder="<?php echo _('From name');?>" value="<?php echo get_saved_data('from_name');?>">
	            </div>
	        </div>
	        
	        <label class="control-label" for="from_email"><?php echo _('From email');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" disabled="disabled" id="from_email" name="from_email" placeholder="<?php echo _('From email');?>" value="<?php echo get_saved_data('from_email');?>">
	            </div>
	        </div>
	        
	        <label class="control-label" for="reply_to"><?php echo _('Reply to email');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="reply_to" name="reply_to" placeholder="<?php echo _('Reply to email');?>" value="<?php echo get_saved_data('reply_to');?>">
	            </div>
	        </div>
	        
	        <?php endif;?>
	        
	        <input type="hidden" name="uid" value="<?php echo get_app_info('userID');?>">
	        
	        <?php $ii = get_app_info('is_sub_user') ? '?i='.get_app_info('app') : ''?>
	        <input type="hidden" name="redirect" id="redirect" value="<?php echo get_app_info('path').'/settings'.$ii;?>">
	        
	        <button type="submit" class="btn btn-inverse"><i class="icon-ok icon-white"></i> <?php echo _('Save');?></button>
	    </form>
    </div>   
    
    <!-- Check if sub user -->
	<?php if(!get_app_info('is_sub_user')):?>	 
    <div class="span5">
	    <h2><?php echo _('Your license key');?></h2><br/>
	    <div>
		    <p><?php echo _('You\'ll need this license key to');?> <a href="https://sendy.co/get-updated?l=<?php echo get_app_info('license');?>" target="_blank" style="text-decoration:underline"><?php echo _('download the latest version of Sendy on our website');?></a>.</p>
		    <div class="well"><strong id="license-key"><?php echo get_user_data('license');?></strong></div>
	    </div>
	    <h2><?php echo _('Your API key');?></h2><br/>
	    <div>
		    <div class="well"><strong id="api-key"><?php echo get_user_data('api_key');?></strong></div>
	    </div>
	    <script type="text/javascript">
	    	$(document).ready(function() {
	    		$("#license-key, #api-key").click(function(){$(this).selectText();});
	    	});
	    </script>
    </div>
    <?php endif;?>
</div>
<!-- Validation -->
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#settings-form").validate({
			rules: {
				company: {
					required: true
				},
				personal_name: {
					required: true
				},
				email: {
					required: true,
					email: true
				},
				send_rate: {
					required: true
					,range: [1, <?php echo $ses_send_rate;?>]
					,number: true
				}
			},
			messages: {
				company: "<?php echo addslashes(_('Your company name is required'));?>",
				personal_name: "<?php echo addslashes(_('Your name is required'));?>",
				email: "<?php echo addslashes(_('A valid email is required'));?>"
			}
		});
	});
</script>
<?php include('includes/footer.php');?>
