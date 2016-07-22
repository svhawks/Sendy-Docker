<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/create/main.php');?>
<?php 
	//IDs
	$cid = isset($_GET['c']) && is_numeric($_GET['c']) ? mysqli_real_escape_string($mysqli, $_GET['c']) : exit;
	$aid = isset($_GET['i']) && is_numeric($_GET['i']) ? mysqli_real_escape_string($mysqli, $_GET['i']) : exit;
	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/app?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
		$q = 'SELECT app FROM campaigns WHERE id = '.$cid;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$a = $row['app'];
		    }  
		    if($a!=get_app_info('restricted_to_app'))
		    {
			    echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/app?i='.get_app_info('restricted_to_app').'"</script>';
				exit;
		    }
		}
	}
?>

<script src="<?php echo get_app_info('path');?>/js/ckeditor/ckeditor.js?7"></script>

<?php 
	$edit = true;
	
	if(get_saved_data('wysiwyg')):
	$html_code_msg = '<span class="wysiwyg-note">'._('Switch to HTML editor if the WYSIWYG editor is causing your newsletter to look weird.').'</span>';
?>
<script src="<?php echo get_app_info('path');?>/js/create/editor.js?8"></script>
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
		    <div class="span10">
			    <div>
			    	<p class="lead"><?php echo get_app_data('app_name');?></p>
		    	</div>
		    	<h2><?php echo _('Edit campaign');?></h2><br/>
		    </div>
	    </div>
	    
	    <div class="row-fluid">
	    	<form action="<?php echo get_app_info('path')?>/includes/create/save-campaign.php?i=<?php echo get_app_info('app')?>&c=<?php echo $cid;?>&edit=true" method="POST" accept-charset="utf-8" class="form-vertical" id="edit-form" enctype="multipart/form-data">
			    <div class="span3">
				    
				    <div id="campaign-title-field" style="<?php if(get_saved_data('label')=='') echo 'display:none;'?>">
					    <label class="control-label" for="campaign_title"><?php echo _('Campaign title');?></label>
				    	<div class="control-group">
					    	<div class="controls">
				              <input type="text" class="input-xlarge" id="campaign_title" name="campaign_title" placeholder="<?php echo _('Title of this campaign');?>" value="<?php echo htmlspecialchars(get_saved_data('label'));?>">
				            </div>
				        </div>
			        </div>
			    
			    	<label class="control-label" for="subject"><?php echo _('Subject');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="subject" name="subject" placeholder="<?php echo _('Subject of this email');?>" value="<?php echo htmlspecialchars(get_saved_data('title'));?>">
			            </div>
			        </div>
			        
			        <?php if(get_saved_data('label')==''):?>
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
					<?php endif;?>
			        
			        <label class="control-label" for="from_name" style="clear:both;"><?php echo _('From name');?></label>
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
				        if (file_exists('uploads/attachments/'.$cid))
						{
							if($handle = opendir('uploads/attachments/'.$cid))
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
												(<?php echo round((filesize('uploads/attachments/'.$cid.'/'.$file)/1000000), 2);?>MB) 
												<a href="<?php echo get_app_info('path');?>/includes/create/delete-attachment.php" data-filename="<?php echo $file;?>" title="<?php echo _('Delete');?>" id="delete<?php echo $i;?>"><i class="icon icon-trash"></i></a>
												<script type="text/javascript">
													$("#delete<?php echo $i?>").click(function(e){
														e.preventDefault();
														filename = $(this).data("filename");
														campaign_id = "<?php echo $cid?>";
														url = $(this).attr("href");
														c = confirm('<?php echo _('Confirm delete');?> \"'+filename+'\"?');
														
														if(c)
														{
															$.post(url, { filename: filename, campaign_id: campaign_id },
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
			        
			        <a href="javascript:void(0)" id="campaign-save-only-btn" class="btn"><i class="icon-ok icon-white"></i> <?php echo _('Save');?></a>
			        <button type="submit" class="btn btn-inverse"><i class="icon-arrow-right icon-white"></i> <?php echo _('Save & next');?></button>			        
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
								
								$.post('<?php echo get_app_info('path');?>/includes/create/toggle-wysiwyg.php', { toggle: $("#toggle-wysiwyg").text(), i:"<?php echo get_app_info('app');?>", c: "<?php echo $cid;?>" },
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
		    </form>
	    </div>
	</div>
</div>
<?php include('includes/footer.php');?>
