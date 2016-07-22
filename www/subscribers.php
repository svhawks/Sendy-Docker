<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/subscribers/main.php');?>
<?php include('includes/helpers/short.php');?>
<?php
	//IDs
	$lid = isset($_GET['l']) && is_numeric($_GET['l']) ? mysqli_real_escape_string($mysqli, $_GET['l']) : exit;
			
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/list?i='.get_app_info('restricted_to_app').'"</script>';
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
	
	//vars
	if(isset($_GET['s'])) $s = trim($_GET['s']);
	else $s = '';
	if(isset($_GET['c'])) $c = $_GET['c'];
	else $c = '';
	if(isset($_GET['p'])) $p = $_GET['p'];
	else $p = '';
	if(isset($_GET['a'])) $a = $_GET['a'];
	else $a = '';
	if(isset($_GET['u'])) $u = $_GET['u'];
	else $u = '';
	if(isset($_GET['b'])) $b = $_GET['b'];
	else $b = '';
	if(isset($_GET['cp'])) $cp = $_GET['cp'];
	else $cp = '';
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
			headers: { 2: { sorter: false}, 4: {sorter: false}, 5: {sorter: false} }	
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
    	<h2><?php echo _('Subscriber lists');?></h2> <br/>

    	<button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/update-list?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>'"><i class="icon-plus-sign"></i> <?php echo _('Add subscribers');?></button> 
    	<button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/delete-from-list?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>'"><i class="icon-minus-sign"></i> <?php echo _('Delete subscribers');?></button> 
    	<button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/unsubscribe-from-list?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>'"><i class="icon-ban-circle"></i> <?php echo _('Mass unsubscribe');?></button> 
    	<?php 
    		//export according to which section user is on
    		if($a=='' && $c=='' && $u=='' && $b=='' && $cp=='')
    		{
	    		$filter = '';
	    		$filter_val = '';
	    		$export_title = _('all subscribers');
    		}
    		else if($a!='')
    		{
	    		$filter = 'a';
	    		$filter_val = $a;
	    		$export_title = _('active subscribers');
    		}
    		else if($c!='')
    		{
	    		$filter = 'c';
	    		$filter_val = $c;
	    		$export_title = _('unconfirmed subscribers');
    		}  
    		else if($u!='')
    		{
	    		$filter = 'u';
	    		$filter_val = $u;
	    		$export_title = _('unsubscribers');
    		} 
    		else if($b!='')
    		{
	    		$filter = 'b';
	    		$filter_val = $b;
	    		$export_title = _('bounced subscribers');
    		}
    		else if($cp!='')
    		{
	    		$filter = 'cp';
	    		$filter_val = $cp;
	    		$export_title = _('subscribers who marked your email as spam');
    		}     	
    	?>
    	<button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/includes/subscribers/export-csv.php?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>&<?php echo $filter.'='.$filter_val;?>'"><i class="icon-download-alt"></i> <?php echo _('Export').' '.$export_title;?></button>
		
		<form class="form-search" action="<?php echo get_app_info('path');?>/subscribers" method="GET" style="float:right;">
    		<input type="hidden" name="i" value="<?php echo get_app_info('app');?>">
    		<input type="hidden" name="l" value="<?php echo $lid;?>">
    		<?php if($a!=''):?>
    		<input type="hidden" name="a" value="<?php echo $a;?>">
    		<?php elseif($c!=''):?>
    		<input type="hidden" name="c" value="<?php echo $c;?>">
    		<?php elseif($u!=''):?>
    		<input type="hidden" name="u" value="<?php echo $u;?>">
    		<?php elseif($b!=''):?>
    		<input type="hidden" name="b" value="<?php echo $b;?>">
    		<?php elseif($cp!=''):?>
    		<input type="hidden" name="cp" value="<?php echo $cp;?>">
    		<?php endif;?>
			<input type="text" class="input-medium search-query" name="s">
			<button type="submit" class="btn"><i class="icon-search"></i> <?php echo _('Search');?></button>
		</form>
    	
    	<br/><br/>
    	<p class="well"><?php echo _('List');?>: <a href="<?php echo get_app_info('path');?>/subscribers?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" title=""><span class="label label-info"><?php echo get_lists_data('name', $lid);?></span></a> | <a href="<?php echo get_app_info('path')?>/list?i=<?php echo get_app_info('app');?>" title=""><?php echo _('Back to lists');?></a>
    	<a href="<?php echo get_app_info('path');?>/edit-list?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" style="float:right;"><i class="icon-wrench"></i> <?php echo _('List settings');?></a>
    	<a href="#subscribeform" style="float:right;margin-right:20px;" data-toggle="modal"><i class="icon-list-alt"></i> <?php echo _('Subscribe form');?></a>
    	
    	<?php 
	    	$q = 'SELECT cron_ares FROM login WHERE id = '.get_app_info('main_userID');
	    	$r = mysqli_query($mysqli, $q);
	    	if ($r)
	    	{
	    	    while($row = mysqli_fetch_array($r)) 
	    	    	$cron_ares = $row['cron_ares'];
	    	}	    	
	    	if($cron_ares):
    	?>
    	<span class="badge" style="float:right;margin:0 20px 0 -15px;"><?php echo get_autoresponder_count();?></span>
    	<a href="<?php echo get_app_info('path');?>/autoresponders-list?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" style="float:right;margin-right:20px;"><i class="icon-time"></i> <?php echo _('Autoresponders');?></a>
    	<?php else:?>
    	<a href="#ares_cron" style="float:right;margin-right:20px;" data-toggle="modal"><i class="icon-time"></i> <?php echo _('Autoresponders');?></a>
    	<?php endif;?>
    	<span class="badge" style="float:right;margin:0 20px 0 -15px;"><?php echo get_custom_fields_count();?></span>
    	<a href="<?php echo get_app_info('path');?>/custom-fields?i=<?php echo get_app_info('app');?>&l=<?php echo $lid;?>" style="float:right;margin-right:20px;"><i class="icon-list"></i> <?php echo _('Custom fields');?></a>
    	</p><br/>
    	
    	<div id="subscribeform" class="modal hide fade">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h3><?php echo _('Subscribe form');?></h3>
            </div>
            <div class="modal-body">
            <p><?php echo _('This is the subscribe form HTML code for');?> <span class="label label-info"><?php echo get_lists_data('name', $lid);?></span>. <?php if(!get_app_info('is_sub_user')): echo _('To sign users up programmatically, use our');?> <a href="https://sendy.co/api" style="text-decoration: underline;" target="_blank"><?php echo _('API');?></a>.<?php endif;?></p>
