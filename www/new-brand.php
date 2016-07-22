<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>

<!-- Validation -->
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#settings-form").validate({
			rules: {
				app_name: {
					required: true	
				},
				from_name: {
					required: true	
				},
				from_email: {
					required: true,
					email: true
				},
				reply_to: {
					required: true,
					email: true
				}
			},
			messages: {
				app_name: "<?php echo addslashes(_('Please specify your brand\'s name'));?>",
				from_name: "<?php echo addslashes(_('\'From name\' is required'));?>",
				from_email: "<?php echo addslashes(_('A valid \'From email\' is required'));?>",
				reply_to: "<?php echo addslashes(_('A valid \'Reply to\' email is required'));?>"
			}
		});
		
		//Check if login email clashes with the main login email address
		$("#settings-form").submit(function(e){			
			login_email = $('#login_email').val();
			if(login_email == "<?php echo get_app_info('email');?>")
			{
				e.preventDefault(); 
				$("#duplicate-login-email").show();
			}
			
		});
	});
</script>

<form action="<?php echo get_app_info('path')?>/includes/app/create.php" method="POST" accept-charset="utf-8" class="form-vertical" enctype="multipart/form-data" id="settings-form">

<div class="row-fluid">
	<div class="span2">
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
	</div>
    <div class="span5">
    	<h2><?php echo _('New brand');?></h2><br/>
	    	
    	<label class="control-label" for="app_name"><?php echo _('Brand name');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="app_name" name="app_name" placeholder="<?php echo _('The name of the brand you\'re sending from');?>">
            </div>
        </div>
        
        <label class="control-label" for="from_name"><?php echo _('From name');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="from_name" name="from_name" placeholder="<?php echo _('From name');?>">
            </div>
        </div>
        
        <label class="control-label" for="from_email"><?php echo _('From email');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="from_email" name="from_email" placeholder="<?php echo _('From email');?>">
            </div>
            <p id="verification-check-loader" style="display:none;"><img src="<?php echo get_app_info('path')?>/img/loader.gif" style="width:16px;"/> <?php echo _('Checking if your \'From email\' is verified in your SES console..');?><br/><br/></p>
            <div class="alert alert-danger" id="unverified-email" style="display:none;"><strong><i class="icon icon-warning-sign"></i> <?php echo _('Unverified \'From email\'');?></strong>: <?php echo _('Your \'From email\' or its domain is not verified in your Amazon SES console. Do either of the following:');?>
            <br/><br/>
            	<ul>
		            <li id="click-to-verify-copy"><a href="javascript:void(0);" id="click-to-verify-btn"><?php echo _('Click here to verify this \'From email\', Amazon will send a verification email to this \'From email\'');?> →</a></li>
		            <li><a href="https://console.aws.amazon.com/ses/home?#verified-senders-domain:" target="_blank"><?php echo _('Alternatively, verify this \'From email\'s \'domain\' in your Amazon SES console');?> →</a></li>
		        </ul>
		    <br/>
		    <p><?php echo _('When you verify a domain, you can use any \'From email\' address belonging to that domain in future without having to verify them individually. This is good if you\'re setting up this brand for your client and you do not want Amazon to send them emails to verify their \'From email\' addresses.');?></p>
            </div>
            <div class="alert alert-danger" id="unverified-email-pending" style="display:none;"><strong><i class="icon icon-warning-sign"></i> <?php echo _('\'From email\' pending verification');?></strong>: <?php echo _('Your \'From email\' or its domain is pending verification in your Amazon SES console. Please complete the verification.');?></div>
            <div class="alert alert-danger" id="api-error" style="display:none;"><strong><i class="icon icon-warning-sign"></i> <?php echo _('Unable to communicate with Amazon SES API');?></strong>: <?php echo _('Please check the error message on the left for instructions.');?></div>
            <div class="alert alert-success" id="verified-email" style="display:none;"><strong><i class="icon icon-ok"></i> <?php echo _('Congrats! This \'From email\' is verified.');?></strong></div>
            
            <?php if(get_app_info('s3_key')!='' && get_app_info('s3_key')!=''):?>
            <script type="text/javascript">
            	$(document).ready(function() {
            		$("#from_email").focusout(function(){
            			$("#verification-check-loader").show();
            			$("#unverified-email").hide();
            			$("#unverified-email-pending").hide();
            			$("#api-error").hide();
            			$("#verified-email").hide();
            			
	            		$.post("<?php echo get_app_info('path')?>/includes/app/check-email-verification.php", { from_email: $("#from_email").val(), auto_verify: 'no' },
            			  function(data) {
            			       if(data=='unverified')
            			      {
            			      	$("#verification-check-loader").hide();
            			      	$("#unverified-email").show();
            			      	$("#unverified-email-pending").hide();
            			      	$("#api-error").hide();
            			      	$("#verified-email").hide();
            			      }
            			      else if(data=='pending verification')
            			      {
	            			    $("#verification-check-loader").hide();
	            			    $("#unverified-email").hide();
            			      	$("#unverified-email-pending").show();
            			      	$("#api-error").hide();
            			      	$("#verified-email").hide();
            			      }
            			      else if(data=='verified')
            			      {
            			      	$("#verification-check-loader").hide();
            			      	$("#unverified-email").hide();
            			      	$("#unverified-email-pending").hide();
            			      	$("#api-error").hide();
            			      	$("#verified-email").show();
            			      }
            			      else if(data=="api error")
            			      {
	            			    $("#verification-check-loader").hide();
            			      	$("#unverified-email").hide();
            			      	$("#unverified-email-pending").hide();
            			      	$("#api-error").show();
            			      	$("#verified-email").hide();
            			      }
            			      else
            			      {
	            			  	$("#verification-check-loader").hide();
            			      }
            			  }
            			);
            		});
            		$("#click-to-verify-btn").click(function(e){
            			e.preventDefault();
            			$("#click-to-verify-copy").html("<?php echo _('Please wait..');?>");
            			$.post("<?php echo get_app_info('path')?>/includes/app/verify-email.php", { from_email: $("#from_email").val() },
        				  function(data) {
        				      if(data)
        				      {
        				      	if(data=="success")
        				      		$("#unverified-email").html("<?php echo _('We have just sent your \'From email\' address to Amazon SES for verification. An email from Amazon is sent to your \'From email\' address with a confirmation link to complete the verification. Click the link to complete the verification, then refresh this page.');?>");
        				      	else
        				      		$("#unverified-email").html("Unable to send your \'From email\' address to Amazon SES for verification! Please try again later.");
        				      }
        				      else
        				      {
        				      	alert("Sorry, unable to verify email address. Please try again later!");
        				      }
        				  }
        				);
        			});
            	});
            </script>
            <?php endif;?>
            
        </div>
        
        <label class="control-label" for="reply_to"><?php echo _('Reply to email');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="reply_to" name="reply_to" placeholder="<?php echo _('Reply to email');?>">
            </div>
        </div>
        
        <label class="control-label" for="reply_to"><?php echo _('Allowed attachments file types');?><br/><i><?php echo _('(empty field to disable attachments in campaigns)')?></i></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="allowed_attachments" name="allowed_attachments" placeholder="jpeg,jpg,gif,png,pdf,zip" value="jpeg,jpg,gif,png,pdf,zip">
            </div>
        </div>
        
        <label class="control-label" for="logo"><?php echo _('Brand logo');?> <em class="thirtytwo"><?php echo _('(32 x 32 pixel, jpeg, jpg, gif or png format)');?></em></label>
        <div class="control-group">
	    	<div class="controls">
	    		<input type="file" id="logo" name="logo" />
            </div>
        </div>
        
        <input type="hidden" name="uid" value="<?php echo get_app_info('userID');?>">
        
        <hr/>
        
        <h3><?php echo _('SMTP settings (only if you\'re not using Amazon SES)');?></h3><br/>
        
        <div class="well">
	        <?php echo _('If you prefer using other email service providers over Amazon SES for sending emails, set your SMTP settings here. You must also remove your \'Amazon Web Services Credentials\' from the main settings so that emails are sent via these SMTP settings. Note that multi-threading is not supported, bounces and complaints will also not be registered if you use other email service providers to send emails.');?>
        </div>
        
        <label class="control-label" for="smtp_host"><?php echo _('Host');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="smtp_host" name="smtp_host" placeholder="eg. smtp.gmail.com">
            </div>
        </div>
        
        <label class="control-label" for="smtp_port"><?php echo _('Port');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="smtp_port" name="smtp_port" placeholder="eg. 465">
            </div>
        </div>
        
        <label class="control-label" for="smtp_ssl">SSL / TLS</label>
    	<div class="control-group">
	    	<div class="controls">
				<select name="smtp_ssl">
				  <option value="ssl" id="ssl">SSL</option>
				  <option value="tls" id="tls">TLS</option>
				 </select>
            </div>
        </div>
        
        <label class="control-label" for="smtp_username"><?php echo _('Username');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="smtp_username" name="smtp_username" placeholder="<?php echo _('Username (usually your email)');?>" autocomplete="off">
            </div>
        </div>
        
        <label class="control-label" for="smtp_password"><?php echo _('Password');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="password" class="input-xlarge" id="smtp_password" name="smtp_password" placeholder="<?php echo _('Your password');?>" autocomplete="off">
            </div>
        </div>
        
        <button type="submit" class="btn btn-inverse"><i class="icon-ok icon-white"></i> <?php echo _('Save');?></button>
    </div>   
    
    <div class="span5">
	    <h2><?php echo _('Brand settings');?></h2><br/>
	    
	    <div class="alert alert-info"><?php echo _('If you\'re creating this brand for your client, you can allow them to send newsletters on their own at a fee you preset below.');?> <?php echo _('Send the');?> <strong><?php echo _('Client login details');?></strong> <?php echo _('to your client so that they can login to manage lists, subscribers and send newsletters.');?><br/><br/><?php echo _('Also, don\'t forget to set your PayPal account email address in');?> <a href="<?php echo get_app_info('path');?>/settings"><?php echo _('Settings');?></a>.</div><br/>
	    
	    <div class="well">
	    	<h3><?php echo _('Client login details');?></h3><br/>
	    	<p><strong><?php echo _('Login URL');?></strong>: <?php echo get_app_info('path');?></p>
		    <p><strong><?php echo _('Login email');?></strong>: <span id="login-email"><input type="text" name="login_email" id="login_email" style="margin-top: 5px;"/></span></p>
		    <div class="alert alert-danger" id="duplicate-login-email" style="display:none;"><i class="icon icon-warning-sign"></i> <?php echo _('This login email is already in use by your main login email address set in your main Settings. Please use another email address.');?> </div>
	    	<p><strong><?php echo _('Password');?></strong>: <span id="generate-password-wrapper"><?php 
		    	$pass = ran_string(8, 8, true, false, true);
		    	echo $pass;
	    	?></span></p>
	    	<p><strong><strong><?php echo _('Language');?></strong>: </strong>
				<select id="language" name="language" style="margin-top:5px;">
				  <option value="en_US">en_US</option>
				  <?php 
						if($handle = opendir('locale')) 
						{
							$i = -1;						
						    while (false !== ($file = readdir($handle))) 
						    {
						    	if($file!='.' && $file!='..' && substr($file, 0, 1)!='.')	
						    	{
						    		if($file!='en_US')
								    	echo '<option value="'.$file.'">'.$file.'</option>';
							    }
								
								$i++;
						    }
						    closedir($handle);
						}
				  ?>
				</select>
			</p>
	    	<input type="hidden" name="pass" value="<?php echo $pass;?>"></input>
	    </div>
    	
    	<hr/>
    	<h3><?php echo _('Campaign fee settings');?></h3><br/>
	    	
    	<label class="control-label" for="currency"><?php echo _('Currency');?></label>
    	<div class="control-group">
	    	<div class="controls">
				<select name="currency">
				  <option value="USD" id="USD">U.S. Dollars</option>
				  <option value="CAD" id="CAD">Canadian Dollars</option>
				  <option value="EUR" id="EUR">Euros</option>
				  <option value="GBP" id="GBP">Pounds Sterling</option>
				  <option value="AUD" id="AUD">Australian Dollars</option>
				  <option value="JPY" id="JPY">Yen</option>
				  <option value="NZD" id="NZD">New Zealand Dollar</option>
				  <option value="CHF" id="CHF">Swiss Franc</option>
				  <option value="HKD" id="HKD">Hong Kong Dollar</option>
				  <option value="SGD" id="SGD">Singapore Dollar</option>
				  <option value="SEK" id="SEK">Swedish Krona</option>
				  <option value="DKK" id="DKK">Danish Krone</option>
				  <option value="PLN" id="PLN">Polish Zloty</option>
				  <option value="NOK" id="NOK">Norwegian Krone</option>
				  <option value="HUF" id="HUF">Hungarian Forint</option>
				  <option value="CZK" id="CZK">Czech Koruna</option>
				  <option value="ILS" id="ILS">Israeli Shekel</option>
				  <option value="MXN" id="MXN">Mexican Peso</option>
				  <option value="BRL" id="BRL">Brazilian Real</option>
				  <option value="MYR" id="MYR">Malaysian Ringgits</option>
				  <option value="PHP" id="PHP">Philippine Pesos</option>
				  <option value="TWD" id="TWD">Taiwan New Dollars</option>
				  <option value="THB" id="THB">Thai Baht</option>
				 </select>
            </div>
        </div>
        
        <label class="control-label" for="delivery_fee"><?php echo _('Delivery Fee');?></label>
    	<div class="control-group">
	    	<div class="controls">
	    		<div class="input-prepend input-append">
	              <span class="add-on">$</span><input type="text" class="input-xlarge" id="delivery_fee" name="delivery_fee" placeholder="Eg. 5" style="width: 80px;">
	            </div>
            </div>
        </div>
        
        <label class="control-label" for="cost_per_recipient"><?php echo _('Cost per recipient');?></label>
    	<div class="control-group">
	    	<div class="controls">
	    		<div class="input-prepend input-append">
	              <span class="add-on">$</span><input type="text" class="input-xlarge" id="cost_per_recipient" name="cost_per_recipient" placeholder="Eg. .01" style="width: 80px;">
	            </div>
            </div>
        </div>
        
        <hr/>
    	<h3><?php echo _('Set monthly limit');?></h3><br/>
    	
    	<label class="control-label" for="choose-limit"><?php echo _('Number of emails per month');?></label>
    	<div class="control-group">
	    	<div class="controls">
	    	
	    		<!-- Choice -->
				<select name="choose-limit" id="choose-limit">
				  <option value="unlimited" id="unlimited"><?php echo _('No limits');?></option>
				  <option value="custom" id="custom"><?php echo _('Set limit');?></option>
				 </select>
				 
				 <!-- Set number -->
				 <br/>
				 <input type="text" class="input-xlarge" id="monthly-limit" name="monthly-limit" placeholder="eg. 100000" value="" style="display:none;">
				 
				 <script type="text/javascript">
					 $(document).ready(function() {
					 	$("#choose-limit").change(function(){			
							if($(this).find(":selected").text()=='<?php echo _('Set limit');?>')
							{
								$("#monthly-limit").show();
								$("#limit-reset-label").show();
								$("#reset-on-day").show();
							}
							else
							{
								$("#monthly-limit").hide();
								$("#limit-reset-label").hide();
								$("#reset-on-day").hide();
							}
						});
					 });
				 </script>
            </div>
        </div>
        
        <!-- Reset on day -->
        <label class="control-label" for="reset-on-day" id="limit-reset-label" style="display:none;"><?php echo _('Reset limit on which day of the month?');?></label>
    	<div class="control-group">
	    	<div class="controls">
				<select name="reset-on-day" id="reset-on-day" style="width:80px; display:none;">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
					<option value="7">7</option>
					<option value="8">8</option>
					<option value="9">9</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
					<option value="13">13</option>
					<option value="14">14</option>
					<option value="15">15</option>
					<option value="16">16</option>
					<option value="17">17</option>
					<option value="18">18</option>
					<option value="19">19</option>
					<option value="20">20</option>
					<option value="20">21</option>
					<option value="20">22</option>
					<option value="20">23</option>
					<option value="20">24</option>
					<option value="20">25</option>
					<option value="20">26</option>
					<option value="20">27</option>
					<option value="20">28</option>
					<option value="20">29</option>
					<option value="20">30</option>
					<option value="20">31</option>
				</select>
	        </div>
        </div>
        
        <button type="submit" class="btn btn-inverse"><i class="icon-ok icon-white"></i> <?php echo _('Save');?></button>
        
    </div> 
</div>

</form>
<script type="text/javascript">
$(document).ready(function() {
	$("#from_email").keyup(function() {
		$("#reply_to").val($("#from_email").val());
		$("#login-email input").val($("#from_email").val());
	});
});
</script>
<?php include('includes/footer.php');?>