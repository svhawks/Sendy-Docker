<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>

<div class="row-fluid">
    <div class="span12">
    	<h2><?php echo _('Clear queue table');?></h2><br/>
    	<?php 
    		$confirm = $_POST['c'];
    		
    		if(count($_POST)!=0 && $confirm==1)
    		{
		    	$q = 'DELETE FROM queue';
		    	$r = mysqli_query($mysqli, $q);
		    	if ($r)
		    	    echo _('The queue table has been cleared successfully!'); 
		    	else
		    		echo _('Failed to clear the queue table.');
		    }
		    else
		    {
			    echo '<form action="" method="post">';
				echo _('Please make sure all currently sending campaigns are completed first before doing this.').'<br/><br/>'._('Do you want to clear the queue table?');
				echo '<input type="hidden" name="c" value="1"/>
						<br/><br/><input type="submit" name="submit" class="btn" value="'._('Yes, clear the queue table').'"/>
					</form>';
		    }
    	?>
    </div> 
</div>
<?php include('includes/footer.php');?>
