<?php 
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//------------------------------------------------------//
	function get_app_data($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM apps WHERE id = "'.get_app_info('app').'" AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row[$val];
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_lists_data($val, $lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM lists WHERE app = "'.get_app_info('app').'" AND id = '.$lid.' AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return stripslashes($row[$val]);
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_subscribers_count($lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		
		//Check if the list has a pending CSV for importing via cron
		$server_path_array = explode('list.php', $_SERVER['SCRIPT_FILENAME']);
		$server_path = $server_path_array[0];
		
		if (file_exists($server_path.'uploads/csvs') && $handle = opendir($server_path.'uploads/csvs')) 
		{
		    while (false !== ($file = readdir($handle))) 
		    {
		    	if($file!='.' && $file!='..' && $file!='.DS_Store' && $file!='.svn')
		    	{
			    	$file_array = explode('-', $file);
			    	
			    	if(!empty($file_array))
			    	{
				    	if(str_replace('.csv', '', $file_array[1])==$lid)
					    	return _('Checking..').'
					    		<script type="text/javascript">
					    			$(document).ready(function() {
					    			
					    				list_interval = setInterval(function(){get_list_count('.$lid.')}, 2000);
						    			
						    			function get_list_count(lid)
						    			{
						    				clearInterval(list_interval);
							    			$.post("includes/list/progress.php", { list_id: lid, user_id: '.get_app_info('main_userID').' },
											  function(data) {
											      if(data)
											      {
											      	if(data.indexOf("%)") != -1)
											      		list_interval = setInterval(function(){get_list_count('.$lid.')}, 2000);
											      		
											      	$("#progress'.$lid.'").html(data);
											      }
											      else
											      {
											      	$("#progress'.$lid.'").html("'._('Error retrieving count').'");
											      }
											  }
											);
										}
										
						    		});
					    		</script>';
			    	}
			    }
		    }
		    closedir($handle);
		}
		
		//if not, just return the subscriber count
		$q = 'SELECT COUNT(list) FROM subscribers use index (s_list) WHERE list = '.$lid.' AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			while($row = mysqli_fetch_array($r))
		    {
				return $row['COUNT(list)'];
		    } 
		}
	}
	
	//------------------------------------------------------//
	function get_unsubscribers_count($lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT COUNT(list) FROM subscribers use index (s_list) WHERE list = '.$lid.' AND unsubscribed = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) return $row['COUNT(list)'];
	}
	
	//------------------------------------------------------//
	function get_unsubscribers_percentage($subscribers, $unsubscribers)
	//------------------------------------------------------//
	{
		$sub_unsub_total = $subscribers+$unsubscribers;
		$unsub_percentage = $sub_unsub_total==0 ? round($unsubscribers * 100, 2) : round($unsubscribers / ($sub_unsub_total) * 100, 2);
		return $unsub_percentage;
	}
	
	//------------------------------------------------------//
	function get_bounced_count($lid)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT COUNT(list) FROM subscribers use index (s_list) WHERE list = '.$lid.' AND bounced = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r) while($row = mysqli_fetch_array($r)) return $row['COUNT(list)'];
	}
	
	//------------------------------------------------------//
	function get_bounced_percentage($bouncers, $subscribers)
	//------------------------------------------------------//
	{
		$bounce_subs_total = $subscribers+$bouncers;
		$bounce_percentage = $bounce_subs_total==0 ? round($bouncers * 100, 2) : round($bouncers / ($bounce_subs_total) * 100, 2);
		return $bounce_percentage;
	}
?>