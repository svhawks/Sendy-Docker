<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/templates/main.php');?>
<?php 
	//IDs
	$aid = isset($_GET['i']) && is_numeric($_GET['i']) ? mysqli_real_escape_string($mysqli, $_GET['i']) : exit;
	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/templates?i='.get_app_info('restricted_to_app').'"</script>';
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
				template_name: {
					required: true	
				},
				html: {
					required: true
				}
			},
			messages: {
				template_name: "<?php echo addslashes(_('The name of this template is required'));?>",
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
		    <div class="span10">
			    <div>
			    	<p class="lead"><?php echo get_app_data('app_name');?></p>
		    	</div>
		    	<h2><?php echo _('Create template');?></h2><br/>
		    </div>
	    </div>
	    
	    <div class="row-fluid">
		    		    
	    	<form action="<?php echo get_app_info('path')?>/includes/templates/save-template.php?i=<?php echo get_app_info('app')?>" method="POST" accept-charset="utf-8" class="form-vertical" id="edit-form">
		    	
		    	<div class="span3">
			        <label class="control-label" for="template_name"><?php echo _('Template name');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="template_name" name="template_name" placeholder="<?php echo _('Name of this template');?>">
			            </div>
			        </div>
			        
			        <button type="submit" class="btn btn-inverse" id="save-button"><i class="icon-ok icon-white"></i> <?php echo _('Save template');?></button>
			    </div>
		    				    
			    <div class="span9">
			    	<p>
				    	<label class="control-label" for="html"><?php echo _('HTML code');?></label>
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
