<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/ares-reports/main.php');?>
<?php include('includes/helpers/short.php');?>
<?php 
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/autoresponders-report.php?i='.get_app_info('restricted_to_app').'&a='.$_GET['a'].'&ae='.$_GET['ae'].'"</script>';
			exit;
		}
	}
?>
<?php 
	$q = 'SELECT * FROM ares_emails WHERE id = '.mysqli_real_escape_string($mysqli, $_GET['ae']);
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$id = stripslashes($row['id']);
  			$title = stripslashes($row['title']);
  			$recipients = stripslashes($row['recipients']);
  			$opens = stripslashes($row['opens']);
  			$opens_all = '';
  			$opens_array = array();
  			
  			if($opens=='')
  			{
  				$percentage_opened = 0;
	  			$opens_unique = 0;
  			}
  			else
  			{
	  			$opens_array = explode(',', $opens);
	  			$opens_all = count($opens_array);
	  			$opens_unique = count(array_unique($opens_array));
	  			$percentage_opened = round($opens_unique/($recipients-get_bounced()) * 100, 2);
	  		}
	  		if($recipients==0 || $recipients=='') 
	  		{
	  			$click_per = round(get_click_percentage($_GET['ae']) *100, 4);
	  			$unsubscribe_per = round(get_unsubscribes() *100, 4);
	  			$bounce_percentage = round(get_bounced() * 100, 2);
		  		$complaint_percentage = round(get_complaints() * 100, 2);
	  		}
	  		else 
	  		{
	  			$click_per = round(get_click_percentage($_GET['ae'])/($recipients-get_bounced()) *100, 4);
	  			$unsubscribe_per = round(get_unsubscribes()/($recipients-get_bounced()) *100, 4);
	  			$bounce_percentage = round((get_bounced()/$recipients) * 100, 2);
		  		$complaint_percentage = round((get_complaints()/$recipients) * 100, 2);
	  		}
	  		
	  		if($opens_all=='')
	  			$opens_all = '0';
	    }  
	}
?>
<script type="text/javascript" src="<?php echo get_app_info('path')?>/js/fancybox/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path')?>/js/fancybox/jquery.fancybox.css" media="screen" />
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {		
		//iframe preview
		$(".iframe-preview").click(function(e) {
			e.preventDefault();
			
			$.fancybox.open({
				href : $(this).attr("href"),
				type : 'iframe',
				padding : 0
			});
		});
	});
