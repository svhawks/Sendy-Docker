<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$app = mysqli_real_escape_string($mysqli, $_POST['id']);
	$list_id = mysqli_real_escape_string($mysqli, $_POST['list']);
	
	//autoresponder data
	$name = mysqli_real_escape_string($mysqli, $_POST['autoresponder_name']);
	$type = mysqli_real_escape_string($mysqli, $_POST['autoresponder_type']);
	$type2_custom_fields = mysqli_real_escape_string($mysqli, $_POST['type2_custom_fields']);
	$type3_custom_fields = mysqli_real_escape_string($mysqli, $_POST['type3_custom_fields']);
	if($type==2) $custom_field = $type2_custom_fields;
	else if($type==3) $custom_field = $type3_custom_fields;
	else $custom_field = '';
	
	//check if custom field is in use
	$custom_field_array = explode('%s%', $custom_field);
	if(array_key_exists(1, $custom_field_array)) $cfa = $custom_field_array[1];
	else $cfa = '';
	
	if($cfa=='inuse')
	{
		header("Location: ".get_app_info('path')."/autoresponders-list?i=$app&l=$list_id&e=1");
		exit;
	}
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	$q = 'INSERT INTO ares (name, type, list, custom_field) VALUES ("'.$name.'", "'.$type.'", "'.$list_id.'", "'.$custom_field.'")';
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		$ares_id = mysqli_insert_id($mysqli);
		header("Location: ".get_app_info('path')."/autoresponders-create?i=$app&a=$ares_id");
	}
?>