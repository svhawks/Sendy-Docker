<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../reports/main.php');?>
<?php 
	//POST variables
	$id = mysqli_real_escape_string($mysqli, $_POST['id']);
	$app = mysqli_real_escape_string($mysqli, $_POST['app']);

	//get subscriber data
	$q = 'SELECT * FROM subscribers WHERE id = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$name = $row['name'];
			$email = $row['email'];
			$list_id = $row['list'];
			$unsubscribed = $row['unsubscribed'];
			$bounced = $row['bounced'];
			$complaint = $row['complaint'];
			$confirmed = $row['confirmed'];
			$last_campaign = $row['last_campaign'];
			if($unsubscribed==0)
  				$status = '<span class="label label-success">'._('Subscribed').'</span>';
  			else if($unsubscribed==1)
  				$status = '<span class="label label-important">'._('Unsubscribed').'</span>';
  			if($bounced==1)
	  			$status = '<span class="label label-inverse">'._('Bounced').'</span>';
	  		if($complaint==1)
	  			$status = '<span class="label label-inverse">'._('Marked as spam').'</span>';
  			if($confirmed!=1)
	  			$status = '<span class="label">'._('Unconfirmed').'</span>';
	  			
	  		//check if name is set
	  		if($name=='')
	  			$name = '<em>'._('not set').'</em>';
	    }  
	}
	
	//get list name
	$q = 'SELECT name FROM lists WHERE id = '.$list_id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$list = $row['name'];
	    }  
	}