<pre id="form-code">
&lt;form action=&quot;<?php echo get_app_info('path');?>/subscribe&quot; method=&quot;POST&quot; accept-charset=&quot;utf-8&quot;&gt;
	&lt;label for=&quot;name&quot;&gt;Name&lt;/label&gt;&lt;br/&gt;
	&lt;input type=&quot;text&quot; name=&quot;name&quot; id=&quot;name&quot;/&gt;
	&lt;br/&gt;
	&lt;label for=&quot;email&quot;&gt;Email&lt;/label&gt;&lt;br/&gt;
	&lt;input type=&quot;text&quot; name=&quot;email&quot; id=&quot;email&quot;/&gt;<?php 
	
	$q = 'SELECT custom_fields FROM lists WHERE id = '.$lid;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$custom_fields = $row['custom_fields'];
	    } 
	    if($custom_fields!='')
	    {
	    	$custom_fields_array = explode('%s%', $custom_fields);
	    	foreach($custom_fields_array as $cf)
	    	{
	    		$cf_array = explode(':', $cf);
		    echo '
	&lt;br/&gt;
	&lt;label for=&quot;'.str_replace(' ', '', $cf_array[0]).'&quot;&gt;'.$cf_array[0].'&lt;/label&gt;&lt;br/&gt;
	&lt;input type=&quot;text&quot; name=&quot;'.str_replace(' ', '', $cf_array[0]).'&quot; id=&quot;'.str_replace(' ', '', $cf_array[0]).'&quot;/&gt;';
			}
	    } 
	}