</script>
<script src="js/highcharts/highcharts.js"></script>
<script type="text/javascript">
	$(document).ready(function() {

	var chart;
    $(document).ready(function() {
    	
    	Highcharts.setOptions({
	        colors: ['#1F1F1F', '#1F1F1F', '#B94A48', '#3A87AD', '#F89406', '#468847', '#999999']
	    });
    	
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container',
                type: 'bar',
                height: 300
            },
            title: {
                text: false
            },
            subtitle: {
                text: false
            },
            xAxis: {
                categories: ['<?php echo _('Activity');?>'],
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: false
                }
            },
            legend: {
	            borderColor: '#E0E0E0'
	        },
            tooltip: {
                formatter: function() {
                    return ''+
                        this.series.name +': '+ this.y;
                }
            },
            plotOptions: {
                bar: {
                	borderWidth: 0,
                	shadow: false,
                	groupPadding: 0,
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            credits: {
                enabled: false
            },
            series: [
            {
                name: '<?php echo _('Marked as spam');?>',
                data: [<?php echo get_complaints();?>]
            },
            {
                name: '<?php echo _('Bounced');?>',
                data: [<?php echo get_bounced();?>]
            },
            {
                name: '<?php echo _('Unsubscribed');?>',
                data: [<?php echo get_unsubscribes();?>]
            },
            {
                name: '<?php echo _('Clicked');?>',
                data: [<?php echo get_click_percentage($_GET['ae']);?>]
            },
            {
                name: '<?php echo _('Unopened');?>',
                data: [<?php echo $recipients - $opens_unique; ?>]
            },
            {
                name: '<?php echo _('Opened');?>',
                data: [<?php echo $opens_unique;?>]
            },
            {
                name: '<?php echo _('Recipients');?>',
                data: [<?php echo $recipients;?>]
            }
            ],
            exporting: { enabled: false }
        });
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
    	<h2><?php echo _('Autoresponder report');?></h2><br/>
    	
    	<h3><?php echo get_saved_data('title');?> <a href="<?php echo get_app_info('path');?>/w/<?php echo short($id);?>/a" title="<?php echo _('View the web version');?>" class="iframe-preview"><span class="icon-eye-open"></span></a></h3>
    	<?php echo _('For');?>: <span class="label label-info"><?php echo get_ares_data('name');?></span> <span>(<?php echo get_ares_type_name('type');?>)</span>, <em><?php echo _('sent to');?> <span class="label"><?php echo number_format(get_saved_data('recipients'));?> <?php echo _('subscribers');?></span></em>
    	
    	<div class="row-fluid">
    		<div class="span4">
		    	<div id="countries-container" style="min-height:300px;margin:20px 0 0 0;"></div>
	    	</div>
    		<div class="span8">
		    	<div id="container" style="margin-top: 50px;"></div>
		    </div>
	    </div>
    	
    	<br/>
    	<div class="row-fluid">
	    	<div class="span6">
	    		<div class="well">
			    	<h3><span class="badge badge-success" style="font-size:16px;"><?php echo $percentage_opened;?>%</span> <?php echo _('opened');?> <span class="label"><?php echo $opens_unique;?> <?php echo _('unique');?> / <?php echo _('opened');?> <?php echo $opens_all;?> <?php echo _('times');?></span></h3><br/>
			    	<h3><span class="badge badge-warning" style="font-size:16px;"><?php echo $recipients - $opens_unique;?></span> <?php echo _('not opened');?></h3><br/>
			    	<h3><span class="badge badge-info" style="font-size:16px;"><?php echo $click_per;?>%</span> <?php echo _('clicked a link');?> <span class="label"><?php echo get_click_percentage($_GET['ae']);?> <?php echo _('clicked');?></span></h3>
			    </div>
	    	</div>
	    	
	    	<div class="span6">
	    		<div class="well">
			    	<h3><span class="badge badge-important" style="font-size:16px;"><?php echo $unsubscribe_per;?>%</span> <?php echo _('unsubscribed');?> <span class="label"><?php echo get_unsubscribes();?> <?php echo _('unsubscribed');?></span></h3><br/>
			    	<h3><span class="badge badge-inverse" style="font-size:16px;"><?php echo $bounce_percentage;?>%</span> <?php echo _('bounced');?> <span class="label"><?php echo get_bounced();?> <?php echo _('bounced');?></span></h3><br/>
			    	<h3><span class="badge badge-inverse" style="font-size:16px;"><?php echo $complaint_percentage;?>%</span> <?php echo _('marked as spam');?> <span class="label"><?php echo get_complaints();?> <?php echo _('marked as spam');?></span></h3>
			    </div>
	    	</div>
	    </div>
	    
	    <!-- Link activity -->
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
		    	<h2 class="report-titles"><?php echo _('Link activity');?></h2>
		    	<a href="<?php echo get_app_info('path');?>/includes/ares-reports/export-csv.php?c=<?php echo $id?>&a=clicks" title="<?php echo _('Export subscribers who clicked');?>" class="report-export"><i class="icon icon-download-alt"></i></a>
	    	</div>
	    </div>
	    <br/>
	    <div class="row-fluid">
	    	<table class="table table-striped table-condensed responsive">
			  <thead>
			    <tr>
			      <th><?php echo _('Link (URL)');?></th>
			      <th><?php echo _('Unique');?></th>
			      <th><?php echo _('Total');?></th>
			    </tr>
			  </thead>
			  <tbody>
			  	
			  	<?php 
				  	$q = 'SELECT * FROM links WHERE ares_emails_id = '.mysqli_real_escape_string($mysqli, $_GET['ae']);
				  	$r = mysqli_query($mysqli, $q);
				  	if ($r && mysqli_num_rows($r) > 0)
				  	{
				  	    while($row = mysqli_fetch_array($r))
				  	    {
				  			$link = stripslashes($row['link']);
				  			$clicks = stripslashes($row['clicks']);
				  			
				  			if($clicks==NULL)
				  			{
				  				$unique_clicks = '0';
				  				$total_clicks = '0';
				  			}
				  			else
				  			{
					  			$total_clicks_array = explode(',', $clicks);
					  			$total_clicks = count($total_clicks_array);
					  			$unique_clicks = count(array_unique($total_clicks_array));
					  		}
				  			
				  			echo '
				  			
				  			<tr>
						      <td><a href="'.$link.'" target="_blank">'.$link.'</a></td>
						      <td>'.$unique_clicks.'</td>
						      <td>'.$total_clicks.'</td>
						    </tr>
				  			
				  			';
				  	    }  
				  	}
				  	else
				  	{
					  	echo '
				  			
			  			<tr>
					      <td>'._('There are no links for this autoresponder.').'</td>
					      <td></td>
					      <td></td>
					    </tr>
			  			
			  			';
				  	}
			  	?>
			    
			  </tbody>
			</table>
	    </div>
	    
	    <!-- Last 10 opened -->
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
		    	<h2 class="report-titles"><?php echo _('Last 10 opened');?></h2>
		    	<a href="<?php echo get_app_info('path');?>/includes/ares-reports/export-csv.php?c=<?php echo $id?>&a=opens" title="<?php echo _('Export subscribers who opened');?>" class="report-export"><i class="icon icon-download-alt"></i></a>
	    	</div>
	    </div>
	    <br/>
	    <div class="row-fluid">
	    	<table class="table table-striped table-condensed responsive">
			  <thead>
			    <tr>
			      <th><?php echo _('Name');?></th>
			      <th><?php echo _('Email');?></th>
			      <th><?php echo _('List');?></th>
			      <th><?php echo _('Status');?></th>
			    </tr>
			  </thead>
			  <tbody>
			  	
			  	<?php 
				  	$q = 'SELECT opens FROM ares_emails WHERE id = '.mysqli_real_escape_string($mysqli, $_GET['ae']);
				  	$r = mysqli_query($mysqli, $q);
				  	if ($r && mysqli_num_rows($r) > 0)
				  	{
				  	    while($row = mysqli_fetch_array($r))
				  	    {
				  	    	$last_opens = $row['opens'];
				  			$last_opens_array = explode(',', $last_opens);
				  			$loop_no = count(array_unique($last_opens_array));
				  			if($loop_no>10) $loop_no = 10;
				  			
				  			if($last_opens=='')
				  			{
					  			echo '
									  			
					  			<tr>
							      <td>'._('No one opened yet.').'</td>
							      <td></td>
							      <td></td>
							      <td></td>
							    </tr>
					  			
					  			';
				  			}
				  			
				  	    	for($z=0;$z<$loop_no;$z++)
				  	    	{
				  	    		$last_opens_array2 = array_reverse(array_unique($last_opens_array));
					  			$last_subscriber_id = explode(':', $last_opens_array2[$z]);
					  			
					  			$q2 = 'SELECT * FROM subscribers WHERE id = '.$last_subscriber_id[0];
					  			$r2 = mysqli_query($mysqli, $q2);
					  			if ($r2 && mysqli_num_rows($r2) > 0)
					  			{
					  			    while($row = mysqli_fetch_array($r2))
					  			    {
					  					$subscriber_id = stripslashes($row['id']);
							  			$name = stripslashes($row['name']);
							  			$email = stripslashes($row['email']);
							  			$listID = stripslashes($row['list']);
							  			$timestamp = parse_date($row['timestamp'], 'short', true);
							  			$unsubscribed = stripslashes($row['unsubscribed']);
							  			$bounced = stripslashes($row['bounced']);
							  			$complaint = stripslashes($row['complaint']);
							  			if($unsubscribed==0)
							  				$unsubscribed = '<span class="label label-success">'._('Subscribed').'</span>';
							  			else if($unsubscribed==1)
							  				$unsubscribed = '<span class="label label-important">'._('Unsubscribed').'</span>';
							  			if($bounced==1)
								  			$unsubscribed = '<span class="label label-inverse">'._('Bounced').'</span>';
								  		if($complaint==1)
								  			$unsubscribed = '<span class="label label-inverse">'._('Marked as spam').'</span>';
							  			
							  			if($name=='')
							  				$name = '['._('No name').']';
							  				
							  			$q2 = 'SELECT name FROM lists WHERE id = '.$listID;
							  			$r2 = mysqli_query($mysqli, $q2);
							  			if ($r2 && mysqli_num_rows($r2) > 0)
							  			{
							  			    while($row = mysqli_fetch_array($r2))
							  			    {
							  					$list_name = stripslashes($row['name']);
							  			    }  
							  			}
					  					
					  					echo '
							  			
							  			<tr>
									      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$name.'</a></td>
									      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$email.'</a></td>
									      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$listID.'" title="">'.$list_name.'</a></td>
									      <td>'.$unsubscribed.'</td>
									    </tr>
							  			
							  			';
					  			    }  
					  			}
					  		}
				  	    }  
				  	}
				  	else
				  	{
					  	echo '
				  			
			  			<tr>
					      <td>'._('No one opened yet.').'</td>
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
	    
	    <!-- Unsubscribed -->
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
		    	<h2 class="report-titles"><?php echo _('Last 10 unsubscribed');?></h2>
		    	<a href="<?php echo get_app_info('path');?>/includes/ares-reports/export-csv.php?c=<?php echo $id?>&a=unsubscribes" title="<?php echo _('Export subscribers who unsubscribed');?>" class="report-export"><i class="icon icon-download-alt"></i></a>
	    	</div>
	    </div>
	    <br/>
	    <div class="row-fluid">
	    	<table class="table table-striped table-condensed responsive">
			  <thead>
			    <tr>
			      <th><?php echo _('Name');?></th>
			      <th><?php echo _('Email');?></th>
			      <th><?php echo _('List');?></th>
			      <th><?php echo _('Status');?></th>
			      <th><?php echo _('Date');?></th>
			    </tr>
			  </thead>
			  <tbody>
			  	
			  	<?php 
				  	$q = 'SELECT * FROM subscribers WHERE unsubscribed = 1 AND last_ares = '.mysqli_real_escape_string($mysqli, $_GET['ae']).' LIMIT 10';
				  	$r = mysqli_query($mysqli, $q);
				  	if ($r && mysqli_num_rows($r) > 0)
				  	{
				  	    while($row = mysqli_fetch_array($r))
				  	    {
				  	    	$subscriber_id = stripslashes($row['id']);
				  			$name = stripslashes($row['name']);
				  			$email = stripslashes($row['email']);
				  			$listID = stripslashes($row['list']);
				  			$timestamp = parse_date($row['timestamp'], 'short', true);
				  			
				  			
				  			if($name=='')
				  				$name = '['._('No name').']';
				  				
				  			$q2 = 'SELECT name FROM lists WHERE id = '.$listID;
				  			$r2 = mysqli_query($mysqli, $q2);
				  			if ($r2 && mysqli_num_rows($r2) > 0)
				  			{
				  			    while($row = mysqli_fetch_array($r2))
				  			    {
				  					$list_name = stripslashes($row['name']);
				  			    }  
				  			}
				  			
				  			echo '
				  			
				  			<tr>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$name.'</a></td>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$email.'</a></td>
						      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$listID.'" title="">'.$list_name.'</a></td>
						      <td><span class="label label-important">'._('Unsubscribed').'</span></td>
						      <td>'.$timestamp.'</td>
						    </tr>
				  			
				  			';
				  	    }  
				  	}
				  	else
				  	{
					  	echo '
				  			
			  			<tr>
					      <td>'._('No one unsubscribed from this autoresponder!').'</td>
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
	    
	    <!-- Bounced -->
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
		    	<h2 class="report-titles"><?php echo _('Last 10 bounced emails');?></h2>
		    	<a href="<?php echo get_app_info('path');?>/includes/ares-reports/export-csv.php?c=<?php echo $id?>&a=bounces" title="<?php echo _('Export subscribers who bounced');?>" class="report-export"><i class="icon icon-download-alt"></i></a>
	    	</div>
	    </div>
	    <br/>
	    <div class="row-fluid">
	    	<table class="table table-striped table-condensed responsive">
			  <thead>
			    <tr>
			      <th><?php echo _('Name');?></th>
			      <th><?php echo _('Email');?></th>
			      <th><?php echo _('List');?></th>
			      <th><?php echo _('Status');?></th>
			      <th><?php echo _('Date');?></th>
			    </tr>
			  </thead>
			  <tbody>
			  	
			  	<?php 
				  	$q = 'SELECT * FROM subscribers WHERE bounced = 1 AND last_ares = '.mysqli_real_escape_string($mysqli, $_GET['ae']).' LIMIT 10';
				  	$r = mysqli_query($mysqli, $q);
				  	if ($r && mysqli_num_rows($r) > 0)
				  	{
				  	    while($row = mysqli_fetch_array($r))
				  	    {
				  	    	$subscriber_id = stripslashes($row['id']);
				  			$name = stripslashes($row['name']);
				  			$email = stripslashes($row['email']);
				  			$listID = stripslashes($row['list']);
				  			$timestamp = parse_date($row['timestamp'], 'short', true);
				  			
				  			if($name=='')
				  				$name = '['._('No name').']';
				  				
				  			$q2 = 'SELECT name FROM lists WHERE id = '.$listID;
				  			$r2 = mysqli_query($mysqli, $q2);
				  			if ($r2 && mysqli_num_rows($r2) > 0)
				  			{
				  			    while($row = mysqli_fetch_array($r2))
				  			    {
				  					$list_name = stripslashes($row['name']);
				  			    }  
				  			}
				  			
				  			echo '
				  			
				  			<tr>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$name.'</a></td>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$email.'</a></td>
						      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$listID.'" title="">'.$list_name.'</a></td>
						      <td><span class="label label-inverse">'._('Bounced').'</span></td>
						      <td>'.$timestamp.'</td>
						    </tr>
				  			
				  			';
				  	    }  
				  	}
				  	else
				  	{
					  	echo '
				  			
			  			<tr>
					      <td>'._('No emails bounced from this autoresponder!').'</td>
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
	    
	    <!-- Marked as spam -->
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
		    	<h2 class="report-titles"><?php echo _('Last 10 marked as spam');?></h2>
		    	<a href="<?php echo get_app_info('path');?>/includes/ares-reports/export-csv.php?c=<?php echo $id?>&a=complaints" title="<?php echo _('Export subscribers who marked your email as spam');?>" class="report-export"><i class="icon icon-download-alt"></i></a>
	    	</div>
	    </div>
	    <br/>
	    <div class="row-fluid">
	    	<table class="table table-striped table-condensed responsive">
			  <thead>
			    <tr>
			      <th><?php echo _('Name');?></th>
			      <th><?php echo _('Email');?></th>
			      <th><?php echo _('List');?></th>
			      <th><?php echo _('Status');?></th>
			      <th><?php echo _('Date');?></th>
			    </tr>
			  </thead>
			  <tbody>
			  	
			  	<?php 
				  	$q = 'SELECT * FROM subscribers WHERE complaint = 1 AND last_ares = '.mysqli_real_escape_string($mysqli, $_GET['ae']).' LIMIT 10';
				  	$r = mysqli_query($mysqli, $q);
				  	if ($r && mysqli_num_rows($r) > 0)
				  	{
				  	    while($row = mysqli_fetch_array($r))
				  	    {
				  	    	$subscriber_id = stripslashes($row['id']);
				  			$name = stripslashes($row['name']);
				  			$email = stripslashes($row['email']);
				  			$listID = stripslashes($row['list']);
				  			$timestamp = parse_date($row['timestamp'], 'short', true);
				  			
				  			if($name=='')
				  				$name = '[No name]';
				  				
				  			$q2 = 'SELECT name FROM lists WHERE id = '.$listID;
				  			$r2 = mysqli_query($mysqli, $q2);
				  			if ($r2 && mysqli_num_rows($r2) > 0)
				  			{
				  			    while($row = mysqli_fetch_array($r2))
				  			    {
				  					$list_name = stripslashes($row['name']);
				  			    }  
				  			}
				  			
				  			echo '
				  			
				  			<tr>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$name.'</a></td>
						      <td><a href="#subscriber-info" data-id="'.$subscriber_id.'" data-toggle="modal" class="subscriber-info">'.$email.'</a></td>
						      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$listID.'" title="">'.$list_name.'</a></td>
						      <td><span class="label label-inverse">'._('Marked as spam').'</span></td>
						      <td>'.$timestamp.'</td>
						    </tr>
				  			
				  			';
				  	    }  
				  	}
				  	else
				  	{
					  	echo '
				  			
			  			<tr>
					      <td>'._('No one marked your email as spam!').'</td>
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
	    
	    <!-- Countries -->
	    <br/>
	    <div class="row-fluid">
	    	<div class="span12">
	    		<h2><?php echo _('All countries');?></h2><br/>
		    	<table class="table table-striped table-condensed responsive">
				  <thead>
				    <tr>
				      <th><?php echo _('Country');?></th>
				      <th><?php echo _('Opens');?></th>
				      <th><?php echo _('Export');?></th>
				    </tr>
				  </thead>
				  <tbody>
				  	
				  	<?php 		  			
			  			if($opens_all!='')
			  			{
			  				$unique_countries = array_unique($opens_array);
			  				$unique_countries_array = array();
			  				$country_count_array = array();
			  				
				  			for($i=0;$i<count($opens_array);$i++)
				  			{
				  				if(array_key_exists($i, $unique_countries)) $ucnts = $unique_countries[$i];
				  				else $ucnts = '';
				  				
				  				$get_country = explode(':', $ucnts);
				  				if(array_key_exists(1, $get_country)) $gcty = $get_country[1];
				  				else $gcty = '';
				  				
				  				if($gcty!='')
				  				{
						  			array_push($unique_countries_array, $gcty);
						  		}
				  			}
				  			
				  			$unique_countries_array_unique = array_unique($unique_countries_array);
				  			
				  			foreach($unique_countries_array_unique as $ucau)
				  			{
				  				$no_in_country = array_keys($unique_countries_array, $ucau);
				  				array_push($country_count_array, count($no_in_country).'%'.country_code_to_country($ucau).'%'.$ucau);
				  			}
				  			
				  			natsort($country_count_array);
				  			$country_count_array = array_reverse($country_count_array);
				  			if(count($opens_array)==0)
							{
								echo '
					  			<tr>
					  				<td>'._('No opens yet!').'</td>
					  				<td>0</td>
					  				<td></td>
					  			</tr>
					  			<script type="text/javascript">
							  		$("#countries-container").html("<span class=\'badge\'>'._('No opens yet!').'</span>");
							  		$("#countries-container").css("margin-top", "155px");
							  		$("#countries-container").css("margin-left", "180px");
							  		$("#countries-container").css("margin-bottom", "-155px");
							  	</script>
					  			';
							}
							else
							{
					  			foreach($country_count_array as $cca)
					  			{
					  				$cc = explode('%',$cca);
					  				
						  			echo '
						  			<tr>
						  				<td>'.$cc[1].'</td>
						  				<td>'.$cc[0].'</td>
						  				<td><a href="'.get_app_info('path').'/includes/ares-reports/export-csv.php?c='.$id.'&a='.$cc[2].'" title="'._('Export subscribers from').' '.$cc[1].'"><i class="icon icon-download-alt"></i></a></td>
						  			</tr>
						  			';
					  			}
					  			
					  	?>
		  			<script type="text/javascript">
		  				var chart2;
						$(document).ready(function() {
							chart2 = new Highcharts.Chart({
								chart: {
									renderTo: 'countries-container',
									plotBackgroundColor: null,
									plotBorderWidth: null,
									plotShadow: false
								},
								title: {
									text: '<?php echo _('Top 10 countries');?>',
									style: {
										color: '#525252',
										fontWeight: 'bold',
										fontSize: '14px'
									},
									verticalAlign: 'bottom'
								},
								tooltip: {
									formatter: function() {
										return '<b>'+ this.point.name +'</b>: '+Math.round(this.percentage) +' %';
									}
								},
								plotOptions: {
									pie: {
										borderWidth: 0,
										shadow: false,
										allowPointSelect: true,
										cursor: 'pointer',
										dataLabels: {
											enabled: true
										},
										showInLegend: false
									}
								},
								credits: {
					                enabled: false
					            },
								series: [{
									type: 'pie',
									name: 'Countries',
									data: [
										<?php 
											$ct = 0;
											foreach($country_count_array as $cca)
								  			{
								  				if($ct<10)
								  				{
									  				$cc = explode('%',$cca);
									  				
									  				if($ct==0)
									  				{
										  				echo '{
															name: "'.$cc[1].'",
															y: '.$cc[0].',
															sliced: true,
															selected: true
														},';
									  				}
									  				else
									  				{
											  			echo '
											  			[\''.$cc[1].'\',   '.$cc[0].'],
											  			';
											  		}
										  		}
										  		$ct++;
									  		}
										?>
									]
								}],
								exporting: { enabled: false }
							});
						});
		  			</script>
					  			
			  		<?php
			  		
					  		}
			  			}
					  	else
					  	{
						  	echo '
					  			
				  			<tr>
						      <td>'._('No countries detected yet.').'</td>
						      <td></td>
						    </tr>
				  			
				  			';
					  	}
				  	?>
				    
				  </tbody>
				</table>
	    	</div>
	    	
	    </div>
	    
    </div>   
</div>
<div id="subscriber-info" class="modal hide fade">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h3><?php echo _('Subscriber info');?></h3>
    </div>
    <div class="modal-body">
	    <p id="subscriber-text"></p>
    </div>
    <div class="modal-footer">
      <a href="#" class="btn btn-inverse" data-dismiss="modal"><?php echo _('Close');?></a>
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
