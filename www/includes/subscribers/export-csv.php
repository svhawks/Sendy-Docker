<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 

/********************************/
$userID = get_app_info('main_userID');
$app = mysqli_real_escape_string($mysqli, $_GET['i']);
$listID = mysqli_real_escape_string($mysqli, $_GET['l']);

//Check if sub user is trying to download CSVs from other brands
if(get_app_info('is_sub_user')) 
{
	$q = 'SELECT app FROM lists WHERE id = '.$listID;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0) while($row = mysqli_fetch_array($r)) $app_attached_to_listID = $row['app'];

	if(get_app_info('app')!=get_app_info('restricted_to_app') || $app_attached_to_listID!=get_app_info('restricted_to_app'))
	{
		echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/list?i='.get_app_info('restricted_to_app').'"</script>';
		exit;
	}
}

if(isset($_GET['a']))
{
	$additional = 'AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1';
	$filename_additional = '-active';
}
else if(isset($_GET['c']))
{
	$additional = 'AND confirmed = 0';
	$filename_additional = '-unconfirmed';
}
else if(isset($_GET['u']))
{
	$additional = 'AND unsubscribed = 1 AND bounced = 0';
	$filename_additional = '-unsubscribed';
}
else if(isset($_GET['b']))
{
	$additional = 'AND bounced = 1';
	$filename_additional = '-bounced';
}
else if(isset($_GET['cp']))
{
	$additional = 'AND complaint = 1';
	$filename_additional = '-marked-as-spam';
}
else
{
	$additional = '';
	$filename_additional = '-all';
}
/********************************/

$q = 'SELECT name, custom_fields FROM lists WHERE id = '.$listID.' AND userID = '.$userID;
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0)
{
    while($row = mysqli_fetch_array($r))
    {
		$list_name = $row['name'];
		$custom_fields = $row['custom_fields'];
		$filename = str_replace(' ', '-', $list_name);
		$filename = strtolower($filename.$filename_additional).'.csv';
    }  
}

$q2 = 'SELECT name, email, custom_fields FROM subscribers WHERE list = '.$listID.' '.$additional.' AND userID = '.$userID;
$r2 = mysqli_query($mysqli, $q2);
if ($r2 && mysqli_num_rows($r2) > 0)
{
	$data = '';
    while($row = mysqli_fetch_array($r2))
    {
		$name = '"'.$row['name'].'"';
		$email = '"'.$row['email'].'"';
		$custom_values = $row['custom_fields'];
		$cf_value = '';
		
		if($custom_fields=='')
			$data .= $name.','.$email."\n";
		else
		{
			//format custom fields into CSV
			$custom_fields_array = explode('%s%', $custom_fields);
			$custom_values_array = explode('%s%', $custom_values);
			for($i=0;$i<count($custom_fields_array);$i++)
			{
				$cf_field_array = explode(':', $custom_fields_array[$i]);
				
				if($cf_field_array[1]=='Date' && $cf_field_array[1]!='')
					$cf_value .= '"'.strftime("%b %d %Y", $custom_values_array[$i]).'",';
				else			
					$cf_value .= '"'.$custom_values_array[$i].'",';
			}
			$cf_value = substr($cf_value, 0, -1);
			$data .= $name.','.$email.','.$cf_value."\n";
		}
    }
    $data = substr($data, 0, -1);
    $data = str_replace("\r" , "" , $data);
}

if ( $data == "" )
{
    $data = "\n(0) Records Found!\n";                        
}

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");
print "$data";
 
?>