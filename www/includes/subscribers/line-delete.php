<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php

/********************************/
$userID = get_app_info('main_userID');
$app = $_POST['app'];
$listID = mysqli_real_escape_string($mysqli, $_POST['list_id']);
$line = $_POST['line'];
/********************************/

//if user did not enter anything
if($line=='')
{
	//show error msg
	header("Location: ".get_app_info('path').'/delete-from-list?i='.$app.'&l='.$listID.'&e=2'); 
	exit;
}

$line_array = explode("\r\n", $line);

for($i=0;$i<count($line_array);$i++)
{
	$q = 'DELETE FROM subscribers WHERE email = "'.trim($line_array[$i]).'" AND list = '.$listID.' AND userID = '.$userID;
	$r = mysqli_query($mysqli, $q);
	if ($r){}
}

header("Location: ".get_app_info('path').'/subscribers?i='.$app.'&l='.$listID); 

?>
