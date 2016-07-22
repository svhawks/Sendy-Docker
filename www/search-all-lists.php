<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>
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
	
	//vars
	if(isset($_GET['s'])) $s = trim($_GET['s']);
?>
<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span10">
    	<div>
	    	<p class="lead"><?php echo get_app_data('app_name');?></p>
    	</div>
    	<h2><?php echo _('Search all lists');?></h2> <br/>
		
		<div class="well">
			<p><?php echo _('Keyword');?>: <span class="label label-info"><?php echo $s;?></span> | Results <span class="label label-info" id="results-count"></span> <a href="<?php echo get_app_info('path')?>/list?i=<?php echo get_app_info('app');?>" title="" style="float:right;"><i class="icon icon-double-angle-left"></i> <?php echo _('Back to lists');?></a></p>
		</div>
		
	    <table class="table table-striped table-condensed responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('Name');?></th>
		      <th><?php echo _('Email');?></th>
		      <th><?php echo _('List');?></th>
		      <th><?php echo _('Last activity');?></th>
		      <th><?php echo _('Status');?></th>
		      <th><?php echo _('Unsubscribe');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>		  	
		  	<?php		  		
		  		$q = 'SELECT subscribers.id, subscribers.name, subscribers.email, subscribers.unsubscribed, subscribers.bounced, subscribers.complaint, subscribers.confirmed, subscribers.list, subscribers.timestamp FROM subscribers, lists WHERE (subscribers.name LIKE "%'.$s.'%" OR subscribers.email LIKE "%'.$s.'%" OR subscribers.custom_fields LIKE "%'.$s.'%") AND lists.app = '.get_app_info('app').' AND lists.id = subscribers.list ORDER BY subscribers.timestamp DESC';
			  	$r = mysqli_query($mysqli, $q);
			  	$number_of_results = mysqli_num_rows($r);
			  	echo '
			  	<script type="text/javascript">
			  	$(document).ready(function() {
			  		$("#results-count").text("'.$number_of_results.'");
			  	});
			    </script>
			  	';
			  	if ($r && $number_of_results > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = $row['id'];
			  			$name = stripslashes($row['name']);
			  			$email = stripslashes($row['email']);
			  			$unsubscribed = $row['unsubscribed'];
			  			$bounced = $row['bounced'];
			  			$complaint = $row['complaint'];
			  			$confirmed = $row['confirmed'];
			  			$list = $row['list'];
			  			$timestamp = parse_date($row['timestamp'], 'short', true);
			  			
			  			if($unsubscribed==0)
			  				$unsubscribed = '<span class="label label-success">'._('Subscribed').'</span>';
			  			else if($unsubscribed==1)
			  				$unsubscribed = '<span class="label label-important">'._('Unsubscribed').'</span>';
			  			if($bounced==1)
				  			$unsubscribed = '<span class="label label-inverse">'._('Bounced').'</span>';
				  		if($complaint==1)
				  			$unsubscribed = '<span class="label label-inverse">'._('Marked as spam').'</span>';
				  		if($confirmed==0)
			  				$unsubscribed = '<span class="label">'._('Unconfirmed').'</span>';
				  			
				  		if($name=='')
				  			$name = '['._('No name').']';
			  			
			  			echo '
			  			
			  			<tr id="'.$id.'">
			  			  <td><a href="#subscriber-info" data-id="'.$id.'" data-toggle="modal" class="subscriber-info">'.$name.'</a></td>
					      <td><a href="#subscriber-info" data-id="'.$id.'" data-toggle="modal" class="subscriber-info">'.$email.'</a></td>
					      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$list.'">'.get_list_name($list).'</a></td>
					      <td>'.$timestamp.'</td>
					      <td id="unsubscribe-label-'.$id.'">'.$unsubscribed.'</td>
					      <td>
					    ';
					    
					    if($row['unsubscribed']==0)
							$action_icon = '
								<a href="javascript:void(0)" title="'._('Unsubscribe').' '.$email.'" data-action'.$id.'="unsubscribe" id="unsubscribe-btn-'.$id.'">
									<i class="icon icon-ban-circle"></i>
								</a>
								';
						else if($row['unsubscribed']==1)
							$action_icon = '
								<a href="javascript:void(0)" title="'._('Resubscribe').' '.$email.'" data-action'.$id.'="resubscribe" id="unsubscribe-btn-'.$id.'">
									<i class="icon icon-ok"></i>
								</a>
							';
						if($row['bounced']==1 || $row['complaint']==1)
							$action_icon = '
								-
							';
						if($row['confirmed']==0)
							$action_icon = '
								<a href="javascript:void(0)" title="'._('Confirm').' '.$email.'" data-action'.$id.'="confirm" id="unsubscribe-btn-'.$id.'">
									<i class="icon icon-ok"></i>
								</a>
							';
						
						echo $action_icon;
					    
					    echo'
					      </td>
					      <td><a href="javascript:void(0)" title="'._('Delete').' '.$email.'?" id="delete-btn-'.$id.'" class="delete-subscriber"><i class="icon icon-trash"></i></a></td>
					      <script type="text/javascript">
					    	$("#delete-btn-'.$id.'").click(function(e){
								e.preventDefault(); 
								c = confirm("'._('Confirm delete').' '.$email.'?");
								if(c)
								{
									$.post("includes/subscribers/delete.php", { subscriber_id: '.$id.' },
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
							$("#unsubscribe-btn-'.$id.'").click(function(e){
								e.preventDefault(); 
								action = $("#unsubscribe-btn-'.$id.'").data("action'.$id.'");
								$.post("includes/subscribers/unsubscribe.php", { subscriber_id: '.$id.', action: action},
								  function(data) {
								      if(data)
								      {
								      	if($("#unsubscribe-label-'.$id.'").text()=="'._('Subscribed').'")
								      	{
								      		$("#unsubscribe-btn-'.$id.'").html("<li class=\'icon icon-ok\'></li>");
								      		$("#unsubscribe-btn-'.$id.'").data("action'.$id.'", "resubscribe");
									      	$("#unsubscribe-label-'.$id.'").html("<span class=\'label label-important\'>'._('Unsubscribed').'</span>");
									    }
									    else
									    {
									    	$("#unsubscribe-btn-'.$id.'").html("<li class=\'icon icon-ban-circle\'></li>");
								      		$("#unsubscribe-btn-'.$id.'").data("action'.$id.'", "unsubscribe");
									      	$("#unsubscribe-label-'.$id.'").html("<span class=\'label label-success\'>'._('Subscribed').'</span>");
									    }
									    if($("#unsubscribe-label-'.$id.'").text()=="'._('Unconfirmed').'")
									    {
									    	$("#unsubscribe-btn-'.$id.'").html("<li class=\'icon icon-ban-circle\'></li>");
								      		$("#unsubscribe-btn-'.$id.'").data("action'.$id.'", "confirm");
									      	$("#unsubscribe-label-'.$id.'").html("<span class=\'label label-success\'>'._('Subscribed').'</span>");
									    }
								      }
								      else
								      {
								      	alert("'._('Sorry, unable to unsubscribe. Please try again later!').'");
								      }
								  }
								);
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
			  				<td>'._('No subscribers found.').'</td>
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

<!-- Subscriber info card -->
<div id="subscriber-info" class="modal hide fade">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h3><?php echo _('Subscriber info');?></h3>
    </div>
    <div class="modal-body">
	    <p id="subscriber-text"></p>
    </div>
    <div class="modal-footer">
      <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign" style="margin-top: 5px;"></i> <?php echo _('Close');?></a>
    </div>
  </div>
<script type="text/javascript">
	$(".subscriber-info").click(function(){
		s_id = $(this).data("id");
		$("#subscriber-text").html("<?php echo _('Fetching');?>..");
		
		$.post("<?php echo get_app_info('path');?>/includes/subscribers/subscriber-info.php", { id: s_id, app:<?php echo get_app_info('app');?> },
		  function(data) {
		      if(data)
		      {
		      	$("#subscriber-text").html(data);
		      }
		      else
		      {
		      	$("#subscriber-text").html("<?php echo _('Oops, there was an error getting the subscriber\'s info. Please try again later.');?>");
		      }
		  }
		);
	});
</script>

<?php include('includes/footer.php');?>
