<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/dashboard/main.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/reports?i='.get_app_info('restricted_to_app').'"</script>';
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
    	<h2><?php echo _('Campaign reports');?></h2><br/>
    	
	    <table class="table table-striped responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('Campaign');?></th>
		      <th><?php echo _('Recipients');?></th>
		      <th><?php echo _('Sent');?></th>
		      <th><?php echo _('Unique Opens');?></th>
		      <th><?php echo _('Unique Clicks');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	
		  	<?php 
		  		$limit = 10;
				$total_subs = totals($_GET['i'], 'reports');
				$total_pages = ceil($total_subs/$limit);
				$p = isset($_GET['p']) ? $_GET['p'] : null;
				$offset = $p!=null ? ($p-1) * $limit : 0;
				
			  	$q = 'SELECT * FROM campaigns WHERE userID = '.get_app_info('main_userID').' AND app='.get_app_info('app').' AND sent != "" ORDER BY id DESC LIMIT '.$offset.','.$limit;
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = stripslashes($row['id']);
			  			$title = stripslashes(htmlentities($row['title'],ENT_QUOTES,"UTF-8"));
			  			$campaign_title = $row['label']=='' ? $title : stripslashes(htmlentities($row['label'],ENT_QUOTES,"UTF-8"));
			  			$recipients = stripslashes($row['recipients']);
			  			$sent = stripslashes($row['sent']);
			  			$opens = stripslashes($row['opens']);
			  			$from_email = stripslashes($row['from_email']);
			  			$error_stack = stripslashes($row['errors']);
			  			$error_stack_array = explode(',', $error_stack);
			  			$no_of_errors = count($error_stack_array);
			  			
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
				  			$percentage_opened = round($opens_unique/$recipients * 100, 2);
				  		}
			  			$percentage_clicked = round(get_click_percentage($id)/$recipients *100, 2);
			  			
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
						$today = $sent;
						$currentdaynumber = strftime('%d', $today);
						$currentday = strftime('%A', $today);
						$currentmonthnumber = strftime('%m', $today);
						$currentmonth = strftime('%B', $today);
						$currentyear = strftime('%Y', $today);
						$unconverted_date = array('[currentdaynumber]', '[currentday]', '[currentmonthnumber]', '[currentmonth]', '[currentyear]');
						$converted_date = array($currentdaynumber, $currentday, $currentmonthnumber, $currentmonth, $currentyear);
						$title = str_replace($unconverted_date, $converted_date, $title);
			  			
			  			if($sent==NULL)
			  			{
			  				echo '
				  				<tr id="'.$id.'">
							      <td><span class="label">Draft</span> <a href="'.get_app_info('path').'/send-to?i='.get_app_info('app').'&c='.$id.'" title="'._('Define recipients & send').'">'.$campaign_title.'</a> | <a href="'.get_app_info('path').'/edit?i='.get_app_info('app').'&c='.$id.'" title="'._('Edit this campaign').'"> Edit</a></td>
							      <td>-</td>
							      <td>-</td>
							      <td>-</td>
							      <td>-</td>
							      <td><a href="javascript:void(0)" title="'._('Delete').' '.$campaign_title.'?" id="delete-btn-'.$id.'" class="delete-campaign"><i class="icon icon-trash"></i></a></td>
							      <script type="text/javascript">
							    	$("#delete-btn-'.$id.'").click(function(e){
									e.preventDefault(); 
									c = confirm("'._('Confirm delete').' '.$campaign_title.'?");
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
			  			else
			  			{
			  				if($error_stack != '')
				  				$download_errors = ' | <a href="'.get_app_info('path').'/includes/app/download-errors-csv.php?c='.$id.'" title="'._('Download CSV of emails that were not delivered to even after retrying').'">'.$no_of_errors.' '._('not delivered').'</a>';
				  			else
				  				$download_errors = '';
				  				
				  			echo '
				  				<tr id="'.$id.'">
							      <td><i class="icon icon-bar-chart" style="margin-right:3px;"></i> <a href="'.get_app_info('path').'/report?i='.get_app_info('app').'&c='.$id.'" title="">'.$campaign_title.'</a>'.$download_errors.'</td>
							      <td>'.number_format($recipients).'</td>
							      <td>'.parse_date($sent, 'long', true).'</td>
							      <td><span class="label">'.$percentage_opened.'%</span> '.number_format($opens_unique).' '._('opened').'</td>
							      <td><span class="label">'.$percentage_clicked.'%</span> '.number_format(get_click_percentage($id)).' '._('clicked').'</td>
							      <td><a href="javascript:void(0)" title="'._('Delete').' '.$campaign_title.'?" id="delete-btn-'.$id.'" class="delete-campaign"><i class="icon icon-trash"></i></a></td>
							      <script type="text/javascript">
							    	$("#delete-btn-'.$id.'").click(function(e){
									e.preventDefault(); 
									c = confirm("'._('Confirm delete').' '.$campaign_title.'?");
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
					      <td>'._('There are no reports yet.').' <a href="'.get_app_info('path').'/create?i='.get_app_info('app').'" title="">'._('Create your first campaign').'</a>!</td>
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
		<?php pagination($limit, 'reports'); ?>
    </div>   
</div>
<?php include('includes/footer.php');?>