?>
<div class="row-fluid">
	<div class="span2">
		<img src="<?php echo get_gravatar($email);?>" class="gravatar"/>
	</div>
    <div class="span5">	    	
    	<strong><?php echo _('Name');?>: </strong>
    	<span id="edit-name"><?php echo $name;?></span>
    	<input type="text" name="name" id="name" value="<?php echo strip_tags($name);?>" style="width: 70%; margin-top: 5px; display:none;"/><br/>
    	<script type="text/javascript">
    		$(document).ready(function() {
    			$("#edit-name").mouseover(function(){
	    			$(this).css("text-decoration", "underline");
    			});
    			$("#edit-name").mouseout(function(){
	    			$(this).css("text-decoration", "none");
    			});
    			$("#edit-name").click(function(){
		    		$(this).hide();
		    		$("#name").show();
		    		$("#name").focus();
	    		});
	    		$("#name").blur(function(){
		    		$(this).hide();
		    		$("#edit-name").show();
	    		});
	    		$("#name").keypress(function(e){
				    if(e.which == 13)
				    {
				    	update_name();
				    }
				});
				function update_name()
				{
					$("#edit-name").show(0, function(){
						if($("#name").val() != $(this).text())
						$.post("<?php echo get_app_info('path')?>/includes/subscribers/edit.php", { sid: <?php echo $id;?>, name: $("#name").val() },
						  function(data) {
						      if(data != 1)
						      {
						      	$("#edit-name").text($("#edit-name").text());
						      	alert("<?php echo _('Sorry, unable to save. Please try again later!');?>");
						      }
						      else
						      {
							      $("#edit-name").text($("#name").val());
						      }
						  }
						);
					});
		    		$("#name").hide();
				}
    		});
    	</script>
    	
		<strong><?php echo _('Email');?>: </strong>
		<span id="edit-email"><?php echo $email;?></span>
		<input type="text" name="email" id="email" value="<?php echo $email;?>" style="width: 70%; margin-top: 5px; display:none;"/><br/>
		<script type="text/javascript">
    		$(document).ready(function() {
    			$("#edit-email").click(function(){
		    		$(this).hide();
		    		$("#email").show();
		    		$("#email").focus();
	    		});
	    		$("#edit-email").mouseover(function(){
	    			$(this).css("text-decoration", "underline");
    			});
    			$("#edit-email").mouseout(function(){
	    			$(this).css("text-decoration", "none");
    			});
	    		$("#email").blur(function(){
		    		$(this).hide();
		    		$("#edit-email").show();
	    		});
	    		$("#email").keypress(function(e){
				    if(e.which == 13)
				    {
				    	update_email();
				    }
				});
				function update_email()
				{			
					$("#edit-email").show(0, function(){
						if($("#email").val() != $(this).text())
						$.post("<?php echo get_app_info('path')?>/includes/subscribers/edit.php", { sid: <?php echo $id;?>, email: $("#email").val() },
						  function(data) {
						      if(data != 1)
						      {
						      	 $("#edit-email").text($("#edit-email").text());
						      	 alert(data);
						      }
						      else
						      {
								  $("#edit-email").text($("#email").val());
						      }
						  }
						);
						$("#email").hide();
					});
				}
    		});
    	</script>
		
		<?php 
			//get custom fields and values
			$q = 'SELECT lists.custom_fields, subscribers.custom_fields AS custom_values FROM lists, subscribers WHERE subscribers.id = '.$id.' AND lists.id = subscribers.list';
			$r = mysqli_query($mysqli, $q);
			if ($r)
			{
			    while($row = mysqli_fetch_array($r))
			    {
					$custom_fields = $row['custom_fields'];
					$custom_values = $row['custom_values'];
			    }
			    
			    //if there's custom fields for this list, show custom fields and their values
			    if($custom_fields!='')
			    {
				    $custom_fields_array = explode('%s%', $custom_fields);
				    
				    $i = 0;
				    foreach($custom_fields_array as $cf)
				    {
					    $cf_array = explode(':', $cf);
					    $cf_field = $cf_array[0];
					    $cf_format = $cf_array[1];
					    $cf_value_array = explode('%s%', $custom_values);
					    $cf_field_without_dash = str_replace('-', '_dash_', $cf_field);
					    $cf_field_without_dash = str_replace('?', '_question_', $cf_field_without_dash);
					    
					    //format date if format is date
					    if($cf_format=='Date' && $cf_value_array[$i]!='')
					    {
						    $cf_value_array[$i] = strftime("%b %d, %Y", $cf_value_array[$i]);
					    }
					    
					    //check if value is empty
					    if($cf_value_array[$i]=='')
					    	$cf_value_array[$i] = '<em>not set</em>';
					    
					    echo '<strong>'.$cf_field.': </strong>';
					    
					    $cf_field_without_dash = str_replace(" ", "", $cf_field_without_dash);
					    
					    //Check if custom field name begins with a number, if so, append 'ncf_' to custom field name
					    $cf_field_without_dash_first_number = is_numeric(substr($cf_field_without_dash, 0, 1)) ? true : false;
					    if($cf_field_without_dash_first_number) $cf_field_without_dash = 'ncf_'.$cf_field_without_dash;
					    
					    echo'
					    <span id="edit-'.$cf_field_without_dash.'">'.$cf_value_array[$i].'</span>
					    <input type="text" name="'.$cf_field_without_dash.'" id="'.$cf_field_without_dash.'" value="'.strip_tags($cf_value_array[$i]).'" style="width: 70%; margin-top: 5px; display:none;"/>
					    <br/>';
					    ?>
					    
					    <script type="text/javascript">
				    		$(document).ready(function() {
				    			$("#edit-<?php echo $cf_field_without_dash;?>").click(function(){
						    		$(this).hide();
						    		$("#<?php echo $cf_field_without_dash;?>").show();
						    		$("#<?php echo $cf_field_without_dash;?>").focus();
					    		});
					    		$("#edit-<?php echo $cf_field_without_dash;?>").mouseover(function(){
					    			$(this).css("text-decoration", "underline");
				    			});
				    			$("#edit-<?php echo $cf_field_without_dash;?>").mouseout(function(){
					    			$(this).css("text-decoration", "none");
				    			});
					    		$("#<?php echo $cf_field_without_dash;?>").blur(function(){
						    		$(this).hide();
						    		$("#edit-<?php echo $cf_field_without_dash;?>").show();
					    		});
					    		$("#<?php echo $cf_field_without_dash;?>").keypress(function(e){
								    if(e.which == 13)
								    {
								    	update_<?php echo $cf_field_without_dash;?>();
								    }
								});
								function update_<?php echo $cf_field_without_dash;?>()
								{			
									$("#edit-<?php echo $cf_field_without_dash;?>").show(0, function(){
										if($("#<?php echo $cf_field_without_dash;?>").val() != $(this).text())
										$.post("<?php echo get_app_info('path')?>/includes/subscribers/edit.php", { sid: <?php echo $id;?>, <?php echo $cf_field_without_dash;?>: $("#<?php echo $cf_field_without_dash;?>").val() },
										  function(data) {
										      if(data != 1)
										      {
										      	 $("#edit-<?php echo $cf_field_without_dash;?>").text($("#edit-<?php echo $cf_field_without_dash;?>").text());
										      	 alert(data);
										      }
										      else
										      {
												  $("#edit-<?php echo $cf_field_without_dash;?>").text($("#<?php echo $cf_field_without_dash;?>").val());
										      }
										  }
										);
										$("#<?php echo $cf_field_without_dash;?>").hide();
									});
								}
				    		});
				    	</script>
					    
					    <?php
					    
					    $i++;
				    }
				}
			}
		?>
    </div>
    <div class="span5">
		<strong><?php echo _('List');?>: <span class="label label-info"><?php echo $list;?></span></strong><br/>
		<strong><?php echo _('Status');?>: <?php echo $status;?></strong>
    </div>