?>

	&lt;br/&gt;
	&lt;input type=&quot;hidden&quot; name=&quot;list&quot; value=&quot;<?php echo short($lid);?>&quot;/&gt;
	&lt;input type=&quot;submit&quot; name=&quot;submit&quot; id=&quot;submit&quot;/&gt;
&lt;/form&gt;</pre>

<script type="text/javascript">
	$(document).ready(function() {
		$("#form-code").click(function(){
			$(this).selectText();
		});
	});
</script>

            </div>
            <div class="modal-footer">
              <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign"></i> <?php echo _('Okay');?></a>
            </div>
          </div>
		
		<?php if($s!=''):?>
		<p><?php echo _('Keyword');?>: <span class="label"><?php echo $s;?></span></p><br/>
		<?php endif;?>
		
		<div class="row-fluid">
    		<div class="span12">
		    	<div id="container" style="min-height:200px;margin:0 0 30px 0;"></div>
	    	</div>
	    </div>
	    
	    <div class="row-fluid">
		    <div class="span12">				
				<ul class="nav nav-tabs">
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo $_GET['i']?>&l=<?php echo $lid?>" id="all"><?php echo _('All');?> <span class="badge badge-info"><?php echo get_totals('', '');?></span></a></li>
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo $_GET['i']?>&l=<?php echo $lid?>&a=1" id="active"><?php echo _('Active');?> <span class="badge badge-success"><?php echo get_totals('a', '');?></span></a></li>
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo $_GET['i']?>&l=<?php echo $lid?>&c=0" id="unconfirmed"><?php echo _('Unconfirmed');?> <span class="badge"><?php echo get_totals('confirmed', 0);?></span></a></li>
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo $_GET['i']?>&l=<?php echo $lid?>&u=1" id="unsubscribed"><?php echo _('Unsubscribed');?> <span class="badge badge-important"><?php echo get_totals('unsubscribed', 1);?></span></a></li>
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo $_GET['i']?>&l=<?php echo $lid?>&b=1" id="bounced"><?php echo _('Bounced');?> <span class="badge badge-inverse"><?php echo get_totals('bounced', 1);?></span></a></li>
				  <li><a href="<?php echo get_app_info('path')?>/subscribers?i=<?php echo $_GET['i']?>&l=<?php echo $lid?>&cp=1" id="complaint"><?php echo _('Marked as spam');?> <span class="badge badge-inverse"><?php echo get_totals('complaint', 1);?></span></a></li>
				</ul>
		    </div>
	    </div>
	    <script type="text/javascript">
			$(document).ready(function() {
				<?php if($a=='' && $c=='' && $u=='' && $b=='' && $cp==''):?>
				$("#all").addClass("tab-active");
				<?php elseif($a!=''):?>
				$("#active").addClass("tab-active");
				<?php elseif($c!=''):?>
				$("#unconfirmed").addClass("tab-active");
				<?php elseif($u!=''):?>
				$("#unsubscribed").addClass("tab-active");
				<?php elseif($b!=''):?>
				$("#bounced").addClass("tab-active");
				<?php elseif($cp!=''):?>
				$("#complaint").addClass("tab-active");
				<?php endif;?>
				
				$("#single").click(function(){
					$("#opt_in").val("0");
				});
				$("#double").click(function(){
					$("#opt_in").val("1");
				});
			});
		</script>
		
	    <table class="table table-striped table-condensed responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('Name');?></th>
		      <th><?php echo _('Email');?></th>
		      <th><?php echo _('Last activity');?></th>
		      <th><?php echo _('Status');?></th>
		      <th><?php echo _('Unsubscribe');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	
		  	<?php 	  			
		  		$limit = 20;
				$total_subs = totals($lid);
				$total_pages = ceil($total_subs/$limit);
				
				if($p!=null)
				{
					$offset = ($p-1) * $limit;
				}
				else
					$offset = 0;
		  		
		  		if($s=='')
		  		{
		  			if($a=='' && $c=='' && $u=='' && $b=='' && $cp=='')
		  				$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
		  			else if($a!='')
			  			$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND confirmed = 1 AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
		  			else if($c!='')
			  			$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND confirmed = '.$c.' ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
			  		else if($u!='')
			  			$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND unsubscribed = '.$u.' AND bounced = 0 ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
			  		else if($b!='')
			  			$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND bounced = '.$b.' ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
			  		else if($cp!='')
			  			$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND complaint = '.$cp.' ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
				}
				else
				{
					if($a=='' && $c=='' && $u=='' && $b=='' && $cp=='')
						$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND (name LIKE "%'.$s.'%" OR email LIKE "%'.$s.'%" OR custom_fields LIKE "%'.$s.'%") ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
					else if($a!='')
						$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND confirmed = 1 AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND (name LIKE "%'.$s.'%" OR email LIKE "%'.$s.'%" OR custom_fields LIKE "%'.$s.'%") ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
					else if($c!='')
						$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND confirmed = '.$c.' AND (name LIKE "%'.$s.'%" OR email LIKE "%'.$s.'%" OR custom_fields LIKE "%'.$s.'%") ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
					else if($u!='')
						$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND unsubscribed = '.$u.' AND bounced = 0 AND (name LIKE "%'.$s.'%" OR email LIKE "%'.$s.'%" OR custom_fields LIKE "%'.$s.'%") ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
					else if($b!='')
						$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND bounced = '.$b.' AND (name LIKE "%'.$s.'%" OR email LIKE "%'.$s.'%" OR custom_fields LIKE "%'.$s.'%") ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
					else if($cp!='')
						$q = 'SELECT * FROM subscribers WHERE list = '.mysqli_real_escape_string($mysqli, $lid).' AND complaint = '.$cp.' AND (name LIKE "%'.$s.'%" OR email LIKE "%'.$s.'%" OR custom_fields LIKE "%'.$s.'%") ORDER BY timestamp DESC LIMIT '.$offset.','.$limit;
				}
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
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
			  			</tr>
			  		';
			  	}
		  	?>
		    
		  </tbody>
		</table>
		
		<?php pagination($limit);?>
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

