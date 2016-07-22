<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 

/********************************/
$table = 'subscribers'; // table to export
$userID = get_app_info('main_userID');
$ares_id = mysqli_real_escape_string($mysqli, $_GET['c']);
$action = $_GET['a'];
$additional_query = '';
/********************************/

if($action == 'clicks')
{
	//file name
	$filename = 'clicked.csv';
	$additional_query = 'AND unsubscribed = 0 AND bounced = 0 AND complaint = 0';
	
	//get
	$clicks_join = '';
	$clicks_array = array();
	$clicks_unique = 0;
	
	$q = 'SELECT * FROM links WHERE ares_emails_id = '.$ares_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
	    	$id = stripslashes($row['id']);
			$clicks = stripslashes($row['clicks']);
			if($clicks!='')
				$clicks_join .= $clicks.',';				
	    }  
	}
	
	$clicks_array = explode(',', $clicks_join);
	$clicks_unique = array_unique($clicks_array);
	$subscribers = substr(implode(',', $clicks_unique), 0, -1);
}
else if($action == 'opens')
{
	//file name
	$filename = 'opened.csv';
	$additional_query = 'AND unsubscribed = 0 AND bounced = 0 AND complaint = 0';
	
	$q = 'SELECT * FROM ares_emails WHERE id = '.$ares_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
  			$opens = stripslashes($row['opens']);
  			
  			$opens_array = explode(',', $opens);
  			$opens_array_no_country = array();
  			foreach($opens_array as $opens_array_nc)
  			{
  				$e = explode(':', $opens_array_nc);
	  			array_push($opens_array_no_country, $e[0]);
  			}
  			
  			$opens_unique = array_unique($opens_array_no_country);
	  		$subscribers = implode(',', $opens_unique);
	    }  
	}
}
else if($action == 'unsubscribes')
{
	//file name
	$filename = 'unsubscribed.csv';
	
	$q = 'SELECT * FROM subscribers WHERE last_ares = '.$ares_id.' AND unsubscribed = 1';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$unsubscribes_array = array();
	    while($row = mysqli_fetch_array($r))
	    {
  			$unsubscriber_id = $row['id'];
  			array_push($unsubscribes_array, $unsubscriber_id);
	    }  
	    
	    $subscribers = implode(',', $unsubscribes_array);
	}
}
else if($action == 'bounces')
{
	//file name
	$filename = 'bounced.csv';
	
	$q = 'SELECT * FROM subscribers WHERE last_ares = '.$ares_id.' AND bounced = 1';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$unsubscribes_array = array();
	    while($row = mysqli_fetch_array($r))
	    {
  			$unsubscriber_id = $row['id'];
  			array_push($unsubscribes_array, $unsubscriber_id);
	    }  
	    
	    $subscribers = implode(',', $unsubscribes_array);
	}
}
else if($action == 'complaints')
{
	//file name
	$filename = 'marked-as-spam.csv';
	
	$q = 'SELECT * FROM subscribers WHERE last_ares = '.$ares_id.' AND complaint = 1';
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		$unsubscribes_array = array();
	    while($row = mysqli_fetch_array($r))
	    {
  			$unsubscriber_id = $row['id'];
  			array_push($unsubscribes_array, $unsubscriber_id);
	    }  
	    
	    $subscribers = implode(',', $unsubscribes_array);
	}
}
else
{
	//file name
	$filename = $action.'.csv';
	
	$q = 'SELECT * FROM ares_emails WHERE id = '.$ares_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
  			$opens = stripslashes($row['opens']);
  			
  			$opens_array = explode(',', $opens);
  			$opens_array_ids = array();
  			$opens_array_country_match = array();
  			
  			foreach($opens_array as $opens_array_nc)
  			{
  				$e = explode(':', $opens_array_nc);
	  			array_push($opens_array_ids, $e[0]);
  			}
  			
  			foreach($opens_array as $opens_array_cts)
  			{
	  			$f = explode(':', $opens_array_cts);
	  			if($f[1]==$action)
	  				array_push($opens_array_country_match, $f[0]);
  			}
  			
  			$opens_unique = array_unique($opens_array_country_match);
	  		$subscribers = implode(',', $opens_unique);
	    }  
	}
}

//Export
$select = 'SELECT name, email FROM '.$table.' where id IN ('.$subscribers.') '.$additional_query;
$export = mysqli_query($mysqli, $select);
$fields = mysqli_num_fields ( $export );
while( $row = mysqli_fetch_row( $export ) )
{
    $line = '';
    $i = 1;
    foreach( $row as $value )
    {                                            
        if ( ( !isset( $value ) ) || ( $value == "" ) )
        {
        	if($i<$fields)
	            $value = ",";
        }
        else
        {
            $value = str_replace( '"' , '""' , $value );
            if($i<$fields)
	            $value = $value . ",";
        }
        $line .= $value;
        $i++;
    }
    $data .= trim( $line ) . "\n";
}
$data = str_replace( "\r" , "" , $data );

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