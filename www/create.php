<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/create/main.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/create?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
	}
?>

<script src="<?php echo get_app_info('path');?>/js/ckeditor/ckeditor.js?7"></script>
<script src="<?php echo get_app_info('path');?>/js/create/editor.js?8"></script>

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
				}
			},
			messages: {
				subject: "<?php echo addslashes(_('The subject of your email is required'));?>",
				from_name: "<?php echo addslashes(_('\'From name\' is required'));?>",
				from_email: "<?php echo addslashes(_('A valid \'From email\' is required'));?>",
				reply_to: "<?php echo addslashes(_('A valid \'Reply to\' email is required'));?>",
				html: "<?php echo addslashes(_('Your HTML code is required'));?>"
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
	    	<h2><?php echo _('Create new campaign');?></h2><br/>
    	</div>
    	<div class="row-fluid">
    		<form action="<?php echo get_app_info('path')?>/includes/create/save-campaign.php?i=<?php echo get_app_info('app')?>" method="POST" accept-charset="utf-8" class="form-vertical" id="edit-form" enctype="multipart/form-data">
			    <div class="span3">
				    
				    <div id="campaign-title-field" style="display:none;">
					    <label class="control-label" for="campaign_title"><?php echo _('Campaign title');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="text" class="input-xlarge" id="campaign_title" name="campaign_title" placeholder="<?php echo _('Title of this campaign');?>">
				            </div>
				        </div>
			        </div>
				    
			    	<label class="control-label" for="subject"><?php echo _('Subject');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="subject" name="subject" placeholder="<?php echo _('Subject of this email');?>">
			            </div>
			        </div>
			        
			        <a href="javascript:void(0);" id="set-campaign-title-btn"><?php echo _('Set a title for this campaign?');?></a> <a href="javascript:void(0)" title="<?php echo _('This title (instead of the subject line) will be displayed in your campaigns list and reports. You can also set the title later in the campaign report after sending this campaign.');?>" class="icon icon-info-sign" id="set-campaign-title-btn-info"></a>
					<script type="text/javascript">
					  $(document).ready(function() {
					  	$("#set-campaign-title-btn").click(function(){
					      	$(this).fadeOut();
					      	$("#set-campaign-title-btn-info").fadeOut();
					      	$("#campaign-title-field").slideDown("fast");
					      	$("#campaign_title").focus();
					  	});
					  	$("#campaign_title").blur(function(){
						  	if($(this).val()=='')
						  	{
							  	$("#set-campaign-title-btn").fadeIn();
							  	$("#set-campaign-title-btn-info").fadeIn();
						      	$("#campaign-title-field").slideUp("fast");
					        }
					  	});
					  });
					</script>
			        
			        <label class="control-label" for="from_name" style="clear:both;"><?php echo _('From name');?></label>
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
			        </div><br/>
			        <?php endif;?>
			        
			        <a href="javascript:void(0)" id="campaign-save-only-btn" class="btn"><i class="icon-ok icon-white"></i> <?php echo _('Save');?></a>
			        <button type="submit" class="btn btn-inverse"><i class="icon-arrow-right icon-white"></i> <?php echo _('Save & next');?></button>			        
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
								if($("#subject").val()=="") $("#subject").val("Untitled");
								
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
			 </form>
		</div>
	</div>
</div>
<?php include('includes/footer.php');?>
