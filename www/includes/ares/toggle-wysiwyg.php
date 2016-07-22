<?php 
	include('../functions.php');
	include('../login/auth.php');
	
	$toggle = mysqli_real_escape_string($mysqli, $_POST['toggle']);
	$c = mysqli_real_escape_string($mysqli, $_POST['ae']);
	
	if($toggle==_('Save and switch to HTML editor'))
		$toggle = 0;
	else
		$toggle = 1;
	
	$q = 'UPDATE ares_emails SET wysiwyg='.$toggle.' WHERE id='.$c;
	$r = mysqli_query($mysqli, $q);
	if ($r)
		echo true;
?>