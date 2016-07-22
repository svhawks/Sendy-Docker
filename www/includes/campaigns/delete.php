<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	$campaign_id = mysqli_real_escape_string($mysqli, $_POST['campaign_id']);
	
	$q = 'DELETE FROM campaigns WHERE id = '.$campaign_id.' AND userID = '.get_app_info('main_userID');
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		$q2 = 'DELETE FROM links WHERE campaign_id = '.$campaign_id;
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2)
		{
			if(file_exists('../../uploads/attachments/'.$campaign_id))
			{
				$files = glob('../../uploads/attachments/'.$campaign_id.'/*'); // get all file names
				foreach($files as $file){
				    unlink($file); 
				}
				rmdir('../../uploads/attachments/'.$campaign_id);
			}
		    echo true; 
		}
	}
	
?>