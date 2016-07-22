<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$id = mysqli_real_escape_string($mysqli, $_POST['id']);
	
	$q = 'DELETE FROM ares WHERE id = '.$id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		$q3 = 'SELECT id FROM ares_emails WHERE ares_id = '.$id;
		$r3 = mysqli_query($mysqli, $q3);
		if ($r3 && mysqli_num_rows($r3) > 0)
		{
		    while($row = mysqli_fetch_array($r3))
		    {
				$ares_email_id = $row['id'];
				
				if(file_exists('../../uploads/attachments/a'.$ares_email_id))
				{
					$files = glob('../../uploads/attachments/a'.$ares_email_id.'/*'); // get all file names
					foreach($files as $file){
					    unlink($file); 
					}
					rmdir('../../uploads/attachments/a'.$ares_email_id);
				}
		    }  
		}
		
		$q2 = 'DELETE FROM ares_emails WHERE ares_id = '.$id;
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2)
		{
			echo true;
		}
	}
	
?>