<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$index = $_POST['index'];
	$list_id = mysqli_real_escape_string($mysqli, $_POST['list']);
	
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
	    $custom_fields_value = explode(':', $custom_fields_array[$index]);
	    
	    //check if any autoresponder is using this custom field, if so, throw an error
	    $q4 = 'SELECT id FROM ares WHERE custom_field = "'.$custom_fields_value[0].'" AND list = '.$list_id;
	    $r4 = mysqli_query($mysqli, $q4);
	    if (mysqli_num_rows($r4) > 0)
	    {
	    	echo 'ares_used';
	    	exit;
	    }
	    
	    unset($custom_fields_array[$index]);
	    $c_field = implode('%s%', $custom_fields_array);
	    	
	    //update custom_fields column
	    $q2 = 'UPDATE lists SET custom_fields = "'.$c_field.'" WHERE id = '.$list_id;
	    $r2 = mysqli_query($mysqli, $q2);
	    if ($r2){}
	}
	
	//delete data from subscribers	
	$q3 = 'SELECT id, custom_fields FROM subscribers WHERE list = '.$list_id;
	$r3 = mysqli_query($mysqli, $q3);
	if ($r3)
	{
	    while($row = mysqli_fetch_array($r3))
	    {
	    	//reset array and vars
			unset($custom_fields_array);
			$c_field = '';
			
			//retrieved from database
			$s_id = $row['id'];
			$custom_fields = $row['custom_fields'];
			
			//delete element from array
		    $custom_fields_array = explode('%s%', $custom_fields);
		    unset($custom_fields_array[$index]);
		    $c_field = implode('%s%', $custom_fields_array);
		    	
		    //update custom_fields column
		    $q4 = 'UPDATE subscribers SET custom_fields = "'.$c_field.'" WHERE list = '.$list_id.' AND id = '.$s_id;
		    $r4 = mysqli_query($mysqli, $q4);
	    }  
	}
	
	echo true;
?>