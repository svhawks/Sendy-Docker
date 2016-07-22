<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$app = mysqli_real_escape_string($mysqli, $_POST['id']);
	$list_id = mysqli_real_escape_string($mysqli, $_POST['list']);
	
	//the custom field
	$the_field = $_POST['c_field'];
	$type = $_POST['c_type'];
	
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
	    
	    //check if custom field exists
	    $custom_fields_array = explode('%s%', $custom_fields);
	    foreach($custom_fields_array as $cf)
	    {
		    $cf_array = explode(':', $cf);
		    if(strtolower($cf_array[0])==strtolower($the_field) || strtolower($the_field)=='name' || strtolower($the_field)=='email')
		    {
			    header("Location: ".get_app_info('path')."/custom-fields?i=$app&l=$list_id&e=1");
			    exit;
		    }
	    }
	    
	    //if nothing is in the custom_fields, just add it to column
	    if($custom_fields == '')
	    	$c_field = $the_field.':'.$type;
	    else
	    {
		    $c_field = $custom_fields.'%s%'.$the_field.':'.$type;
	    }
	    //update custom_fields column
	    $q2 = 'UPDATE lists SET custom_fields = "'.$c_field.'" WHERE id = '.$list_id;
	    $r2 = mysqli_query($mysqli, $q2);
	    if ($r2){}
	    
	    //if nothing is in the custom_fields, just add it to column
	    if($custom_fields == '')
	    	$c_field2 = '';
	    else
	    {	    	
	    	$q3 = 'SELECT id, custom_fields FROM subscribers WHERE list = '.$list_id;
	    	$r3 = mysqli_query($mysqli, $q3);
	    	if ($r3)
	    	{
	    	    while($row = mysqli_fetch_array($r3))
	    	    {
	    	    	//retrieved from database
	    			$s_id = $row['id'];
	    			$custom_fields = $row['custom_fields'];
	    			$c_field2 = $custom_fields.'%s%';
	    			
	    			//update custom_fields column
				    $q4 = 'UPDATE subscribers SET custom_fields = "'.$c_field2.'" WHERE list = '.$list_id.' AND id = '.$s_id;
				    $r4 = mysqli_query($mysqli, $q4);
	    	    }  
	    	}
	    }
	}
	
	header("Location: ".get_app_info('path')."/custom-fields?i=$app&l=$list_id");
?>