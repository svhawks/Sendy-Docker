<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>

<?php 
	//IDs
	$lid = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, $_GET['l']) : exit;
?>

<!-- Validation -->
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#add-custom-field-form").validate({
			rules: {
				c_field: {
					required: true
				}
			},
			messages: {
				c_field: "<?php echo addslashes(_('Please enter a custom field name'));?>"
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
		    	<h2><?php echo _('Custom fields');?></h2>
				<br/>
		    	<p class="well"><?php echo _('List');?>: <a href="<?php echo get_app_info('path');?>/subscribers?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" title=""><span class="label label-info"><?php echo get_lists_data('name', $lid);?></span></a> | <a href="<?php echo get_app_info('path')?>/list?i=<?php echo get_app_info('app');?>" title=""><?php echo _('Back to lists');?></a>
		    	</p><br/>
	    	</div>
	    </div>
	    
	    <div class="row-fluid">
	    	<div class="span12">
				<form method="POST" action="<?php echo get_app_info('path');?>/includes/list/add-custom-field.php" id="add-custom-field-form">
				  <h3><?php echo _('Add a field');?></h3><hr/>
				  <label for="c_field"><?php echo _('Field name');?></label>
				  <?php 
				  	$err = isset($_GET['e']) ? $_GET['e'] : '';
				  	if($err==1):
				  ?>
				  <div class="alert alert-error"><?php echo _('This custom field already exist, please use a unique custom field.');?></div>
				  <?php endif;?>
				  <div id="field-name">
					  <div class="left">
					  	  <input type="text" name="c_field" id="c_field" placeholder="<?php echo _('Name of custom field');?>">

					  </div>
					  <div class="right">
						  <span class="data-type"><?php echo _('Data type');?></span>
						  <select style="width: 100px;" name="c_type" id="c_type">
						  	<option value="Text"><?php echo _('Text');?></option>
						  	<option value="Date"><?php echo _('Date');?></option>
						  </select>
					  </div>
				  </div>
				  <input type="hidden" name="id" value="<?php echo get_app_info('app');?>"/>
				  <input type="hidden" name="list" value="<?php echo $lid;?>"/>
				  <button type="submit" class="btn" id="c_button"><i class="icon icon-plus"></i> <?php echo _('Add custom field');?></button>
				</form>
			</div>
	    </div>
	    
	    <br/><br/>
	    
	    <div class="row-fluid">
		    <div class="span12">
		    	<h3><?php echo _('Existing fields');?></h3><hr/>
		    	<div class="alert alert-error" id="delete-cf-error" style="display:none;"><?php echo _('This custom field is currently used by an autoresponder. In order to delete this custom field, delete the autoresponder associated with this custom field.');?></div>
				<table class="table table-striped responsive">
	              <thead>
	                <tr>
	                  <th><?php echo _('Field name');?></th>
	                  <th><?php echo _('Personalization tag');?></th>
	                  <th><?php echo _('Data type');?></th>
	                  <th><?php echo _('Edit');?></th>
	                  <th><?php echo _('Delete');?></th>
	                </tr>
	              </thead>
	              <tbody>
	                <tr>
	                  <td><?php echo _('Name');?></td>
	                  <td>[Name,fallback=]</td>
	                  <td><?php echo _('Text');?></td>
	                  <td>-</td>
	                  <td>-</td>
	                </tr>
	                <tr>
	                  <td><?php echo _('Email');?></td>
	                  <td>[Email,fallback=]</td>
	                  <td><?php echo _('Text');?></td>
	                  <td>-</td>
	                  <td>-</td>
	                </tr>
	                
	                <?php 
		                $q = 'SELECT custom_fields FROM lists WHERE id = '.$lid;
		                $r = mysqli_query($mysqli, $q);
		                if ($r)
		                {
		                    while($row = mysqli_fetch_array($r))
		                    {
		                		$cfields = $row['custom_fields'];
		                    }  
		                }
		                
		                if($cfields!='')
		                {
			                $cfields_array = explode('%s%', $cfields);
			                
			                $i = 0;
		                    foreach($cfields_array as $f)
		                    {
		                    	$f_array = explode(':', $f);
			                    echo '
			                    	<tr id="'.$i.'">
					                  <td>'.$f_array[0].'</td>
					                  <td>['.str_replace(' ', '', $f_array[0]).',fallback=]</td>
					                  <td>'.($f_array[1]=='Text' ? _('Text') : _('Date')).'</td>
					                  <td><a href="#edit-custom-field" title="" data-id="'.$i.'" data-name="'.$f_array[0].'" data-toggle="modal" class="edit-btn"><i class="icon icon-pencil"></i></a></td>
					                  <td><a href="javascript:void(0)" title="" id="delete-btn-'.$i.'"><i class="icon icon-trash"></i></a></td>
					                  <script type="text/javascript">
								    	$("#delete-btn-'.$i.'").click(function(e){
										e.preventDefault(); 
										c = confirm("'._('All data belonging to this custom field will also be deleted.').' '._('Confirm delete').' '.$f_array[0].'?");
										if(c)
										{
											$.post("includes/list/delete-custom-field.php", { index: '.$i.', list: '.$lid.' },
											  function(data) {
											      if(data)
											      {
											      	if(data=="ares_used")
											      	{
											      		$("#delete-cf-error").slideDown();
											      	}
											      	else
											      	{
												      	$("#'.$i.'").fadeOut();
												      	window.location = "'.get_app_info('path').'/custom-fields?i='.get_app_info('app').'&l='.$lid.'";
												    }
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
			                    $i++;
		                    }
		                }
	                ?>
	                
	              </tbody>
	            </table>
			</div>
	    </div>
    </div>
</div>

<div id="edit-custom-field" class="modal hide fade">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h3><?php echo _('Edit custom field');?></h3>
    </div>
    <div class="modal-body">
    	<form action="<?php echo get_app_info('path')?>/includes/list/edit-custom-field.php" method="POST" id="edit-form">
    		<label for="field_name"><?php echo _('Field name');?></label>
			<input type="text" value="" name="field_name" id="field_name">
			<input type="hidden" value="" name="field_index" id="field_index">
			<input type="hidden" value="<?php echo $lid; ?>" name="lid">
			<input type="hidden" value="<?php echo get_app_info('app'); ?>" name="the_app">
    	</form>
    </div>
    <div class="modal-footer">
      <a href="#" class="btn" data-dismiss="modal"><i class="icon icon-remove"></i> <?php echo _('Close');?></a>
      <a href="javascript:void(0)" class="btn btn-inverse" id="save-btn"><i class="icon icon-ok"></i> <?php echo _('Save');?></a>
    </div>
  </div>
<script type="text/javascript">
	$(".edit-btn").click(function(){
		index = $(this).data("id");
		fname = $(this).data("name");
		$("#field_index").val(index);
		$("#field_name").val(fname);
	});
	$("#save-btn").click(function(){
		$("#edit-form").submit();
	});
</script>

<?php include('includes/footer.php');?>
