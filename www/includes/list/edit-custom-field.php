<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$index = $_POST['field_index'];
	$field_name = mysqli_real_escape_string($mysqli, $_POST['field_name']);
	$list_id = mysqli_real_escape_string($mysqli, $_POST['lid']);
	$app = mysqli_real_escape_string($mysqli, $_POST['the_app']);
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	$q = 'SELECT custom_fields FROM lists WHERE id = '.$list_id;
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$custom_fields = $row['custom_fields'];
	    }  
	    
	    //delete element from array
	    $custom_fields_array = explode('%s%', $custom_fields);
	    
	    //check if custom field exists	    
	    foreach($custom_fields_array as $cf)
	    {
		    $cf_array = explode(':', $cf);
		    if(strtolower($cf_array[0])==strtolower($field_name) || strtolower($field_name)=='name' || strtolower($field_name)=='email')
		    {
		    	header("Location: ".get_app_info('path')."/custom-fields?i=$app&l=$list_id&e=1");
			    exit;
		    }
	    }
	    
	    $custom_field = explode(':', $custom_fields_array[$index]);
	    $custom_field_old = $custom_field[0];
	    $custom_field[0] = $field_name;
	    $custom_field_glued = implode(':', $custom_field);
	    $custom_fields_array[$index] = $custom_field_glued;
	    $c_field = implode('%s%', $custom_fields_array);
	    
	    //check autoresponders
	    $q3 = 'SELECT id FROM ares WHERE custom_field = "'.$custom_field_old.'" AND list = '.$list_id;
	    $r3 = mysqli_query($mysqli, $q3);
	    if (mysqli_num_rows($r3) > 0)
	    {
	    	while($row = mysqli_fetch_array($r3)) $ares_id = $row['id'];
		    
	        $q4 = 'UPDATE ares SET custom_field = "'.$field_name.'" WHERE id = '.$ares_id;
	        $r4 = mysqli_query($mysqli, $q4);
	        if ($r4){}
	    }
	    
	    //update custom_fields column
	    $q2 = 'UPDATE lists SET custom_fields = "'.$c_field.'" WHERE id = '.$list_id;
	    $r2 = mysqli_query($mysqli, $q2);
	    if ($r2)
	    	header("Location: ".get_app_info('path')."/custom-fields?i=$app&l=$list_id");
	}
?>