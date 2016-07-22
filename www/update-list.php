<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>
<?php 
	//IDs
	$lid = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, $_GET['l']) : exit;
	
	if(isset($_GET['e'])) $err = $_GET['e'];
	else $err = '';
	
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/update-list?i='.get_app_info('restricted_to_app').'&l='.$lid.'"</script>';
			exit;
		}
		$q = 'SELECT app FROM lists WHERE id = '.$lid;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$a = $row['app'];
		    }  
		    if($a!=get_app_info('restricted_to_app'))
		    {
			    echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/list?i='.get_app_info('restricted_to_app').'"</script>';
				exit;
		    }
		}
	}
?>

<!-- Validation -->
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#import-update-form").validate({
			rules: {
				csv_file: {
					required: true
				}
			},
			messages: {
				csv_file: "<?php echo addslashes(_('Please upload a CSV file'));?>"
			}
		});
		$("#line-import-form").validate({
			rules: {
				line: {
					required: true
				}
			},
			messages: {
				line: "<?php echo addslashes(_('Please enter at least one combination of name & email'));?>"
			}
		});
	});
</script>

<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span5">
    	<div>
	    	<p class="lead"><?php echo get_app_data('app_name');?></p>
	    	<p><?php echo _('List');?>: <span class="label"><?php echo get_list_data('name');?></span></p>
	    	<br/>
    	</div>
    	<h2><?php echo _('Import via CSV file');?></h2><br/>
	    <form action="<?php echo get_app_info('path')?>/includes/subscribers/import-update.php" method="POST" accept-charset="utf-8" class="form-vertical" enctype="multipart/form-data" id="import-update-form">
	        
	        <?php if($err==1):?>
			<div class="alert alert-error">
			  <button type="button" class="close" data-dismiss="alert">×</button>
			  <strong><?php echo _('Number of columns in CSV does not match CSV format example (as shown below).');?></strong>
			</div>
			<?php elseif($err==3):?>
			<div class="alert alert-error">
			  <button type="button" class="close" data-dismiss="alert">×</button>
			  <p><strong><?php echo _('Please upload a CSV file.');?></strong></p>
			  <p><?php echo _('If you are uploading a huge CSV file, Try increasing the following values in your server\'s php.ini to larger numbers. Contact your hosting support if you\'re unsure how to do this.');?></p>
			  <ul>
			  	<li><code>upload_max_filesize</code></li>
				<li><code>post_max_size</code></li>
				<li><code>memory_limit</code></li>
				<li><code>max_input_time</code></li>
				<li><code>max_execution_time</code> <?php echo _('(set to 0 so that execution won\'t time out indefinitely)');?></li>
			  </ul>
			  <p><?php echo _('Alternatively, try splitting your huge CSV file into several smaller sized CSV files and import them one after another.');?></p>
			</div>
			<?php elseif($err==4):?>
			<div class="alert alert-error">
			  <button type="button" class="close" data-dismiss="alert">×</button>
			  <strong><?php echo _('Could not upload file. Please make sure permissions in /uploads/ folder is set to 777. Then remove the /csvs/ folder in the /uploads/ folder and try again.');?></strong>
			</div>
			<?php endif;?>
			
	        <label class="control-label" for="csv_file"><em><?php echo _('CSV format');?>:</em></label>
        	<ul>
        		<li><?php echo _('Format your CSV the same way as the example below (without the first title row)');?></li>
        		<li><?php echo _('The number of columns in your CSV should be the same as the example below');?></li>
        		<li><?php echo _('If you want to import more than just name & email');?>, <a href="<?php echo get_app_info('path');?>/custom-fields?i=<?php echo $_GET['i'];?>&l=<?php echo $lid;?>" title="" style="text-decoration:underline;"><?php echo _('create custom fields first');?></a></li>
        	</ul>
	        <table class="table table-bordered table-striped table-condensed" style="width: 300px;">
			  <tbody>
			  	<tr>
				  	<th><?php echo _('Name');?></th>
				  	<th><?php echo _('Email');?></th>
				  	<?php 
					      $q = 'SELECT custom_fields FROM lists WHERE id = '.$lid;
					      $r = mysqli_query($mysqli, $q);
					      if ($r)
					      {
					          while($row = mysqli_fetch_array($r))
					          {
						          $custom_field = $row['custom_fields'];
					          }
					          if($custom_field!='')
					          {
						          $custom_field_array = explode('%s%', $custom_field);
						          foreach($custom_field_array as $cf)
						          {
						          	  $cf_array = explode(':', $cf);
							          echo '<th>'.$cf_array[0].'</th>';
						          }
						      }
					      }
				      ?>
			  	</tr>
			    <tr>
			      <td>Philip Morris</td>
			      <td>pmorris@gmail.com</td>
			      <?php 
					      $q = 'SELECT custom_fields FROM lists WHERE id = '.$lid;
					      $r = mysqli_query($mysqli, $q);
					      if ($r)
					      {
					          while($row = mysqli_fetch_array($r))
					          {
						          $custom_field = $row['custom_fields'];
					          }
					          if($custom_field!='')
					          {
						          $custom_field_array = explode('%s%', $custom_field);
						          foreach($custom_field_array as $cf)
						          {
							          echo '<td></td>';
						          }
						      }
					      }
				      ?>
			    </tr>
			    <tr>
			      <td>Jane Webster</td>
			      <td>jwebster@gmail.com</td>
			      <?php 
					      $q = 'SELECT custom_fields FROM lists WHERE id = '.$lid;
					      $r = mysqli_query($mysqli, $q);
					      if ($r)
					      {
					          while($row = mysqli_fetch_array($r))
					          {
						          $custom_field = $row['custom_fields'];
					          }
					          if($custom_field!='')
					          {
						          $custom_field_array = explode('%s%', $custom_field);
						          foreach($custom_field_array as $cf)
						          {
							          echo '<td></td>';
						          }
						      }
					      }
				      ?>
			    </tr>
			  </tbody>
			</table>
	        <div class="control-group">
		    	<div class="controls">
	              <input type="file" class="input-xlarge" id="csv_file" name="csv_file">
	            </div>
	        </div>
	        
	        <?php 
		        //check if cron is set up
		    	$q = 'SELECT cron_csv FROM login WHERE id = '.get_app_info('main_userID');
		    	$r = mysqli_query($mysqli, $q);
		    	if ($r)
		    	    while($row = mysqli_fetch_array($r)) 
		    	    	$cron = $row['cron_csv'];
		    	
		    	//get server path
		    	$server_path_array = explode('update-list.php', $_SERVER['SCRIPT_FILENAME']);
			    $server_path = $server_path_array[0];
	        ?>
	        
	        <input type="hidden" name="list_id" value="<?php echo $lid;?>">
	        <input type="hidden" name="app" value="<?php echo $_GET['i'];?>">
	        <input type="hidden" name="cron" value="<?php echo $cron;?>">
	        
	        <br/>
	        <button type="submit" class="btn btn-inverse"><i class="icon icon-double-angle-down"></i> <?php echo _('Import');?></button>
	        <br/><br/>
	        
	        <?php if(!$cron && !get_app_info('is_sub_user')): ?>
	        <p class="alert alert-info" style="width: 70%;"><strong><?php echo _('Note');?>:</strong> <?php echo _('If your CSV is huge and your server constantly timeout');?>, <a href="#cron-instructions" data-toggle="modal" style="text-decoration:underline;"><?php echo _('setup a cron job');?></a>. <?php echo _('By setting up a cron job, your CSV will continue to import without needing your window to be opened and timeouts will automatically be handled as well.');?></p>
	        
	         <div id="cron-instructions" class="modal hide fade">
	            <div class="modal-header">
	              <button type="button" class="close" data-dismiss="modal">&times;</button>
	              <h3><i class="icon icon-time" style="margin-top: 5px;"></i> <?php echo _('Add a cron job');?></h3>
	            </div>
	            <div class="modal-body">
	            <p><?php echo _('To import large CSVs more reliably and have Sendy handle server timeouts, add a');?> <a href="http://en.wikipedia.org/wiki/Cron" target="_blank" style="text-decoration:underline"><?php echo _('cron job');?></a> <?php echo _('with the following command.');?></p>
	            <h3><?php echo _('Command');?></h3>
	            <pre id="command">php <?php echo $server_path;?>import-csv.php > /dev/null 2>&amp;1</pre>
	            <p><?php echo _('This command can be run at any time interval you want. You\'ll need to set your cron job with the following.');?><br/><em><?php echo _('(Note that adding cron jobs vary from hosts to hosts, most offer a UI to add a cron job easily. Check your hosting control panel or consult your host if unsure.)');?></em>.</p>
	            <h3><?php echo _('Cron job');?></h3>
	            <pre id="cronjob">*/1 * * * * php <?php echo $server_path;?>import-csv.php > /dev/null 2>&amp;1</pre>
	            <p><?php echo _('The above cron job runs every 1 minute. You can set it at 5 minutes (eg. */5) or any interval you want. The shorter the interval, the faster your CSV will start to import. Once added, wait for cron job to start running. If your cron job is functioning correctly, the blue informational message will disappear and future CSV imports will be done via cron.');?></p>
	            </div>
	            <div class="modal-footer">
	              <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign"></i> <?php echo _('Okay');?></a>
	            </div>
	        </div>
	        <script type="text/javascript">
			$(document).ready(function() {
				$("#command, #cronjob").click(function(){
					$(this).selectText();
				});
			});
			</script>
	        <?php endif;?>
	          
	    </form>
	    
	    <br/>
	    
	    <h2><?php echo _('Add name and email per line');?></h2><br/>
	    <form action="<?php echo get_app_info('path')?>/includes/subscribers/line-update.php" method="POST" accept-charset="utf-8" class="form-vertical" enctype="multipart/form-data" id="line-import-form">
	        
	        <?php if($err==2):?>
			<div class="alert alert-error">
			  <button type="button" class="close" data-dismiss="alert">×</button>
			  <strong><?php echo _('Sorry, we didn\'t receive any input.');?></strong>
			</div>
			<?php endif;?>
			
	        <label class="control-label" for="line"><?php echo _('Name and email');?><br/><em style="color:#A1A1A1">(<?php echo _('to import more than just name and email, import via CSV');?>)</em></label>
            <div class="control-group">
		    	<div class="controls">
	              <textarea class="input-xlarge" id="line" name="line" rows="10" placeholder="Eg. Herman Miller,hermanmiller@gmail.com"></textarea>
	            </div>
	        </div>
	        
	        <input type="hidden" name="list_id" value="<?php echo $lid;?>">
	        <input type="hidden" name="app" value="<?php echo $_GET['i'];?>">
	        
	        <br/>
	        <button type="submit" class="btn btn-inverse"><i class="icon icon-double-angle-down"></i> <?php echo _('Add');?></button>
	    </form>
    </div>  
</div>
<?php include('includes/footer.php');?>
