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
	function get_saved_data($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		global $edit;
		
		$q = 'SELECT '.$val.' FROM template WHERE id = "'.mysqli_real_escape_string($mysqli, $_GET['t']).'" AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
		    	$value = stripslashes($row[$val]);				
				return $value;
		    }  
		}
	}
	
	//------------------------------------------------------//
	function totals($app)
	//------------------------------------------------------//
	{
		global $mysqli;
			
		$q = 'SELECT id FROM template WHERE app = '.$app.' AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r) return mysqli_num_rows($r);
	}
	
	//------------------------------------------------------//
	function pagination($limit)
	//------------------------------------------------------//
	{		
		global $p;
		
		$curpage = $p;
		
		$next_page_num = 0;
		$prev_page_num = 0;
		
		$total_campaigns = totals($_GET['i']);
		$total_pages = @ceil($total_campaigns/$limit);
		
		if($total_campaigns > $limit)
		{
			if($curpage>=2)
			{
				$next_page_num = $curpage+1;
				$prev_page_num = $curpage-1;
			}
			else
			{
				$next_page_num = 2;
			}
		
			echo '<div class="btn-group" id="pagination">';
			
			//Prev btn
			if($curpage>=2)
				if($prev_page_num==1)
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/templates?i='.get_app_info('app').'\'"><span class="icon icon icon-arrow-left"></span></button>';
				else
					echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/templates?i='.get_app_info('app').'&p='.$prev_page_num.'\'"><span class="icon icon icon-arrow-left"></span></button>';
			else
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-left"></span></button>';
			
			//Next btn
			if($curpage==$total_pages)
				echo '<button class="btn disabled"><span class="icon icon icon-arrow-right"></span></button>';
			else
				echo '<button class="btn" onclick="window.location=\''.get_app_info('path').'/templates?i='.get_app_info('app').'&p='.$next_page_num.'\'"><span class="icon icon icon-arrow-right"></span></button>';
					
			echo '</div>';
		}
	}
?>