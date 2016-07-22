<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php 
	$listID = mysqli_real_escape_string($mysqli, $_GET['list_id']);
	$delete = $_POST['delete'];
	
	if($listID != '')
	{	
		$q = 'SELECT name FROM lists WHERE id = '.$listID;
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$list_name = $row['name'];
		    }  
		}
	
		$q = 'SELECT id FROM subscribers WHERE list = '.$listID.' GROUP BY email HAVING (COUNT(email) > 1)';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
			$i = 0;
		    while($row = mysqli_fetch_array($r))
		    {
				$id = $row['id'];
				
				if(count($_POST)!=0 && $_POST['delete']==1)
				{
					$q2 = 'DELETE FROM subscribers WHERE id = '.$id;
					mysqli_query($mysqli, $q2);
				}
				
				$i++;
		    }  
		    
		    if(count($_POST)!=0)
			{
			    echo '<b>'.$i.'</b> '._('duplicate emails deleted from').' <b>'.$list_name.'</b>.<br/><br/><a href="'.get_app_info('path').'/remove-duplicates" style="text-decoration:underline;">&larr; '._('Back').'</a>';
			}
			else
			{
				echo '<form action="" method="post">';
				echo '<b>'.$i.'</b> '._('duplicate email(s) found in').' <b>'.$list_name.'</b>, '._('delete them?');
				echo '<input type="hidden" name="delete" value="1"/>
						<br/><br/><input type="submit" name="submit" class="btn" value="Delete"/>
					</form><br/><a href="'.get_app_info('path').'/remove-duplicates" style="text-decoration:underline;">&larr; '._('Back').'</a>';
			}
		}
		else
		{
			echo _('No duplicate emails found.').'<br/><br/><a href="'.get_app_info('path').'/remove-duplicates" style="text-decoration:underline;">&larr; '._('Back').'</a>';
		}
	}
	else
	{
		echo '
		<h2>'._('Check and remove duplicate emails').'</h2><br/>
		'._('Select the list that have duplicates so that you can remove them').':<br/><br/><h3>'._('All available lists').'</h3><br/>
		';
		$q = 'SELECT id, name FROM lists WHERE userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$id = $row['id'];
				$list_name = $row['name'];
				
				$q2 = 'SELECT id FROM subscribers WHERE list = '.$id.' GROUP BY email HAVING (COUNT(email) > 1)';
				$r2 = mysqli_query($mysqli, $q2);
				if ($r2 && mysqli_num_rows($r2) > 0)
				{
					$i = 0;
				    while($row = mysqli_fetch_array($r2))
				    {
				    	$i++;
				    }
				}
				
				echo '<a href="'.get_app_info('path').'/remove-duplicates?list_id='.$id.'" style="text-decoration:underline;">'.$list_name.'</a> ('.mysqli_num_rows($r2).' '._('duplicates').')<br/>';
		    }  
		}
	}
?>
<?php include('includes/footer.php');?>