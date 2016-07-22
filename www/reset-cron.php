<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>

<div class="row-fluid">
    <div class="span12">
    	<h2><?php echo _('Reset cron setup');?></h2><br/>
    	<?php 
    		$confirm = $_POST['c'];
    		
    		if(count($_POST)!=0 && $confirm==1)
    		{
		    	$q = 'UPDATE login SET cron = 0, cron_ares = 0, cron_csv = 0';
		    	$r = mysqli_query($mysqli, $q);
		    	if ($r)
		    	    echo _('Cron setup has been reset. You\'ll now be able to see cron setup instructions.'); 
		    	else
		    		echo _('Failed to reset cron.');
		    }
		    else
		    {
			    echo '<form action="" method="post">';
				echo _('Do you want to reset cron setup so that you can view cron setup instructions again?');
				echo '<input type="hidden" name="c" value="1"/>
						<br/><br/><input type="submit" name="submit" class="btn" value="'._('Yes, reset cron setup').'"/>
					</form>';
		    }
    	?>
    </div> 
</div>
<?php include('includes/footer.php');?>
