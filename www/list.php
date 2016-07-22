<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/list/main.php');?>
<?php include('includes/helpers/short.php');?>
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
			headers: { 0: { sorter: false}, 5: {sorter: false}, 6: {sorter: false} }	
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
    	<h2><?php echo _('Subscriber lists');?></h2><br/>
    	
    	<div style="clear:both;">
	    	<button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/new-list?i=<?php echo get_app_info('app');?>'"><i class="icon-plus-sign"></i> <?php echo _('Add a new list');?></button>
	    	
	    	<form class="form-search" action="<?php echo get_app_info('path');?>/search-all-lists" method="GET" style="float:right;">
	    		<input type="hidden" name="i" value="<?php echo get_app_info('app');?>">
				<input type="text" class="input-medium search-query" name="s" style="width: 200px;">
				<button type="submit" class="btn"><i class="icon-search"></i> <?php echo _('Search all lists');?></button>
			</form>
		</div>
		
		<br/>
    	
	    <table class="table table-striped responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('ID');?></th>
		      <th><?php echo _('List');?></th>
		      <th><?php echo _('Active');?></th>
		      <th><?php echo _('Unsubscribed');?></th>
		      <th><?php echo _('Bounced');?></th>
		      <th><?php echo _('Edit');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	
		  	<!-- Auto select encrypted listID -->
		  	<script type="text/javascript">
		  		$(document).ready(function() {
					$(".encrypted-list-id").mouseover(function(){
						$(this).selectText();
					});
				});
			</script>
		  	
		  	<?php 
			  	$q = 'SELECT id, name FROM lists WHERE app = '.get_app_info('app').' AND userID = '.get_app_info('main_userID').' ORDER BY name ASC';
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = $row['id'];
			  			$name = stripslashes($row['name']);
			  			$subscribers_count = get_subscribers_count($id);
			  			$unsubscribers_count = get_unsubscribers_count($id);
			  			$bounces_count = get_bounced_count($id);
			  			if(strlen(short($id))>5) $listid = substr(short($id), 0, 5).'..';
			  			else $listid = short($id);
			  				
			  			echo '
			  			
			  			<tr id="'.$id.'">
			  			  <td><span class="label" id="list'.$id.'">'.$listid.'</span><span class="label encrypted-list-id" id="list'.$id.'-encrypted" style="display:none;">'.short($id).'</span></td>
					      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$id.'" title="">'.$name.'</a></td>
					      <td id="progress'.$id.'">'.$subscribers_count.'</td>
					      <td><span class="label">'.get_unsubscribers_percentage($subscribers_count, $unsubscribers_count).'%</span> '.$unsubscribers_count.' '._('users').'</td>
					      <td><span class="label">'.get_bounced_percentage($bounces_count, $subscribers_count).'%</span> '.$bounces_count.' '._('users').'</td>
					      <td><a href="edit-list?i='.get_app_info('app').'&l='.$id.'" title=""><i class="icon icon-pencil"></i></a></td>
					      <td><a href="javascript:void(0)" title="'._('Delete').' '.$name.'?" id="delete-btn-'.$id.'" class="delete-list"><i class="icon icon-trash"></i></a></td>
					      <script type="text/javascript">
					    	$("#delete-btn-'.$id.'").click(function(e){
							e.preventDefault(); 
							c = confirm("'._('All subscribers, custom fields and autoresponders in this list will also be permanently deleted. Confirm delete').' '.$name.'?");
							if(c)
							{
								$.post("includes/list/delete.php", { list_id: '.$id.' },
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
							$("#list'.$id.'").mouseover(function(){
								$("#list'.$id.'-encrypted").show();
								$(this).hide();
							});
							$("#list'.$id.'-encrypted").mouseout(function(){
								$(this).hide();
								$("#list'.$id.'").show();
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
				  			<td>'._('No list yet.').' <a href="'.get_app_info('path').'/new-list?i='.get_app_info('app').'" title="">'._('Add one').'</a>!</td>
				  			<td></td>
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
    </div>   
</div>
<?php include('includes/footer.php');?>