</div>
<hr>
<h4><?php echo _('Campaign activity');?></h4><br/>
<table class="table table-striped table-condensed responsive">
  <thead>
    <tr>
      <th><?php echo _('Campaign');?></th>
      <th><?php echo _('Opens');?></th>
      <th><?php echo _('Clicks');?></th>
      <th><?php echo _('Country');?></th>
    </tr>
  </thead>
  <tbody>
  	<?php 
	  	$q = 'SELECT * FROM campaigns WHERE userID = '.get_app_info('main_userID').' AND app = '.$app.' ORDER BY sent DESC LIMIT 20';
	  	$r = mysqli_query($mysqli, $q);
	  	if ($r && mysqli_num_rows($r) > 0)
	  	{
	  		$has_activity = false;
	  	    while($row = mysqli_fetch_array($r))
	  	    {
	  	    	//opens and country data
	  	    	$country = '';
	  	    	$s_id = '';
	  	    	$open_count = 0;
	  	    	$campaign_id = $row['id'];
	  			$title = $row['title'];
	  			$opens = $row['opens'];
	  			$opens_array = explode(',', $opens);
	  			$links_clicked = '';
	  			for($z=0;$z<count($opens_array);$z++)
	  	    	{
		  			$subscriber_id = explode(':', $opens_array[$z]);
		  			if($subscriber_id[0]==$id)
		  			{
		  				$s_id = $subscriber_id[0];
		  				$country = $subscriber_id[1];
		  				$has_activity = true;
		  				$open_count += 1;
		  			}
		  		}
		  		
		  		//get links data
		  		$q2 = 'SELECT link, clicks FROM links WHERE campaign_id = '.$campaign_id;
		  		$r2 = mysqli_query($mysqli, $q2);
		  		if ($r2 && mysqli_num_rows($r2) > 0)
		  		{
		  			$click_count = 0;
		  			$link_array = array();
		  		    while($row = mysqli_fetch_array($r2))
		  		    {
		  				$clicks = $row['clicks'];
		  				$link = $row['link'];
		  				$clicks_array = explode(',', $clicks);
		  				if(in_array($id, $clicks_array))
		  				{
		  					$click_count++;
		  					array_push($link_array, $link);
		  				}
		  		    }  
		  		    for($y=0;$y<count($link_array);$y++)
		  		    {		  		    	
		  		    	$links_clicked .= strlen($link_array[$y])>32 ? substr($link_array[$y], 0, 32).'..<br/>' : $link_array[$y].'<br/>';
		  		    }
		  		}
		  		
		  		if($s_id!='')
		  		{
		  			$cty = country_code_to_country($country);
		  			if($cty=='')
		  				$cty = _('Not detected');
			  		echo '
				  	<tr>
				      <td><a href="'.get_app_info('path').'/report?i='.$app.'&c='.$campaign_id.'" title="'._('View report for').' '.$title.'">'.$title.'</a></td>
				      <td>'.$open_count.'</td>
				    ';
				    
				    if(count($link_array)==0)
				    	echo '<td>0</td>';
				    else
					    echo '<td><a href="javascript:void(0)" title="'._('Links clicked').'" data-content="'.$links_clicked.'" id="click-links-'.$campaign_id.'" style="text-decoration:underline;">'.$click_count.'</a></td>';
				    
				    echo '
				      <td>'.$cty.'</td>
				      <script type="text/javascript">
							$(document).ready(function() {
								$("#click-links-'.$campaign_id.'").popover({placement:"left"})
							});	
						</script>
				    </tr>
				  	';
		  		}
	  	    }  
	  	    if(!$has_activity)
	  	    {
		  	    echo '
			  	<tr>
			      <td>'._('No activity.').'</td>
			      <td></td>
			      <td></td>
			      <td></td>
			    </tr>
			  	';
	  	    }
	  	}
	  	else
	  	{
		  	echo '
		  	<tr>
		      <td>'._('No activity.').'</td>
		      <td></td>
		      <td></td>
		      <td></td>
		    </tr>
		  	';
	  	}
  	?>
  </tbody>
</table>