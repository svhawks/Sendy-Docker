<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/dashboard/main.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/app?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
	}
?>
<link href="<?php echo get_app_info('path');?>/js/tablesorter/theme.default.min.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/tablesorter/jquery.tablesorter.widgets.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('table').tablesorter({
			widgets        : ['saveSort'],
			usNumberFormat : true,
			sortReset      : true,
			sortRestart    : true,
			headers: { 2: { sorter: false}, 5: {sorter: false}, 6: {sorter: false} }	
		});
	});
</script>
<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span10">
    	<div>
	    	<p class="lead"><?php echo get_app_data('app_name');?></p>
    	</div>
    	<h2><?php echo _('All campaigns');?></h2><br/>
    	<div style="clear:both;">
    		<a href="<?php echo get_app_info('path');?>/create?i=<?php echo get_app_info('app');?>" class="btn"><i class="icon-plus-sign"></i> <?php echo _('Create & send new campaign');?></a>
    		<a href="#rss-modal" class="btn campaigns-rss-btn" style="float:right" title="<?php echo _('Campaigns RSS feed');?>" data-toggle="modal"><i class="icon icon-rss"></i></a>
    		<div id="rss-modal" class="modal hide fade">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h3><?php echo _('Campaigns RSS feed');?></h3>
            </div>
            <div class="modal-body">
				<p><?php echo _('The following is the link to your campaigns RSS feed. This RSS feed displays an archive of the last 100 campaigns previously sent.');?></p>
				<p class="well" id="feed-url"><?php echo get_app_info('path');?>/campaigns-rss?a=<?php echo get_app_data('app_key');?>&i=<?php echo get_app_info('app');?></p>
				<p><strong><?php echo _('Some ways to use your RSS feed');?></strong></p>
				<ul>
					<li><?php echo _('Integrate your campaigns feed with your website or application anyway you like');?></li>
					<li><?php echo _('Display an archive of your past campaigns on your website or application');?></li>
					<li><?php echo _('Let your audience subscribe to your RSS feed so they\'ll get notified whenever you send a campaign. Good for users who prefers RSS over email (or even both)');?></li>
					<li><?php echo _('Subscribe to your client\'s RSS feed (if you give access to your clients) to get notified when they send a new campaign');?></li>
				</ul>
				<script type="text/javascript">
					$(document).ready(function() {
						$("#feed-url").click(function(){
							$(this).selectText();
						});
					});
				</script>
            </div>
            <div class="modal-footer">
              <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign"></i> <?php echo _('Okay');?></a>
            </div>
          </div>
    		
    	</div>
    	
    	<br/>
    	
	    <table class="table table-striped responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('Campaign');?></th>
		      <th><?php echo _('Recipients');?></th>
		      <th><?php echo _('Sent');?></th>
		      <th><?php echo _('Unique Opens');?></th>
		      <th><?php echo _('Unique Clicks');?></th>
		      <th><?php echo _('Duplicate');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	
		  	<?php 
		  		$limit = 10;
				$total_subs = totals($_GET['i']);
				$total_pages = ceil($total_subs/$limit);
				$p = isset($_GET['p']) ? $_GET['p'] : null;
				$offset = $p!=null ? ($p-1) * $limit : 0;
				
			  	$q = 'SELECT * FROM campaigns WHERE userID = '.get_app_info('main_userID').' AND app='.get_app_info('app').' ORDER BY id DESC LIMIT '.$offset.','.$limit;
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = stripslashes($row['id']);
			  			$timezone = stripslashes($row['timezone']);
			  			if($timezone=='' || $timezone==0) date_default_timezone_set(get_app_info('timezone'));
			  			else date_default_timezone_set($timezone);
			  			$title = stripslashes(htmlentities($row['title'],ENT_QUOTES,"UTF-8"));
			  			$campaign_title = $row['label']=='' ? $title : stripslashes(htmlentities($row['label'],ENT_QUOTES,"UTF-8"));
			  			$recipients = stripslashes($row['recipients']);
			  			$sent = stripslashes($row['sent']);
			  			$opens = stripslashes($row['opens']);
			  			$send_date = stripslashes($row['send_date']);
			  			$scheduled_lists = stripslashes($row['lists']);
			  			$to_send = stripslashes($row['to_send']);
			  			$to_send_lists = stripslashes($row['to_send_lists']);
			  			$from_email = stripslashes($row['from_email']);
			  			$error_stack = stripslashes($row['errors']);
			  			$error_stack_array = explode(',', $error_stack);
			  			$no_of_errors = count($error_stack_array);
			  			
			  			//check if campaign is completely sent
			  			if($sent!='')
			  			{
			  				//check if campaign sending is incomplete
			  				if($recipients>=$to_send)
			  				{
			  					$sent_to_all = true;
			  				}
			  				else
				  			{
					  			if($to_send==NULL)
					  				$sent_to_all = true;
					  			else
					  				$sent_to_all = false;
				  			}
			  			}
			  			else
			  			{
			  				$sent_to_all = false;
			  				
			  				//check if scheduled
				  			if($send_date=='')
				  			{
				  				$label = '<span class="label">'._('Draft').'</span>';
				  				$scheduled_title = _('Define recipients & send');
				  			}
				  			else
				  			{
				  				//get lists name
				  				$j = 1;
				  				$q2 = 'SELECT name FROM lists WHERE id in ('.$scheduled_lists.')';
				  				$r2 = mysqli_query($mysqli, $q2);
				  				if ($r2 && mysqli_num_rows($r2) > 0)
				  				{
				  					$scheduled_list_name = '';
				  				    while($row = mysqli_fetch_array($r2))
				  				    {
				  						$scheduled_list_name .= $row['name'];
				  						if($j < mysqli_num_rows($r2) && $j != mysqli_num_rows($r2)-1)
				  							$scheduled_list_name .= ', ';
				  						else if($j == mysqli_num_rows($r2)-1)
				  							$scheduled_list_name .= ' '._('and').' ';
				  						$j++;
				  				    }  
				  				}
				  				
				  				date_default_timezone_set($timezone);
				  				$send_date_totime = strftime("%a, %b %d, %Y %I:%M%p", $send_date);
				  				$label = '<span class="label label-info">'._('Scheduled').'</span>';
				  				$scheduled_title = _('Scheduled on').' '.$send_date_totime.' ('.$timezone.') '._('to').' ('.$scheduled_list_name.')';
				  			}
			  			}
			  			
			  			if($opens=='')
			  			{
			  				$percentage_opened = 0;
				  			$opens_unique = 0;
			  			}
			  			else
			  			{
				  			$opens_array = explode(',', $opens);
				  			$opens_array2 = array();
				  			foreach($opens_array as $oa)
				  			{
					  			$oa = $oa.',';
					  			$oa = delete_between(':', ',', $oa);
					  			array_push($opens_array2, $oa);
				  			}
				  			$opens_unique = count(array_unique($opens_array2));
				  			$percentage_opened = round($opens_unique/($recipients-get_bounced($id)) * 100, 2);
				  		}
				  		if($recipients==0 || $recipients=='') $percentage_clicked = round(get_click_percentage($id) *100, 2);
			  			else $percentage_clicked = round(get_click_percentage($id)/$recipients *100, 2);
			  			
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
						$today = $sent == '' ? time() : $sent;
						$today = $send_date !='' && $send_date !=0 ? $send_date : $today;
						$currentdaynumber = strftime('%d', $today);
						$currentday = strftime('%A', $today);
						$currentmonthnumber = strftime('%m', $today);
						$currentmonth = strftime('%B', $today);
						$currentyear = strftime('%Y', $today);
						$unconverted_date = array('[currentdaynumber]', '[currentday]', '[currentmonthnumber]', '[currentmonth]', '[currentyear]');
						$converted_date = array($currentdaynumber, $currentday, $currentmonthnumber, $currentmonth, $currentyear);
						$title = str_replace($unconverted_date, $converted_date, $title);
			  			
			  			if(!$sent_to_all)
			  			{
			  				if($sent!='')
				  			{
				  				//if sending incomplete
				  				if($recipients<$to_send)
				  				{
				  					//if CRON has executed the script / sending has started
				  					if($send_date!='0' && $timezone!='0')
				  					{
					  					echo '
					  						<tr id="'.$id.'">
										      <td id="label'.$id.'"><span class="label label-warning">'._('Sending').'</span> <a href="'.get_app_info('path').'/report?i='.get_app_info('app').'&c='.$id.'" title="'._('Currently sending your campaign to').' '.number_format($to_send).' '._('recipients').' ('._('excluding duplicates between lists').')">'.$campaign_title.'</a> ';
										      
										if(!get_app_info('cron_sending')) 
										echo '
									    <span id="separator'.$id.'">|</span> <span id="continue-sending-text"><a href="javascript:void(0)" id="continue-sending-btn-'.$id.'" title="'._('If you think sending has stopped before it was completely sent, click to continue sending to the rest of your recipients').'" data-url="'.get_app_info('path').'/includes/create/send-now.php" data-id="'.$id.'" data-email_list="'.$to_send_lists.'" data-app="'.get_app_info('app').'" data-offset="'.$recipients.'">'._('Resume').'</a></span>
									    ';
										      
										echo ' </td>
										      <td id="progress'.$id.'">'._('Checking..').'</td>
										      <td id="sent-status'.$id.'">'.parse_date($sent, 'long', true).'</td>
										      <td><span class="label">'.$percentage_opened.'%</span> '.number_format($opens_unique).' '._('opened').'</td>
										      <td><span class="label">'.$percentage_clicked.'%</span> '.number_format(get_click_percentage($id)).' '._('clicked').'</td>
										      <td>';
									    
									    if(get_app_info('is_sub_user'))
										{
										    echo '
										    <form action="'.get_app_info('path').'/includes/app/duplicate.php" method="POST" accept-charset="utf-8" class="form-vertical" name="duplicate-form" id="duplicate-form-direct-'.$id.'" style="margin-bottom:0px;">
										    <input type="hidden" name="campaign_id" value="'.$id.'"/>
										    <input type="hidden" name="on-brand" value="'.get_app_info('app').'"/>
										    <a href="javascript:void(0)" id="duplicate-btn-direct-'.$id.'"><i class="icon icon-copy"></i></a>
										    <script type="text/javascript">
										    $("#duplicate-btn-direct-'.$id.'").click(function(){
										    	$("#duplicate-form-direct-'.$id.'").submit();
										    });
										    </script>
										    </form>
										    ';
										}
										else
										    echo '<a href="#duplicate-modal" title="" id="duplicate-btn-'.$id.'" data-toggle="modal" data-cid="'.$id.'" class="duplicate-btn"><i class="icon icon-copy"></i></a>';
								      
								        echo '</td>
										      <td><a href="javascript:void(0)" title="'._('Delete').' '.$campaign_title.'?" id="delete-btn-'.$id.'" class="delete-campaign"><i class="icon icon-trash"></i></a></td>
										      <script type="text/javascript">
										    	$("#delete-btn-'.$id.'").click(function(e){
												e.preventDefault(); 
												c = confirm(\''._('Confirm delete').' '.addslashes($title).'?\');
												if(c)
												{
													$.post("includes/campaigns/delete.php", { campaign_id: '.$id.' },
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
												
												$("#continue-sending-btn-'.$id.'").click(function(e){
													e.preventDefault();
													c = confirm("'._('Only continue if you think that sending has stopped. Resume sending?').'");
													if(c)
													{
														url = $(this).data("url");
														campaign_id = $(this).data("id");
														email_list = $(this).data("email_list");
														app = $(this).data("app");
														offset = $(this).data("offset");
														
														$(this).tooltip("hide");
														$("#continue-sending-text").html("'._('Ok').'");
														
														$.post(url, { campaign_id: campaign_id, email_list: email_list, app: app, offset: offset },
														  function(data) {													  	  
														      if(data)
														      {
														      	//
														      }
														  }
														);
													}
												});
												
												$(document).ready(function() {
								    			
								    				refresh_interval = setInterval(function(){get_sent_count('.$id.')}, 2000);
									    			
									    			function get_sent_count(cid)
									    			{
									    				clearInterval(refresh_interval);
									    				
										    			$.post("includes/app/progress.php", { campaign_id: cid },
														  function(data) {
														      if(data)
														      {
														      	if(data.indexOf("%)") == -1)
														      	{													      		
														      		$("#label'.$id.' span.label").text("'._('Sent').'");
															    	$("#label'.$id.' span.label").removeClass("label-warning");
															    	$("#label'.$id.' span.label").addClass("label-success");
															    	$("#label'.$id.' a").tooltip("hide").attr("data-original-title", "'._('View report for this campaign').'").tooltip("fixTitle");
																    $("#sent-status'.$id.'").text("'.parse_date($sent, 'long', true).'");
																    $("#separator'.$id.'").hide();
																    $("#continue-sending-btn-'.$id.'").hide();
														      	}
														      	else refresh_interval = setInterval(function(){get_sent_count('.$id.')}, 2000);
														      		
														      	$("#progress'.$id.'").html(data);
														      }
														      else
														      {
														      	$("#progress'.$id.'").html("'._('Error retrieving count').'");
														      }
														  }
														);
													}
													
									    		});
												</script>
										    </tr>
					  					';
					  				}
				  					
				  					//CRON have not executed the sending script
				  					else
				  					{
				  					echo '
				  						<tr id="'.$id.'">
									      <td id="label'.$id.'"><span class="label label-warning">'._('Preparing').'</span> <a href="javascript:void(0)" title="'._('Preparing to send your campaign to').' '.number_format($to_send).' '._('recipients')._(' (excluding duplicates between lists), please wait.').'">'.$campaign_title.'</a></td>
									      <td id="progress'.$id.'">'._('Checking..').'</td>
									      <td id="sent-status'.$id.'">'._('Preparing to send').'..</td>
									      <td><span class="label">'.$percentage_opened.'%</span> '.number_format($opens_unique).' '._('opened').'</td>
									      <td><span class="label">'.$percentage_clicked.'%</span> '.number_format(get_click_percentage($id)).' '._('clicked').'</td>
									      <td>
									';
									 
									if(get_app_info('is_sub_user'))
									{
									    echo '
									    <form action="'.get_app_info('path').'/includes/app/duplicate.php" method="POST" accept-charset="utf-8" class="form-vertical" name="duplicate-form" id="duplicate-form-direct-'.$id.'" style="margin-bottom:0px;">
									    <input type="hidden" name="campaign_id" value="'.$id.'"/>
									    <input type="hidden" name="on-brand" value="'.get_app_info('app').'"/>
									    <a href="javascript:void(0)" id="duplicate-btn-direct-'.$id.'"><i class="icon icon-copy"></i></a>
									    <script type="text/javascript">
									    $("#duplicate-btn-direct-'.$id.'").click(function(){
									    	$("#duplicate-form-direct-'.$id.'").submit();
									    });
									    </script>
									    </form>
									    ';
									}
									else
									    echo '<a href="#duplicate-modal" title="" id="duplicate-btn-'.$id.'" data-toggle="modal" data-cid="'.$id.'" class="duplicate-btn"><i class="icon icon-copy"></i></a>';
									      
										 echo '</td>
										      <td><a href="javascript:void(0)" title="'._('Delete').' '.$campaign_title.'?" id="delete-btn-'.$id.'" class="delete-campaign"><i class="icon icon-trash"></i></a></td>
										      <script type="text/javascript">
										    	$("#delete-btn-'.$id.'").click(function(e){
												e.preventDefault(); 
												c = confirm(\''._('Confirm delete').' '.addslashes($title).'?\');
												if(c)
												{
													$.post("includes/campaigns/delete.php", { campaign_id: '.$id.' },
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
												
												$(document).ready(function() {
								    			
								    				refresh_interval = setInterval(function(){get_sent_count('.$id.')}, 2000);
									    			
									    			function get_sent_count(cid)
									    			{
									    				clearInterval(refresh_interval);
									    				
										    			$.post("includes/app/progress.php", { campaign_id: cid },
														  function(data) {
														      if(data)
														      {
														      	if(data.indexOf("%)") != -1)
														      		refresh_interval = setInterval(function(){get_sent_count('.$id.')}, 2000);
														      	
														      	$("#progress'.$id.'").html(data);
														      	
														      	if(data != "0 <span style=\"color:#488846;\">(0%)</span> <img src=\"'.get_app_info('path').'/img/loader.gif\" style=\"width:16px;\"/>")
															    {
															    	window.location = "'.get_app_info('path').'/app?i='.get_app_info('app').'";
															    }
														      }
														      else
														      {
														      	$("#progress'.$id.'").html("'._('Error retrieving count').'");
														      }
														  }
														);
													}
													
									    		});
												</script>
										    </tr>
					  					';
					  					
					  					echo '
					  					<script type="text/javascript">
											time_to_show = 5 * 60 * 1000; // 5 mins
											setTimeout(show_cron_info, time_to_show);
											function show_cron_info()
											{
												//Show cron job may not be working modal window
												$("#cron-job-info").modal("show");
											}
										</script>
					  					';
				  					}
				  				}
				  			}
				  				
				  			else
				  			{
				  				echo '
					  				<tr id="'.$id.'">
								      <td>'.$label.' <a href="'.get_app_info('path').'/send-to?i='.get_app_info('app').'&c='.$id.'" title="'.$scheduled_title.'">'.$campaign_title.'</a> <span style="color:#737373;font-size:12px;">|</span> <a href="'.get_app_info('path').'/edit?i='.get_app_info('app').'&c='.$id.'" title="'._('Edit this campaign').'" style="color:#737373;font-size:12px;"> '._('Edit').'</a></td>
								      <td>-</td>
								      <td>-</td>
								      <td>-</td>
								      <td>-</td>
								      <td>';
								      
								if(get_app_info('is_sub_user'))
								{
								    echo '
								    <form action="'.get_app_info('path').'/includes/app/duplicate.php" method="POST" accept-charset="utf-8" class="form-vertical" name="duplicate-form" id="duplicate-form-direct-'.$id.'" style="margin-bottom:0px;">
								    <input type="hidden" name="campaign_id" value="'.$id.'"/>
								    <input type="hidden" name="on-brand" value="'.get_app_info('app').'"/>
								    <a href="javascript:void(0)" id="duplicate-btn-direct-'.$id.'"><i class="icon icon-copy"></i></a>
								    <script type="text/javascript">
								    $("#duplicate-btn-direct-'.$id.'").click(function(){
								    	$("#duplicate-form-direct-'.$id.'").submit();
								    });
								    </script>
								    </form>
								    ';
								}
								else
								    echo '<a href="#duplicate-modal" title="" id="duplicate-btn-'.$id.'" data-toggle="modal" data-cid="'.$id.'" class="duplicate-btn"><i class="icon icon-copy"></i></a>';
								 
								 echo ' </td>
								      <td><a href="javascript:void(0)" title="'._('Delete').' '.$campaign_title.'?" id="delete-btn-'.$id.'" class="delete-campaign"><i class="icon icon-trash"></i></a></td>
								      <script type="text/javascript">
								    	$("#delete-btn-'.$id.'").click(function(e){
										e.preventDefault(); 
										c = confirm(\''._('Confirm delete').' '.addslashes($title).'?\');
										if(c)
										{
											$.post("includes/campaigns/delete.php", { campaign_id: '.$id.' },
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
								    </tr>
					  			';
					  		}
			  			}
			  			else
			  			{
			  				if($error_stack != '')
				  				$download_errors = ' <span style="color:#737373;font-size:12px;">|</span> <a href="'.get_app_info('path').'/includes/app/download-errors-csv.php?c='.$id.'" title="'._('Download CSV of emails that were not delivered to even after retrying').'" style="color:#737373;font-size:12px;">'.$no_of_errors.' '._('not delivered').'</a>';
				  			else
				  				$download_errors = '';
			  				
				  			echo '
				  				<tr id="'.$id.'">
							      <td><span class="label label-success">'._('Sent').'</span></a> <a href="'.get_app_info('path').'/report?i='.get_app_info('app').'&c='.$id.'" title="'._('View report for this campaign').'">'.$campaign_title.'</a>'.$download_errors.'</td>
							      <td>'.number_format($recipients).'</td>
							      <td>'.parse_date($sent, 'long', true).'</td>
							      <td><span class="label">'.$percentage_opened.'%</span> '.number_format($opens_unique).' '._('opened').'</td>
							      <td><span class="label">'.$percentage_clicked.'%</span> '.number_format(get_click_percentage($id)).' '._('clicked').'</td>
							      <td>';
							      
							if(get_app_info('is_sub_user'))
							{
							    echo '
							    <form action="'.get_app_info('path').'/includes/app/duplicate.php" method="POST" accept-charset="utf-8" class="form-vertical" name="duplicate-form" id="duplicate-form-direct-'.$id.'" style="margin-bottom:0px;">
							    <input type="hidden" name="campaign_id" value="'.$id.'"/>
							    <input type="hidden" name="on-brand" value="'.get_app_info('app').'"/>
							    <a href="javascript:void(0)" id="duplicate-btn-direct-'.$id.'"><i class="icon icon-copy"></i></a>
							    <script type="text/javascript">
							    $("#duplicate-btn-direct-'.$id.'").click(function(){
							    	$("#duplicate-form-direct-'.$id.'").submit();
							    });
							    </script>
							    </form>
							    ';
							}
							else
							    echo '<a href="#duplicate-modal" title="" id="duplicate-btn-'.$id.'" data-toggle="modal" data-cid="'.$id.'" class="duplicate-btn"><i class="icon icon-copy"></i></a>';
						      
						      echo '</td>
							      <td><a href="javascript:void(0)" title="'._('Delete').' '.$campaign_title.'?" id="delete-btn-'.$id.'" class="delete-campaign"><i class="icon icon-trash"></i></a></td>
							      <script type="text/javascript">
							    	$("#delete-btn-'.$id.'").click(function(e){
									e.preventDefault(); 
									c = confirm(\''._('Confirm delete').' '.addslashes($title).'?\');
									if(c)
									{
										$.post("includes/campaigns/delete.php", { campaign_id: '.$id.' },
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
							    </tr>
				  			';
				  			$download_errors = '';
				  		}
			  	    }  
			  	}
			  	else
			  	{
				  	echo '
				  		<tr>
					      <td>'._('There are no campaigns yet').'. <a href="'.get_app_info('path').'/create?i='.get_app_info('app').'" title="">'._('Send one').'</a>!</td>
					      <td></td>
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
		
		<?php pagination($limit); ?>
			
		<div id="duplicate-modal" class="modal hide fade">
		    <div class="modal-header">
		      <button type="button" class="close" data-dismiss="modal">&times;</button>
		      <h3><?php echo _('Duplicate on which brand?');?></h3>
		    </div>
		    <div class="modal-body">
		    	<form action="<?php echo get_app_info('path')?>/includes/app/duplicate.php" method="POST" accept-charset="utf-8" class="form-vertical" name="duplicate-form" id="duplicate-form">
		    	<div class="control-group">
		            <label class="control-label" for="on-brand"><?php echo _('Choose a brand you\'d like to duplicate this campaign on');?>:</label><br/>
		            <div class="controls">
		              <select id="on-brand" name="on-brand">
		              	<?php 
		              		echo '<option value="'.get_app_info('app').'" id="brand-'.get_app_info('app').'">'.get_app_data('app_name').'</option>';
		              	
			              	$q = 'SELECT id, app_name FROM apps WHERE userID = '.get_app_info('main_userID');
			              	$r = mysqli_query($mysqli, $q);
			              	if ($r && mysqli_num_rows($r) > 0)
			              	{
			              	    while($row = mysqli_fetch_array($r))
			              	    {
			              	    	$app_id = $row['id'];
			              			$app_name = $row['app_name'];
			              			
			              			//sub users can only duplicate a campaign in their own brand
			              			if(get_app_info('is_sub_user')!=true)
			              			{
				              			if($app_id != get_app_info('app'))
					              			echo '<option value="'.$app_id.'" id="brand-'.$app_id.'">'.$app_name.'</option>';
				              		}
			              	    }  
			              	}
		              	?>
		              </select>
		              <input type="hidden" name="campaign_id" id="campaign_id" value=""></input>
		            </div>
		          </div>
		          </form>
		    </div>
		    <div class="modal-footer">
		      <a href="#" class="btn btn" data-dismiss="modal"><?php echo _('Cancel');?></a>
		      <a href="javascript:void(0)" class="btn btn-inverse" id="duplicate-btn"><?php echo _('Duplicate');?></a>
		    </div>
	    
		    <script type="text/javascript">
			    $(".duplicate-btn").click(function(){
				    cid = $(this).data("cid");
				    $("#campaign_id").val(cid);
			    });
			    $("#duplicate-btn").click(function(){
				    $("#duplicate-form").submit();
			    });
		    </script>
		</div>
		
		<div id="cron-job-info" class="modal hide fade">
		    <div class="modal-header">
		      <button type="button" class="close" data-dismiss="modal">&times;</button>
		      <h3><span class="icon icon-warning-sign"></span> <?php echo _('Your cron job may not be working');?></h3>
		    </div>
		    <div class="modal-body">
			    <p><?php echo _('Your campaign has been in \'Preparing\' status for more than 5 minutes. This means that your cron job aren\'t executing the \'scheduled.php\' script to start sending. If your campaign does not go into \'Sending\' status in the next 5 minutes, please see this troubleshooting tip');?> → <a href="https://sendy.co/troubleshooting#campaign-stuck-in-preparing-mode" target="_blank" style="text-decoration: underline;">https://sendy.co/troubleshooting#campaign-stuck-in-preparing-mode</a></p>
		    </div>
		    <div class="modal-footer">
		      <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign" style="margin-top: 5px;"></i> <?php echo _('Close');?></a>
		    </div>
		</div>
		
    </div>   
</div>
<?php include('includes/footer.php');?>
