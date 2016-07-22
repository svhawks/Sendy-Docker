<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/ares/main.php');?>
<?php include('includes/helpers/short.php');?>

<?php 
	//IDs
	$aid = isset($_GET['a']) && is_numeric($_GET['a']) ? mysqli_real_escape_string($mysqli, $_GET['a']) : exit;
	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/autoresponders-emails?i='.get_app_info('restricted_to_app').'&a='.$aid.'"</script>';
			exit;
		}
	}
?>

<script type="text/javascript" src="<?php echo get_app_info('path')?>/js/fancybox/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path')?>/js/fancybox/jquery.fancybox.css" media="screen" />
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {		
		//iframe preview
		$(".iframe-preview").click(function(e) {
			e.preventDefault();
			
			$.fancybox.open({
				href : $(this).attr("href"),
				type : 'iframe',
				padding : 0
			});
		});
	});
</script>

<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span10">
    	<div class="row-fluid">
	    	<div class="span12">
		    	<div>
			    	<p class="lead"><?php echo get_app_data('app_name');?></p>
		    	</div>
		    	<h2><?php echo _('Autoresponder emails');?></h2><?php echo _('For');?>: <span class="label label-info"><?php echo get_ares_data('name');?></span> <span>(<?php echo get_ares_type_name('type');?>)</span>
	    	</div>
	    </div>
	    
	    <br/><br/>
	    
	    <a href="<?php echo get_app_info('path')?>/autoresponders-create?i=<?php echo get_app_info('app')?>&a=<?php echo $aid?>" title="" class="btn"><i class="icon icon-plus"></i> <?php echo _('Add a new email to this autoresponder');?></a>
	    
	    <br/><br/>
	    
	    <div class="row-fluid">
	    	<div class="span12 ares">
				<table class="table ares-email-table table-striped responsive">
		          <thead>
		            <tr>
		              <th><?php echo _('Send');?></th>
		              <th><?php echo _('Email');?></th>
		              <th><?php echo _('Sent');?></th>
		              <th><?php echo _('Unique Opens');?></th>
		              <th><?php echo _('Unique Clicks');?></th>
		            </tr>
		          </thead>
		          <tbody>
		          	<?php 
			          	$q = 'SELECT * FROM ares_emails WHERE ares_id = '.$aid;
			          	$r = mysqli_query($mysqli, $q);
			          	if ($r && mysqli_num_rows($r) > 0)
			          	{
			          	    while($row = mysqli_fetch_array($r))
			          	    {
			          			$ares_email_id = $row['id'];
			          			$time_condition = $row['time_condition'];
			          			$title = $row['title'];
			          			$recipients = $row['recipients'];
			          			$opens = $row['opens'];
			          			$from_email = $row['from_email'];
			          			
			          			//opens
			          			if($opens=='')
					  			{
					  				$percentage_opened = 0;
						  			$opens_unique = 0;
					  			}
					  			else
					  			{
						  			$opens_array = explode(',', $opens);
						  			$opens_unique = count(array_unique($opens_array));
						  			$percentage_opened = round($opens_unique/$recipients * 100, 2);
						  		}
						  		
						  		if($recipients==0 || $recipients=='') $percentage_clicked = round(get_click_percentage($ares_email_id) *100, 2);
					  			else $percentage_clicked = round(get_click_percentage($ares_email_id)/$recipients *100, 2);
			          			
			          			//format time condition
			          			if($time_condition=='immediately')
				          			$time_condition = _('On').'<br/><b>'._('sign up').'</b>';
				          		else if($time_condition=='')
				          		{
				          			if(get_ares_data('type')==2)
					          			$time_condition = _('Annually on').'<br/><b>'.get_ares_data('custom_field').'</b>';
					          		else
					          			$time_condition = _('Once on').'<br/><b>'.get_ares_data('custom_field').'</b>';
				          		}
				          		else
				          		{
					          		if(substr($time_condition, 0, 1)=='+')
					          		{
						          		switch(get_ares_data('type'))
							    		{
								    		case 1:
								    		$time_condition_beforeafter = _('after').'<br/><b>'._('signup').'</b>';
								    		break;
								    		
								    		default:
								    		$time_condition_beforeafter = _('after').'<br/><b>'.get_ares_data('custom_field').'</b>';
							    		}
						          	}
						          	else
						          	{
						          		switch(get_ares_data('type'))
							    		{
								    		case 1:
								    		$time_condition_beforeafter = _('before').'<br/><b>'._('signup').'</b>';
								    		break;
								    		
								    		default:
								    		$time_condition_beforeafter = _('before').'<br/><b>'.get_ares_data('custom_field').'</b>';
							    		}
						          	}
					          		
					          		$time_condition = substr($time_condition, 1);
					          		$time_condition_array = explode(' ', $time_condition);
					          		switch($time_condition_array[0])
					          		{
						          		case 1:
								    	$time_condition = '1 '.substr($time_condition_array[1], 0, -1).' '.$time_condition_beforeafter;
								    	break;
								    	
								    	default:
								    	$time_condition = $time_condition.' '.$time_condition_beforeafter;
								    	break;
					          		}
				          		}
			          			
			          			//tags for subject
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
								
								//convert date
								if(get_app_info('timezone')!='') date_default_timezone_set(get_app_info('timezone'));
								$today = time();
								$currentdaynumber = strftime('%d', $today);
								$currentday = strftime('%A', $today);
								$currentmonthnumber = strftime('%m', $today);
								$currentmonth = strftime('%B', $today);
								$currentyear = strftime('%Y', $today);
								$unconverted_date = array('[currentdaynumber]', '[currentday]', '[currentmonthnumber]', '[currentmonth]', '[currentyear]');
								$converted_date = array($currentdaynumber, $currentday, $currentmonthnumber, $currentmonth, $currentyear);
								$title = str_replace($unconverted_date, $converted_date, $title);
		          	?>
		          	<tr id="email-<?php echo $ares_email_id;?>">
			          <td><?php echo $time_condition;?></td>
		              <td>
		              	<strong><?php echo $title;?></strong><br/>
		              	<div class="btns">
		              	
		              		<ul class="ares_email_options">
					            <li><a href="<?php echo get_app_info('path')?>/autoresponders-edit?i=<?php echo get_app_info('app')?>&a=<?php echo $aid?>&ae=<?php echo $ares_email_id?>" title=""><?php echo _('Edit');?></a></li>
					            <li><a href="<?php echo get_app_info('path');?>/w/<?php echo short($ares_email_id);?>/a?<?php echo time();?>" title="" class="iframe-preview"><?php echo _('Preview');?></a></li>
					            <li><a href="javascript:void(0)" title="" id="delete-<?php echo $ares_email_id;?>" data-id="<?php echo $ares_email_id;?>"><?php echo _('Delete');?></a></li>
					            <li><a href="<?php echo get_app_info('path');?>/autoresponders-report.php?i=<?php echo get_app_info('app')?>&a=<?php echo $aid;?>&ae=<?php echo $ares_email_id; ?>" title=""><?php echo _('View report');?></a></li>
					        </ul>
				            
				            <script type="text/javascript">
				            	$("#delete-<?php echo $ares_email_id?>").click(function(e){
				            		e.preventDefault(); 
									c = confirm("<?php echo _('Confirm delete');?> '<?php echo $title;?>'?");
									if(c)
									{
						            	$.post("<?php echo get_app_info('path')?>/includes/ares/delete-email.php", { id: $(this).data("id") },
					            		  function(data) {
					            		      if(data)
					            		      {
					            		      	$("#email-<?php echo $ares_email_id;?>").fadeOut();
					            		      }
					            		      else
					            		      {
					            		      	alert("<?php echo _('Sorry, unable to delete. Please try again later!');?>");
					            		      }
					            		  }
					            		);
					            	}
				            	});
				            </script>
				        </div>
		              </td>
		              <td><?php echo $recipients;?></td>
		              <td><span class="label"><?php echo $percentage_opened;?>%</span> <?php echo number_format($opens_unique);?> <?php echo _('opened');?></td>
		              <td><span class="label"><?php echo $percentage_clicked;?>%</span> <?php echo number_format(get_click_percentage($ares_email_id));?> <?php echo _('clicked');?></td>
		            </tr>
		            <?php 
				            }  
			          	}
			          	else
			          	{
				          	echo '
				          		<tr>
				          			<td colspan="5">'._('No autoresponder emails are set up.').'</td>
				          		</tr>
				          	';
			          	}
		            ?>
		          </tbody>
		        </table>
		        <br/>
		        <a href="<?php echo get_app_info('path');?>/autoresponders-list?i=<?php echo $_GET['i']?>&l=<?php echo get_ares_data('list');?>" title=""><i class="icon icon-chevron-left"></i> <?php echo _('Back to autoresponders list');?></a>
			</div>
	    </div>
	    
    </div>
</div>

<?php include('includes/footer.php');?>
