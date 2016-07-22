<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/ares/main.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/autoresponders-edit?i='.get_app_info('restricted_to_app').'&a='.$_GET['a'].'&ae='.$_GET['ae'].'"</script>';
			exit;
		}
	}
	$edit = true;
	
	$time_condition_number = '';
	$selected_minutes = '';
	$selected_hours = '';
	$selected_days = '';
	$selected_weeks = '';
	$selected_months = '';
	$selected_before = '';
	$selected_after = '';
	$selected_on = '';
?>

<script src="<?php echo get_app_info('path');?>/js/ckeditor/ckeditor.js?7"></script>
<?php if(get_saved_data('wysiwyg')):
	$html_code_msg = '<span class="wysiwyg-note">'._('Switch to HTML editor if the WYSIWYG editor is causing your newsletter to look weird.').'</span>';
?>
<script src="<?php echo get_app_info('path');?>/js/create/editor.js?7"></script>
<?php 
else:
	$html_code_msg = '<span class="wysiwyg-note">'._('Switch to the WYSIWYG editor to use formatting tools.').'</span>';
endif;?>

<!-- Validation -->
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#edit-form").validate({
			rules: {
				subject: {
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
				},
				html: {
					required: true
				},
				time_condition_number: {
					required: true
				}
			},
			messages: {
				subject: "<?php echo addslashes(_('The subject of your email is required'));?>",
				from_name: "<?php echo addslashes(_('\'From name\' is required'));?>",
				from_email: "<?php echo addslashes(_('A valid \'From email\' is required'));?>",
				reply_to: "<?php echo addslashes(_('A valid \'Reply to\' email is required'));?>",
				html: "<?php echo addslashes(_('Your HTML code is required'));?>",
				time_condition_number: "<?php echo addslashes(_('Please specify a number'));?>"
			}
		});
				
		//drip
		$("#time_condition_intervals").change(function(){			
			if($(this).find(":selected").text()=='<?php echo _('immediately');?>')
				$("#time_condition_number").hide();
			else
				$("#time_condition_number").show();
		});
		
		//others
		$("#time_condition_beforeafter").change(function(){			
			if($(this).find(":selected").text()=='<?php echo _('on');?>')
			{
				$("#time_condition_number").hide();
	    			$("#time_condition_intervals").hide();
			}
			else
			{
				$("#time_condition_number").show();
	    			$("#time_condition_intervals").show();
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
	    	<div>
		    	<p class="lead"><?php echo get_app_data('app_name');?></p>
	    	</div>
	    	<h2><?php echo _('Create autoresponder email');?></h2><?php echo _('For');?>: <a href="<?php echo get_app_info('path')?>/autoresponders-list?i=<?php echo get_app_info('app')?>&l=<?php echo get_ares_data('list')?>" title=""><span class="label label-info"><?php echo get_ares_data('name');?></span></a> <span>(<?php echo get_ares_type_name('type');?>)</span> <br/><br/>
    	</div>
    	
    	<form action="<?php echo get_app_info('path')?>/includes/ares/save-autoresponder-email.php?i=<?php echo get_app_info('app')?>&a=<?php echo $_GET['a']?>&ae=<?php echo $_GET['ae']?>&edit=true" method="POST" accept-charset="utf-8" class="form-vertical" id="edit-form" enctype="multipart/form-data">
    	
    	<div class="row-fluid">
    		<div class="span12 well">
    		
    			<?php if(get_ares_data('type')==1):?>
    			
    			<?php 
    				$saved_time_condition = get_saved_data('time_condition');
    				
	    			if($saved_time_condition=='immediately')
	    			{
	    				$selected_immediately = 'selected';
	    			}
	    			else
	    			{
	    				$selected_immediately = '';
	    				
		    			if(strpos($saved_time_condition,_('minutes')) !== false)
		    				$selected_minutes = 'selected';
		    			else if(strpos($saved_time_condition,_('hours')) !== false)
		    				$selected_hours = 'selected';
		    			else if(strpos($saved_time_condition,_('days')) !== false)
		    				$selected_days = 'selected';
		    			else if(strpos($saved_time_condition,_('weeks')) !== false)
		    				$selected_weeks = 'selected';
		    			else if(strpos($saved_time_condition,_('months')) !== false)
		    				$selected_months = 'selected';
		    			
		    			echo '<script type="text/javascript">
	    				$(document).ready(function() {
	    					$("#time_condition_number").show();
	    					$("#time_condition_intervals").show();
						});
	    				</script>';
	    				
	    				$time_condition_number_array = explode(' ', substr($saved_time_condition, 1));
	    				$time_condition_number = $time_condition_number_array[0];
		    		}
    			?>
    			
	    		<i class="icon-time"></i> <?php echo _('Send email');?> <input type="text" name="time_condition_number" id="time_condition_number" value="<?php echo $time_condition_number;?>" style="width: 20px; text-align:center; margin-top: 8px; height: 19px;">
	    		<select name="time_condition_intervals" id="time_condition_intervals" style="width: auto; margin-top: 7px;">
	    			<option value="immediately" <?php echo $selected_immediately;?>><?php echo _('immediately');?></option>
		    		<option value="minutes" <?php echo $selected_minutes;?>><?php echo _('minutes');?></option>
		    		<option value="hours" <?php echo $selected_hours;?>><?php echo _('hours');?></option>
		    		<option value="days" <?php echo $selected_days;?>><?php echo _('days');?></option>
		    		<option value="weeks" <?php echo $selected_weeks;?>><?php echo _('weeks');?></option>
		    		<option value="months" <?php echo $selected_months;?>><?php echo _('months');?></option>

	    		</select>
	    		 <?php echo _('after they subscribe');?>
	    		 
	    		<input type="hidden" name="time_condition_beforeafter" id="time_condition_beforeafter" value="after">
	    		<script type="text/javascript">
    				$("#time_condition_number").hide();
    			</script>
	    		 
	    		<?php else:?>
	    		
	    		<?php 
    				$saved_time_condition = get_saved_data('time_condition');
    				
	    			if($saved_time_condition=='')
	    			{
		    			$selected_on = 'selected';
	    			}
	    			else
	    			{
		    			if(strpos($saved_time_condition,'minutes') !== false)
		    				$selected_minutes = 'selected';
		    			else if(strpos($saved_time_condition,'hours') !== false)
		    				$selected_hours = 'selected';
		    			else if(strpos($saved_time_condition,'days') !== false)
		    				$selected_days = 'selected';
		    			else if(strpos($saved_time_condition,'weeks') !== false)
		    				$selected_weeks = 'selected';
		    			else if(strpos($saved_time_condition,'months') !== false)
		    				$selected_months = 'selected';
		    				
		    			echo '<script type="text/javascript">
	    				$(document).ready(function() {
	    					$("#time_condition_number").show();
	    					$("#time_condition_intervals").show();
						});
	    				</script>';
	    				
	    				$time_condition_number_array = explode(' ', substr($saved_time_condition, 1));
	    				$time_condition_number = $time_condition_number_array[0];
	    				
	    				if(substr($saved_time_condition, 0, 1)=='-')
	    					$selected_before = 'selected';
	    				else if(substr($saved_time_condition, 0, 1)=='+')
	    					$selected_after = 'selected';
		    		}
    			?>
	    		
	    		<i class="icon-time"></i> <?php echo _('Send email');?> <input type="text" name="time_condition_number" id="time_condition_number" value="<?php echo $time_condition_number;?>" style="width: 20px; text-align:center; margin-top: 8px; height: 19px;">
	    		<select name="time_condition_intervals" id="time_condition_intervals" style="width: auto; margin-top: 7px;">
		    		<option value="minutes" <?php echo $selected_minutes;?>><?php echo _('minutes');?></option>
		    		<option value="hours" <?php echo $selected_hours;?>><?php echo _('hours');?></option>
		    		<option value="days" <?php echo $selected_days;?>><?php echo _('days');?></option>
		    		<option value="weeks" <?php echo $selected_weeks;?>><?php echo _('weeks');?></option>
		    		<option value="months" <?php echo $selected_months;?>><?php echo _('months');?></option>
	    		</select>
	    		<select name="time_condition_beforeafter" id="time_condition_beforeafter" style="width: auto; margin-top: 7px;">
	    			<option value="on" <?php echo $selected_on;?>><?php echo _('on');?></option>
	    			<option value="before" <?php echo $selected_before;?>><?php echo _('before');?></option>
	    			<option value="after" <?php echo $selected_after;?>><?php echo _('after');?></option>
	    		</select>
	    		 <?php echo _('each subscriber\'s');?> '<?php echo get_ares_data('custom_field');?>'
	    		 
	    		 <script type="text/javascript">
	    			$("#time_condition_number").hide();
	    			$("#time_condition_intervals").hide();
	    		</script>
	    		 
	    		<?php endif;?>
	    		 
    		</div>
    	</div>
    	
    	<div class="row-fluid">
		    <div class="span3">
			    
		    	<label class="control-label" for="subject"><?php echo _('Subject');?></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="subject" name="subject" placeholder="<?php echo _('Subject of this email');?>" value="<?php echo get_saved_data('title');?>">
		            </div>
		        </div>
		        
		        <label class="control-label" for="from_name"><?php echo _('From name');?></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="from_name" name="from_name" placeholder="<?php echo _('From name');?>" value="<?php echo get_saved_data('from_name');?>">
		            </div>
		        </div>
		        
		        <label class="control-label" for="from_email"><?php echo _('From email');?></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" <?php if(get_app_info('is_sub_user')) echo 'readonly="readonly"';?> id="from_email" name="from_email" placeholder="<?php echo _('From email');?>" value="<?php echo get_saved_data('from_email');?>">
		            </div>
		        </div>
		        
		        <label class="control-label" for="reply_to"><?php echo _('Reply to email');?></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="reply_to" name="reply_to" placeholder="<?php echo _('Reply to email');?>" value="<?php echo get_saved_data('reply_to');?>">
		            </div>
		        </div>
		        
		        <label class="control-label" for="plain"><?php echo _('Plain text');?></label>
	            <div class="control-group">
			    	<div class="controls">
		              <textarea class="input-xlarge" id="plain" name="plain" rows="10" placeholder="<?php echo _('Plain text of this email');?>"><?php echo get_saved_data('plain_text');?></textarea>
		            </div>
		        </div>
		        
		        <label class="control-label" for="query_string"><?php echo _('Query string');?></label>
		        <div class="well">
			        <?php echo _("Optionally append a query string to all links in your email newsletter. A good use case is Google Analytics tracking. Don't include '?' in your query string.");?>
		        </div>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="query_string" name="query_string" placeholder="eg. utm_source=sendy&utm_medium=email&utm_content=email%20newsletter&utm_campaign=email%20newsletter" value="<?php echo get_saved_data('query_string');?>">
		            </div>
		        </div>
		        
		        <?php 
			        $allowed_attachments = get_app_data('allowed_attachments');
			        $allowed_attachments_array = array_map('trim', explode(',', $allowed_attachments));
			        $allowed_attachments_exts = implode(', ', $allowed_attachments_array);
			        if($allowed_attachments!=''):
			    ?>
		        <label class="control-label" for="attachments"><?php echo _('Attachments');?></label>
	            <div class="control-group">
			    	<div class="controls">
			    		<input type="file" id="attachments" name="attachments[]" multiple />
		            </div>
		            <p class="thirtytwo"><i>Allowed file types: <?php echo $allowed_attachments_exts;?></i></p>
		        </div>
		        <?php endif;?>
		        
		        <?php 
			        if (file_exists('uploads/attachments/a'.$_GET['ae']))
					{
						if($handle = opendir('uploads/attachments/a'.$_GET['ae']))
						{
							$i = -1;
						    while (false !== ($file = readdir($handle))) 
						    {
						    	if($file!='.' && $file!='..'):
				    ?>
									<ul id="attachments">
										<li id="attachment<?php echo $i;?>">
											<?php 
												$filen = $file;
												if(strlen($filen)>30) $filen = substr($file, 0, 30).'...';
												echo $filen;
											?> 
											(<?php echo round((filesize('uploads/attachments/a'.$_GET['ae'].'/'.$file)/1000000), 2);?>MB) 
											<a href="<?php echo get_app_info('path');?>/includes/ares/delete-attachment.php" data-filename="<?php echo $file;?>" title="<?php echo _('Delete');?>" id="delete<?php echo $i;?>"><i class="icon icon-trash"></i></a>
											<script type="text/javascript">
												$("#delete<?php echo $i?>").click(function(e){
													e.preventDefault();
													filename = $(this).data("filename");
													ares_id = "<?php echo $_GET['ae']?>";
													url = $(this).attr("href");
													c = confirm('<?php echo _('Confirm delete');?> \"'+filename+'\"?');
													
													if(c)
													{
														$.post(url, { filename: filename, ares_id: ares_id },
														  function(data) {
														      if(data)
														      {
														      	$("#attachment<?php echo $i?>").fadeOut();
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
										</li>
									</ul>
					<?php
								endif;
								
								$i++;
						    }
						
						    closedir($handle);
						}
					}
		        ?>
		        <br/>	
		        
		        <input type="hidden" name="ares_type" value="<?php echo get_ares_data('type');?>">
		        
		        <button type="submit" class="btn btn-inverse"><i class="icon-ok icon-white"></i> <?php echo _('Save autoresponder email');?></button>
		        
		    </div>   
		    <div class="span9">
		    	<p>
			    	<label class="control-label" for="html"><?php echo _('HTML code');?></label>
			    	<div class="btn-group">
			    	<?php if(get_saved_data('wysiwyg')):?>
					  <button class="btn" id="toggle-wysiwyg"><?php echo _('Save and switch to HTML editor');?></button> <?php echo $html_code_msg;?>
					<?php else:?>
					  <button class="btn" id="toggle-wysiwyg"><?php echo _('Save and switch to WYSIWYG editor');?></button> <?php echo $html_code_msg;?>
					<?php endif;?>
					<script type="text/javascript">
						$("#toggle-wysiwyg").click(function(e){
							e.preventDefault();
							
							$('<input>').attr({
							    type: 'hidden',
							    id: 'w_clicked',
							    name: 'w_clicked',
							    value: '1',
							}).appendTo("#edit-form");
							
							$("#subject").rules("remove");
							$("#html").rules("remove");
							if($("#subject").val()=="") $("#subject").val("<?php echo _('Untitled');?>");
							
							$.post('<?php echo get_app_info('path');?>/includes/ares/toggle-wysiwyg.php', { toggle: $("#toggle-wysiwyg").text(), ae: "<?php echo $_GET['ae'];?>" },
							  function(data) {
							      if(data)
							      {
							      	$("#edit-form").submit();
							      }
							      else
							      {
							      	alert("<?php echo _('Sorry, unable to toggle. Please try again later!');?>");
							      }
							  }
							);
						});
					</script>
					</div>
					<br/>
		            <div class="control-group">
				    	<div class="controls">
			              <textarea class="input-xlarge" id="html" name="html" rows="10" placeholder="<?php echo _('Email content');?>"><?php echo get_saved_data('html_text');?></textarea>
			            </div>
			        </div>
			    	<p><?php echo _('Use the following tags in your subject, plain text or HTML code and they\'ll automatically be formatted when your campaign is sent. For web version and unsubscribe tags, you can style them with inline CSS.');?></p><br/>
			    	<div class="row-fluid">
				    	<?php include('includes/helpers/personalization.tags.php');?>
			    	</div>
		    	</p>
		    </div> 
		</div>
		</form>
	</div>
</div>
<?php include('includes/footer.php');?>
