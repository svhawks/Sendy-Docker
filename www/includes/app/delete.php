<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$id = mysqli_real_escape_string($mysqli, $_POST['id']);
	
	//delete links
	$q = 'SELECT id FROM campaigns WHERE app = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$campaign_id = stripslashes($row['id']);
			
			$q = 'DELETE FROM links WHERE campaign_id = '.$campaign_id;
			$r = mysqli_query($mysqli, $q);
			if ($r)
			{
			    $q = 'DELETE FROM campaigns WHERE id = '.$campaign_id;
				$r = mysqli_query($mysqli, $q);
				if ($r)
				{
				    //ok
				}
			}
	    }  
	}
	
	//delete subscribers
	$q = 'SELECT id FROM lists WHERE app = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$list_id = stripslashes($row['id']);
			
			$q = 'DELETE FROM subscribers WHERE list = '.$list_id;
			$r = mysqli_query($mysqli, $q);
			if ($r)
			{
			    $q = 'DELETE FROM lists WHERE id = '.$list_id;
				$r = mysqli_query($mysqli, $q);
				if ($r)
				{
				    //ok
				}
			}
	    }  
	}
	
	//delete login
	$q = 'DELETE FROM login WHERE app = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    echo true;
	}
	
	//delete app
	$q = 'DELETE FROM apps WHERE id = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    echo true;
	}
	
?>