<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/app?i='.get_app_info('restricted_to_app').'"</script>';
		exit;
	}
?>
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
    <div class="span10">
    	<h2><?php echo _('Select a brand');?></h2><br/>
    	
    	<p><button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/new-brand'"><i class="icon-plus-sign"></i> <?php echo _('Add a new brand');?></button></p><br/>
    	
    	<!-- Auto select encrypted listID -->
	  	<script type="text/javascript">
	  		$(document).ready(function() {
				$(".brand-id").mouseover(function(){
					$(this).selectText();
				});
			});
		</script>
    	
	    <table class="table table-striped responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('ID');?></th>
		      <th><?php echo _('Brands');?></th>
		      <th><?php echo _('Monthly limit');?></th>
		      <th><?php echo _('Used');?></th>
		      <th><?php echo _('Edit');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	<?php 
			  	$q = 'SELECT * FROM apps WHERE userID = '.get_app_info('userID').' ORDER BY app_name ASC';
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = $row['id'];
			  			$title = $row['app_name'];
			  			$from_email = explode('@', $row['from_email']);
			  			$get_domain = $from_email[1];
			  			$allocated_quota = $row['allocated_quota'];
			  			$current_quota = $row['current_quota'];
			  			$day_of_reset = $row['day_of_reset'];
			  			$month_of_next_reset = $row['month_of_next_reset'];
			  			$brand_logo_filename = $row['brand_logo_filename'];
			  			
			  			//Brand logo
			  			if($brand_logo_filename=='') $logo_image = 'https://www.google.com/s2/favicons?domain='.$get_domain;
			  			else $logo_image = get_app_info('path').'/uploads/logos/'.$brand_logo_filename;
			  			
			  			//Check if limit needs to be reset	
						$today_unix_timestamp = time();
						$brand_monthly_quota = $allocated_quota;
						if($brand_monthly_quota!=-1)
						{				
							$day_today = strftime("%e", $today_unix_timestamp);
							$month_today = strftime("%b", $today_unix_timestamp);
							$year_today = strftime("%G", $today_unix_timestamp);
							$no_of_days_this_month = cal_days_in_month(CAL_GREGORIAN, strftime("%m", $today_unix_timestamp), $year_today);
							
							$brand_limit_resets_on = $day_of_reset>$no_of_days_this_month ? $no_of_days_this_month : $day_of_reset;
							$brand_month_of_next_reset = $month_of_next_reset;
							
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
								$q2 = 'UPDATE apps SET current_quota = 0, month_of_next_reset = "'.$month_next.'" WHERE id = '.$id;
								$r2 = mysqli_query($mysqli, $q2);
								if($r2) 
								{
									//Set $current_quota to 0 since current_quota has been reset
									$current_quota = 0;
								}
							}
						}
			  			
			  			//Prepare numbers
			  			if($allocated_quota==-1) 
			  			{
			  				$allocated_quota = '<span style="font-size: 16px;color:#969696;">&infin;</span>';
			  				$current_quota = '<span style="font-size: 16px;color:#969696;">&infin;</span>';
			  			}
			  			else
			  			{
				  			$allocated_quota = number_format($allocated_quota);
			  				if($current_quota>$row['allocated_quota']) $current_quota = '<span style="color:#FF0000;font-weight:bold;">'.number_format($current_quota).'</span>';
			  				else $current_quota = number_format($current_quota);
			  			}
			  			
			  			echo '
			  			<tr id="'.$id.'">
			  				<td><span class="label brand-id">'.$id.'</span></td>
			  				<td><a href="'.get_app_info('path').'/app?i='.$id.'" title=""><img src="'.$logo_image.'" style="margin:-3px 5px 0 0; width:16px; height: 16px;"/>'.$title.'</a></td>
			  				<td>'.$allocated_quota.'</td>
			  				<td>'.$current_quota.'</td>
			  				<td><a href="'.get_app_info('path').'/edit-brand?i='.$id.'" title=""><span class="icon icon-pencil"></span></a></td>
			  				<td><a href="#" title="'._('Delete').' '.$title.'" id="delete-btn-'.$id.'"><span class="icon icon-trash"></span></a></td>
			  				<script type="text/javascript">
					    	$("#delete-btn-'.$id.'").click(function(e){
							e.preventDefault(); 
							c = confirm("'._('All campaigns, lists, subscribers will be permanently deleted. Confirm delete').' '.$title.'?");
							if(c)
							{
								$.post("includes/app/delete.php", { id: '.$id.' },
								  function(data) {
								      if(data)
								      {
								      	$("#'.$id.'").fadeOut();
								      }
								      else
								      {
								      	alert("'._('Sorry, unable to delete. Please try again later!').'");
								      }
								  }
								);
							}
							});
						    </script>
			  			</tr>';
			  	    }  
			  	}
			  	else
			  	{
				  	echo '
				  	<tr>
				  		<td><a href="'.get_app_info('path').'/new-brand" title="">'._('Add your first brand!').'</a></td>
				  		<td></td>
				  		<td></td>
				  		<td></td>
				  		<td></td>
				  		<td></td>
				  	</tr>
				  	';
			  	}
		  	?>
		  </tbody>
		</table>
    </div>   
</div>
<?php include('includes/footer.php');?>