<?php 
	if(!$cron_ares):
	$server_path_array = explode('subscribers.php', $_SERVER['SCRIPT_FILENAME']);
    $server_path = $server_path_array[0];
?>
<!-- Autoresponder cron instructions -->
<div id="ares_cron" class="modal hide fade">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h3><i class="icon icon-time" style="margin-top: 5px;"></i> <?php echo _('Add a cron job for autoresponders');?></h3>
</div>
<div class="modal-body">
<p><?php echo _('To activate autoresponders, add a');?> <a href="http://en.wikipedia.org/wiki/Cron" target="_blank" style="text-decoration:underline"><?php echo _('cron job');?></a> <?php echo _('with the following command.');?></p>
<h3><?php echo _('Command');?></h3>
<pre id="command">php <?php echo $server_path;?>autoresponders.php > /dev/null 2>&amp;1</pre>
<p><?php echo _('This command needs to be run every minute in order to check the database for any autoresponder emails to send. You\'ll need to set your cron job with the following.');?> <br/><em>(<?php echo _('Note that adding cron jobs vary from hosts to hosts, most offer a UI to add a cron job easily. Check your hosting control panel or consult your host if unsure.');?>)</em>.</p>
<h3><?php echo _('Cron job');?></h3>
<pre id="cronjob">*/1 * * * * php <?php echo $server_path;?>autoresponders.php > /dev/null 2>&amp;1</pre>
<p><?php echo _('Once added, wait one minute. If your cron job is functioning correctly, you\'ll see the autoresponder options instead of this modal window when you click on the "Autoresponders" button.');?></p>
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

