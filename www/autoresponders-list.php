<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>

<?php 
	//IDs
	$lid = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, $_GET['l']) : exit;
	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/autoresponders-list?i='.get_app_info('restricted_to_app').'&l='.$lid.'"</script>';
			exit;
		}
	}
	
	if(isset($_GET['e'])) $err = $_GET['e'];
	else $err = '';
?>

<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		<?php 
			$q = 'SELECT id, name, type, custom_field FROM ares WHERE list = '.$lid;
			if(mysqli_num_rows(mysqli_query($mysqli, $q))==0):
		?>
		$("#autoresponder-form").show();
		$("#new-autoresponder-btn").hide();
		<?php else:?>
		$("#autoresponder-form").hide();
		<?php endif;?>
		
		<?php if($err==1):?>
		$("#autoresponder-form").show();
		$("#new-autoresponder-btn").hide();
		<?php endif;?>
		
		$("#new-autoresponder-btn").click(function(){
			$("#autoresponder-form").slideDown();
			$(this).slideUp();
		});
		
		$("#cancel-btn").click(function(){
			$("#autoresponder-form").slideUp();
			$("#new-autoresponder-btn").slideDown();
		});
		
		//Validation
		$("#add-autoresponder-form").validate({
			rules: {
				autoresponder_name: {
					required: true	
				}
			},
			messages: {
				autoresponder_name: "<?php echo addslashes(_('Autoresponder name is required'));?>"
			}
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
		    	<h2><?php echo _('Autoresponders');?></h2>
				<br/>
		    	<p class="well"><?php echo _('List');?>: <a href="<?php echo get_app_info('path');?>/subscribers?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" title=""><span class="label label-info"><?php echo get_lists_data('name', $lid);?></span></a> | <a href="<?php echo get_app_info('path')?>/list?i=<?php echo get_app_info('app');?>" title=""><?php echo _('Back to lists');?></a>
		    	</p><br/>
	    	</div>
	    </div>
	    
	    <?php if($err==1):?>
	    <div class="alert alert-danger"><?php echo _('You can\'t create an autoresponder based on a custom field already in use by another autoresponder.');?></div>
	    <?php endif;?>
	    
		<?php 
	      //check if drip campaign has been created
	  	  $all_used = 0;
		  $q = 'SELECT type FROM ares WHERE list = '.$lid.' AND type = 1';
		  if(mysqli_num_rows(mysqli_query($mysqli, $q)) > 0)
		  	$all_used++;
		  	
		  //check if custom fields has been used up
		  $inuse = 0;
  		  $total_dates = 0;
		  $q = 'SELECT custom_fields FROM lists WHERE id = '.$lid;
		  $r = mysqli_query($mysqli, $q);
		  if (mysqli_num_rows($r) > 0)
		  {
			  while($row = mysqli_fetch_array($r)) $list_custom_fields = stripslashes($row['custom_fields']);
			  $list_custom_fields_array = explode('%s%', $list_custom_fields);
			    foreach($list_custom_fields_array as $cf)
			    {
					  $cf_array = explode(':', $cf);
					  if($cf_array[1]=='Date')
					  {
						  $total_dates++;
						  //check if custom field has been used in any autoresponders before
						  $q2 = 'SELECT custom_field FROM ares WHERE custom_field = "'.$cf_array[0].'" AND list = '.$lid;
						  $r2 = mysqli_query($mysqli, $q2);
						  if (mysqli_num_rows($r2) > 0)
							  $inuse++;
					  }
			    }
		  }
		  //if all custom fields has been used up
		  if($inuse == $total_dates)
			  $all_used++;
	    ?>
	    
	    <?php if($all_used != 2): //if not all autoresponders have been created ?>
	    
	    <p><a href="javascript:void(0)" id="new-autoresponder-btn" class="btn btn-inverse btn-large"><i class="icon-plus icon-white"></i> <?php echo _('Create a new autoresponder');?></a></p>
	    
	    <?php else: ?>
				  
	    <div class="alert alert-info"><?php echo _('All autoresponders have been created.');?></div>
				  
	    <?php endif;?>
	    
	    <div class="row-fluid" id="autoresponder-form">
	    	<div class="span12 well">
				<form method="POST" action="<?php echo get_app_info('path');?>/includes/ares/add-autoresponder.php" id="add-autoresponder-form">
				  <h3><i class="icon icon-plus" style="margin-top: 4px;"></i> <?php echo _('Create a new autoresponder');?></h3><hr/>
				  
				  <?php if($all_used != 2): //if not all autoresponders have been created ?>
				  
				  <div class="row-fluid">
					  <div class="span3">
						  <label for="autoresponder_name"><?php echo _('Autoresponder name');?></label>
						  <input type="text" name="autoresponder_name" id="autoresponder_name" placeholder="Name" style="width: 98%;">
						  <br/><br/>
					  </div>
					  <div class="span9">
						  <p><?php echo _('Autoresponder type');?></p>
						  <?php 
						  	  $drip_created = false;
							  $q = 'SELECT type FROM ares WHERE list = '.$lid.' AND type = 1';
							  if(mysqli_num_rows(mysqli_query($mysqli, $q)) == 0):
						  ?>
						  
						  <label class="radio">
						  	<input type="radio" name="autoresponder_type" id="autoresponder_type1" value="1" checked>
						  	<strong><?php echo _('Drip campaign');?></strong><br/>
						  	<?php echo _('Create a sequence of emails that automatically sends to subscribers after they sign up.');?> 
						  </label><br/>
						  
						  <?php else:
							  $drip_created = true;
						  ?>
						  
						  <div class="alert alert-info"><?php echo _('A drip campaign autoresponder has already been created. You cannot create another drip campaign but you can add more emails to the sequence.');?></div>
						  
						  <?php endif;?>
						  
						  <?php 
						  		$inuse = 0;
						  		$total_dates = 0;
								$q = 'SELECT custom_fields FROM lists WHERE id = '.$lid;
								$r = mysqli_query($mysqli, $q);
								if (mysqli_num_rows($r) > 0)
								{
									while($row = mysqli_fetch_array($r)) $list_custom_fields = stripslashes($row['custom_fields']);
									$list_custom_fields_array = explode('%s%', $list_custom_fields);
									  foreach($list_custom_fields_array as $cf)
									  {
											$cf_array = explode(':', $cf);
											if($cf_array[1]=='Date')
											{
												$total_dates++;
												//check if custom field has been used in any autoresponders before
												$q2 = 'SELECT custom_field FROM ares WHERE custom_field = "'.$cf_array[0].'" AND list = '.$lid;
												$r2 = mysqli_query($mysqli, $q2);
												if (mysqli_num_rows($r2) > 0)
													$inuse++;
											}
									  }
								}
								//if all custom fields has been used up
								if($inuse == $total_dates):
						  ?>
						  
						  <div class="alert alert-info"><?php echo _('There are no available \'date based custom fields\' to create anniversary or date based autoresponders.');?></div>
						  
						  <?php else:?>
							  <?php 
									$q = 'SELECT custom_fields FROM lists WHERE id = '.$lid;
									$r = mysqli_query($mysqli, $q);
									if (mysqli_num_rows($r) > 0):
									while($row = mysqli_fetch_array($r)) $list_custom_fields = stripslashes($row['custom_fields']);
									if($list_custom_fields!=''):
							  ?>
							  <label class="radio" id="ares_type2">
							  	<input type="radio" name="autoresponder_type" id="autoresponder_type2" value="2" <?php if($drip_created) echo 'checked'; ?>>
								<strong><?php echo _('Send annually');?></strong><br/>
								<?php echo _('Create emails that automatically sends annually to subscribers, eg. anniversaries or birthdays.');?>
								<br/><br/>
								<?php echo _('Based on this date');?>:<br/>
								<select id="type2_custom_fields" name="type2_custom_fields">
								  <?php 
									  $list_custom_fields_array = explode('%s%', $list_custom_fields);
									  foreach($list_custom_fields_array as $cf)
									  {
											$cf_array = explode(':', $cf);
											if($cf_array[1]=='Date')
											{
												//check if custom field has been used in any autoresponders before
												$q2 = 'SELECT custom_field FROM ares WHERE custom_field = "'.$cf_array[0].'" AND list = '.$lid;
												$r2 = mysqli_query($mysqli, $q2);
												if (mysqli_num_rows($r2) > 0)
													echo '<option value="'.$cf_array[0].'%s%inuse">'.$cf_array[0].' (in use)</option>';
												else
													echo '<option value="'.$cf_array[0].'">'.$cf_array[0].'</option>';
											}
									  }
								  ?>
								</select>	
								<br/>			
							   </label>
							  <?php endif; endif; ?>
							  
							  <?php 
									$q = 'SELECT custom_fields FROM lists WHERE id = '.$lid;
									$r = mysqli_query($mysqli, $q);
									if (mysqli_num_rows($r) > 0):
									while($row = mysqli_fetch_array($r)) $list_custom_fields = stripslashes($row['custom_fields']);
									if($list_custom_fields!=''):
							  ?>
							  <label class="radio" id="ares_type3" style="margin-top: 18px;">
							  	<input type="radio" name="autoresponder_type" id="autoresponder_type3" value="3">
								<strong><?php echo _('Send on date');?></strong><br/>
								<?php echo _('Create one off emails that sends automatically on a specific date. eg. one off reminders.');?>
								<br/><br/>
								<?php echo _('Based on this date');?>:<br/>
								<select id="type3_custom_fields" name="type3_custom_fields">
								  <?php 
									  $list_custom_fields_array = explode('%s%', $list_custom_fields);
									  foreach($list_custom_fields_array as $cf)
									  {
											$cf_array = explode(':', $cf);
											if($cf_array[1]=='Date')
											{
												//check if custom field has been used in any autoresponders before
												$q2 = 'SELECT custom_field FROM ares WHERE custom_field = "'.$cf_array[0].'" AND list = '.$lid;
												$r2 = mysqli_query($mysqli, $q2);
												if (mysqli_num_rows($r2) > 0)
													echo '<option value="'.$cf_array[0].'%s%inuse">'.$cf_array[0].' (in use)</option>';
												else
													echo '<option value="'.$cf_array[0].'">'.$cf_array[0].'</option>';
											}
									  }
								  ?>
							  
							  
							</select>	
							<br/>				
						   </label>
						  <?php endif; endif; ?>
						  
						  <?php endif; ?>
						  
						  <br/>
						  <input type="hidden" name="id" value="<?php echo $_GET['i'];?>"></input>
						  <input type="hidden" name="list" value="<?php echo $lid;?>"></input>
						  <button class="btn btn-inverse" type="submit"><?php echo _('Save & next');?></button> <?php echo _('or');?> <a href="javascript:void(0)" id="cancel-btn"><?php echo _('cancel');?></a>
					  </div>
				  </div>
				  
				  <?php endif;?>
				</form>
			</div>
	    </div>
	    
	    <br/><br/>
	    
	    <div class="row-fluid">
		    <div class="span12">
		    	<h3><?php echo _('Existing autoresponders');?></h3><hr/>
				<table class="table table-striped responsive">
	              <thead>
	                <tr>
	                  <th><?php echo _('Autoresponder');?></th>
	                  <th><?php echo _('Type');?></th>
	                  <th><?php echo _('Emails sent');?></th>
	                  <th><?php echo _('Delete');?></th>
	                </tr>
	              </thead>
	              <tbody>
	                	<?php 
		                	$q = 'SELECT id, name, type, custom_field FROM ares WHERE list = '.$lid.' ORDER BY type ASC';
		                	$r = mysqli_query($mysqli, $q);
		                	if ($r && mysqli_num_rows($r) > 0)
		                	{
		                	    while($row = mysqli_fetch_array($r))
		                	    {
		                	    	$ares_id = $row['id'];
		                			$ares_name = $row['name'];
		                			$ares_type = $row['type'];
		                			$ares_custom_field = $row['custom_field'];
		                			
		                			$q2 = 'SELECT recipients FROM ares_emails WHERE ares_id = '.$ares_id;
		                			$r2 = mysqli_query($mysqli, $q2);
		                			if ($r2 && mysqli_num_rows($r2) > 0)
		                			{
		                				$recipients = 0;
		                			    while($row = mysqli_fetch_array($r2))
		                			    {
		                					$recipients += $row['recipients'];
		                			    }  
		                			}
		                			else $recipients = _('No emails have been set up');
		                			
		                			switch($ares_type)
		                			{
										case '1':
											$ares_type_name = _('Drip campaign');
									    	break;
									    case '2':
									    	$ares_type_name = _('Send annually').' ('.$ares_custom_field.')';
									    	break;
									    case '3':
									    	$ares_type_name = _('Send on date').' ('.$ares_custom_field.')';
									    	break;
									}
		                			
		                			echo '
		                			<tr id="ares-'.$ares_id.'">
			                			<td><a href="'.get_app_info('path').'/autoresponders-emails?i='.get_app_info('app').'&a='.$ares_id.'">'.$ares_name.'</a></td>
			                			<td>'.$ares_type_name.'</td>
			                			<td>'.$recipients.'</td>
			                			<td><a href="javascript:void(0)" title="" id="delete-'.$ares_id.'" data-id="'.$ares_id.'"><i class="icon-trash"></i></a></td>
			                			<script type="text/javascript">
						            	$("#delete-'.$ares_id.'").click(function(e){
						            		e.preventDefault(); 
											c = confirm("'._('All associated autoresponder emails will be permanently deleted.').' '._('Confirm delete').' \''.$ares_name.'\'?");
											if(c)
											{
								            	$.post("'.get_app_info('path').'/includes/ares/delete-ares.php", { id: $(this).data("id") },
							            		  function(data) {
							            		      if(data)
							            		      {
							            		      	$("#ares-'.$ares_id.'").fadeOut(function(){
							            		      		window.location = "'.get_app_info('path').'/autoresponders-list?i='.get_app_info('app').'&l='.$lid.'";
							            		      	});
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
			                	echo '
			                	<tr>
			                		<td colspan="4">'._('No autoresponders').'</td>
			                	</tr>
			                	';
		                	}
	                	?>                
	              </tbody>
	            </table>
			</div>
	    </div>
    </div>
</div>

<?php include('includes/footer.php');?>
