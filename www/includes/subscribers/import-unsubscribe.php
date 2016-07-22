<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php

/********************************/
$databasetable = "subscribers";
$fieldseparator = ",";
$lineseparator = "\n";
$csvfile = $_FILES['csv_file']['tmp_name'];
$userID = get_app_info('main_userID');
$app = $_POST['app'];
$listID = mysqli_real_escape_string($mysqli, $_POST['list_id']);
/********************************/

if(!file_exists($csvfile)) {
	header("Location: ".get_app_info('path').'/unsubscribe-from-list?i='.$app.'&l='.$listID.'&e=3'); 
	exit;
}

$file = fopen($csvfile,"r");

if(!$file) {
	echo _('Error opening data file.');
	echo ".\n";
	exit;
}

$size = filesize($csvfile);

if(!$size) {
	echo _('File is empty.');
	echo "\n";
	exit;
}

$csvcontent = fread($file,$size);

fclose($file);

$linearray = array();

foreach(explode($lineseparator,$csvcontent) as $line)
{
	//cleanup line
	$line = trim($line," \t");
	$line = str_replace("\r","",$line);
	$line = str_replace('"','',$line);
	$line = str_replace("'",'',$line);
	
	//get the columns
	$linearray = explode($fieldseparator,$line);
	$columns = count($linearray);
	
	//check if there's more than 1 column
	if($columns>1)
	{
		header("Location: ".get_app_info('path').'/unsubscribe-from-list?i='.$app.'&l='.$listID.'&e=1'); 
		exit;
	}
	
	//check if email exists to be unsubscribed
	$q = 'SELECT email FROM subscribers WHERE list = '.$listID.' AND email = "'.$line.'" AND userID = '.$userID;
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) > 0)
	{
		//email exists, unsubscribe subscriber
		$query = 'UPDATE '.$databasetable.' SET unsubscribed = 1 WHERE email = "'.$line.'" AND list = '.$listID.' AND userID = '.$userID;
		mysqli_query($mysqli, $query);
	}
}

//return
header("Location: ".get_app_info('path').'/subscribers?i='.$app.'&l='.$listID); 

?>