<script src="js/highcharts/highcharts.js"></script>
		<script type="text/javascript">
			var month=new Array();
			month[0]="Jan";month[1]="Feb";month[2]="Mar";month[3]="Apr";month[4]="May";month[5]="Jun";month[6]="Jul";month[7]="Aug";month[8]="Sep";month[9]="Oct";month[10]="Nov";month[11]="Dec";
		
			var chart;
			$(document).ready(function() {
				Highcharts.setOptions({
			        colors: ['#468847', '#B94A48', '#333333']
			    });
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'container',
						type: 'areaspline',
						marginBottom: 25
					},
					title: {
						text: false
					},
					subtitle: {
						text: false
					},
					xAxis: {
						categories: [
						<?php 
							$month_array = array();
							$year_array = array();
							$q = 'SELECT MAX(timestamp) FROM subscribers WHERE list = '.$lid;
							$r = mysqli_query($mysqli, $q);
							if ($r && mysqli_num_rows($r) > 0)
							{
							    while($row = mysqli_fetch_array($r))
							    {
							    	$month_max = $row['MAX(timestamp)'];
							    	
							    	if($month_max=='')
								    	$month_max = time();
								    	
							    	$month = strftime('%m', $month_max)-1;
									$year = strftime('%y', $month_max);
							    }  
							}
							
							for($i=0;$i<12;$i++)
							{
								array_push($month_array, $month);
								array_push($year_array, $year);
								$month--;
								if($month<0)
								{
									$month = 11;
									$year--;
								}
							}
							
							$month_array = array_reverse($month_array);
							$year_array = array_reverse($year_array);
							
							for($i=0;$i<12;$i++)
							{
								echo 'month['.$month_array[$i].']'.'+" '.$year_array[$i].'"';
								if($i<11)
									echo ',';
							}
						?>
						]
					},
					yAxis: {
						title: {
							text: false
						},
						plotLines: [{
							value: 0,
							width: 1,
							color: '#808080'
						}]
					},
					plotOptions: {
						line: {
							stacking: 'normal'
						},
						series: {
			                marker: {
			                    enabled: false
			                }
			            }
					},
					tooltip: {
						formatter: function() {
								return '<b>'+ this.series.name +'</b><br/>'+
								this.x +': '+ this.y;
						}
					},
					legend: {
						enabled: false
					},
					credits: {
		                enabled: false
		            },
					series: [{
		                name: 'Subscribers',
		                data: [
		                <?php 
		                	$graph_array = array();
		                	$onemonth = 2629746;
		                	$maxmonth = $month_max;
			                for($i=0;$i<12;$i++)
			                {
				                $q = 'SELECT timestamp FROM subscribers WHERE timestamp <= '.$maxmonth.' AND list = '.$lid.' AND unsubscribed=0 AND bounced = 0 AND complaint = 0 AND confirmed = 1';
				                $r = mysqli_query($mysqli, $q);
				                if ($r && mysqli_num_rows($r) > 0)
				                {
				                    array_push($graph_array, mysqli_num_rows($r));
				                }
				                else
				                	array_push($graph_array, '0');
				                
				                $maxmonth = $maxmonth - $onemonth;
			                }
			                
			                $graph_array = array_reverse($graph_array);
			                
			                for($i=0;$i<12;$i++)
			                {
				                echo $graph_array[$i];
				                
				                if($i<11)
									echo ',';
			                }
			                
		                ?>
		                ]
		            }]
				});
			});
		</script>

<?php include('includes/footer.php');?>
