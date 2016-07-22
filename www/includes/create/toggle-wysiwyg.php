<?php 
	include('../functions.php');
	include('../login/auth.php');
	
	$toggle = mysqli_real_escape_string($mysqli, $_POST['toggle']);
	$app = mysqli_real_escape_string($mysqli, $_POST['i']);
	$c = mysqli_real_escape_string($mysqli, $_POST['c']);
	
	if($toggle==_('Save and switch to HTML editor'))
		$toggle = 0;
	else
		$toggle = 1;
	
	$q = 'UPDATE campaigns SET wysiwyg='.$toggle.' WHERE app = '.$app.' AND id='.$c;
	$r = mysqli_query($mysqli, $q);
	if ($r)
		echo true;
?>