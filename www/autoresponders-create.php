<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/ares/main.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/list?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
	}
?>

<script src="<?php echo get_app_info('path');?>/js/ckeditor/ckeditor.js?7"></script>
<script src="<?php echo get_app_info('path');?>/js/create/editor.js?7"></script>

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
	    	<h2><?php echo _('Create autoresponder email');?></h2><?php echo _('For');?>: <a href="<?php echo get_app_info('path')?>/autoresponders-emails?i=<?php echo get_app_info('app')?>&a=<?php echo $_GET['a']?>" title=""><span class="label label-info"><?php echo get_ares_data('name');?></span></a> <span>(<?php echo get_ares_type_name('type');?>)</span> <br/><br/>
    	</div>
    	
    	<form action="<?php echo get_app_info('path')?>/includes/ares/save-autoresponder-email.php?i=<?php echo get_app_info('app')?>&a=<?php echo $_GET['a']?>" method="POST" accept-charset="utf-8" class="form-vertical" id="edit-form" enctype="multipart/form-data">
    	
    	<div class="row-fluid">
    		<div class="span12 well">
    		
    			<?php if(get_ares_data('type')==1):?>
    			
	    		<i class="icon-time"></i> <?php echo _('Send email');?> <input type="text" name="time_condition_number" id="time_condition_number" placeholder="10" style="width: 20px; text-align:center; margin-top: 8px; height: 19px;">
	    		<select name="time_condition_intervals" id="time_condition_intervals" style="width: auto; margin-top: 7px;">
	    			<option value="immediately"><?php echo _('immediately');?></option>
		    		<option value="minutes"><?php echo _('minutes');?></option>
		    		<option value="hours"><?php echo _('hours');?></option>
		    		<option value="days"><?php echo _('days');?></option>
		    		<option value="weeks"><?php echo _('weeks');?></option>
		    		<option value="months"><?php echo _('months');?></option>

	    		</select>
	    		 <?php echo _('after they subscribe');?>
	    		 
	    		<input type="hidden" name="time_condition_beforeafter" id="time_condition_beforeafter" value="after">
	    		<script type="text/javascript">
    				$("#time_condition_number").hide();
    			</script>
	    		 
	    		<?php else:?>
	    		
	    		<i class="icon-time"></i> <?php echo _('Send email');?> <input type="text" name="time_condition_number" id="time_condition_number" placeholder="10" style="width: 20px; text-align:center; margin-top: 8px; height: 19px;">
	    		<select name="time_condition_intervals" id="time_condition_intervals" style="width: auto; margin-top: 7px;">
		    		<option value="minutes"><?php echo _('minutes');?></option>
		    		<option value="hours"><?php echo _('hours');?></option>
		    		<option value="days"><?php echo _('days');?></option>
		    		<option value="weeks"><?php echo _('weeks');?></option>
		    		<option value="months"><?php echo _('months');?></option>
	    		</select>
	    		<select name="time_condition_beforeafter" id="time_condition_beforeafter" style="width: auto; margin-top: 7px;">
	    			<option value="on"><?php echo _('on');?></option>
	    			<option value="before"><?php echo _('before');?></option>
	    			<option value="after"><?php echo _('after');?></option>
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
		              <input type="text" class="input-xlarge" id="subject" name="subject" placeholder="<?php echo _('Subject of this email');?>">
		            </div>
		        </div>
		        
		        <label class="control-label" for="from_name"><?php echo _('From name');?></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="from_name" name="from_name" placeholder="<?php echo _('From name');?>" value="<?php echo get_app_data('from_name');?>">
		            </div>
		        </div>
		        
		        <label class="control-label" for="from_email"><?php echo _('From email');?></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" <?php if(get_app_info('is_sub_user')) echo 'readonly="readonly"';?> id="from_email" name="from_email" placeholder="<?php echo _('From email');?>" value="<?php echo get_app_data('from_email');?>">
		            </div>
		        </div>
		        
		        <label class="control-label" for="reply_to"><?php echo _('Reply to email');?></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="reply_to" name="reply_to" placeholder="<?php echo _('Reply to email');?>" value="<?php echo get_app_data('reply_to');?>">
		            </div>
		        </div>
		        
		        <label class="control-label" for="plain"><?php echo _('Plain text');?></label>
	            <div class="control-group">
			    	<div class="controls">
		              <textarea class="input-xlarge" id="plain" name="plain" rows="10" placeholder="<?php echo _('Plain text of this email');?>"></textarea>
		            </div>
		        </div>
		        
		        <label class="control-label" for="query_string"><?php echo _('Query string');?></label>
		        <div class="well">
			        <?php echo _("Optionally append a query string to all links in your email newsletter. A good use case is Google Analytics tracking. Don't include '?' in your query string.");?>
		        </div>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="query_string" name="query_string" placeholder="eg. utm_source=sendy&utm_medium=email&utm_content=email%20newsletter&utm_campaign=email%20newsletter">
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
		        <br/>
		        <?php endif;?>
		        
		        <input type="hidden" name="ares_type" value="<?php echo get_ares_data('type');?>">
		        
		        <button type="submit" class="btn btn-inverse"><i class="icon-ok icon-white"></i> <?php echo _('Save autoresponder email');?></button>
		        <br/><br/>
		        <a href="<?php echo get_app_info('path');?>/autoresponders-list?i=<?php echo $_GET['i']?>&l=<?php echo get_ares_data('list');?>" title=""><i class="icon icon-chevron-left"></i> <?php echo _('Back to autoresponders list');?></a>
		        
		    </div>   
		    <div class="span9">
		    	<p>
			    	<label class="control-label" for="html"><?php echo _('HTML code');?></label>
			    	<div class="btn-group">
					<button class="btn" id="toggle-wysiwyg"><?php echo _('Save and switch to HTML editor');?></button> 
					<span class="wysiwyg-note"><?php echo _('Switch to HTML editor if the WYSIWYG editor is causing your newsletter to look weird.');?></span>
					<script type="text/javascript">
						$("#toggle-wysiwyg").click(function(e){
							e.preventDefault();
							
							$('<input>').attr({
							    type: 'hidden',
							    id: 'wysiwyg',
							    name: 'wysiwyg',
							    value: '0',
							}).appendTo("#edit-form");
							
							$('<input>').attr({
							    type: 'hidden',
							    id: 'w_clicked',
							    name: 'w_clicked',
							    value: '1',
							}).appendTo("#edit-form");
							
							$("#subject").rules("remove");
							$("#html").rules("remove");
							if($("#subject").val()=="") $("#subject").val("<?php echo _('Untitled');?>");
							
							$("#edit-form").submit();
						});
					</script>
					</div>
					<br/>
		            <div class="control-group">
				    	<div class="controls">
			              <textarea class="input-xlarge" id="html" name="html" rows="10" placeholder="<?php echo _('Email content');?>"></textarea>
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
