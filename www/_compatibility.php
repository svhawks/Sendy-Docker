<?php 	
	ini_set('display_errors', 0);
	define(PHP_VER, '5.2');
	define(TOTAL_SCORE, '9');
	$result = array();
	$score = 0;

	//check PHP version
	if(version_compare(PHP_VERSION, PHP_VER)==-1)
		$result[] = '<span class="label label-important"><i class="icon-remove icon-white"></i> Sendy requires PHP '.PHP_VER.' to run, your have '.PHP_VERSION.'</span>';
	else
	{
		$result[] = '<span class="label label-success"><i class="icon-ok icon-white"></i> Your PHP version is '.PHP_VERSION.'</span>';
		$score++;
	}
	
	//check if mysqli extension is installed
	if (function_exists("mysqli_connect")) {
		$result[] = '<span class="label label-success"><i class="icon-ok icon-white"></i> mysqli extension is installed</span>';
		$score++;
	}
	else
		$result[] = '<span class="label label-important"><i class="icon-remove icon-white"></i> mysqli extension is not installed</span>';
		
	//check mod_rewrite
	if (function_exists("apache_get_modules")) {
		$modules = apache_get_modules();
		$mod_rewrite = in_array("mod_rewrite",$modules);
		if($mod_rewrite)
		{
			$result[] = '<span class="label label-success"><i class="icon-ok icon-white"></i> mod_rewrite is enabled</span>';
			$score++;
		}
		else
			$result[] = '<span class="label label-warning"><i class="icon-remove icon-white"></i> mod_rewrite is not enabled</span>';
	}
	else
		$result[] = '<span class="label label-warning"><i class="icon-remove icon-white"></i> mod_rewrite is not enabled</span>';
		
	//check if display_errors is on
	if(ini_get('display_errors'))
		$result[] = '<span class="label label-important"><i class="icon-remove icon-white"></i> display_errors is turned on</span>';
	else
	{
		$result[] = '<span class="label label-success"><i class="icon-ok icon-white"></i> display_errors is turned off</span>';
		$score++;
	}
		
	//check
	$exts = array('hash', 'curl', 'gettext');
	foreach($exts as $ext) {
		if(extension_loaded($ext))
		{
			$result[] = '<span class="label label-success"><i class="icon-ok icon-white"></i> '.$ext.' is enabled</span>';
			$score++;
		}
		else
			$result[] = '<span class="label label-important"><i class="icon-remove icon-white"></i> '.$ext.' is not enabled</span>';
	}
	
	//check if curl_exec is enabled
	function curl_exec_enabled()
	{
		$disabled = explode(',', ini_get('disable_functions'));
		if(in_array('curl_exec', $disabled)) return false;
		else return true;
	}
	if(curl_exec_enabled())
	{
		$result[] = '<span class="label label-success"><i class="icon-ok icon-white"></i> curl_exec is enabled</span>';
		$score++;
	}
	else $result[] = '<span class="label label-important"><i class="icon-remove icon-white"></i> curl_exec is disabled</span>';
	
	//check if curl_multi_exec is enabled
	function curl_multi_exec_enabled()
	{
		$disabled = explode(',', ini_get('disable_functions'));
		if(in_array('curl_multi_exec', $disabled)) return false;
		else return true;
	}
	if(curl_multi_exec_enabled())
	{
		$result[] = '<span class="label label-success"><i class="icon-ok icon-white"></i> curl_multi_exec is enabled</span>';
		$score++;
	}
	else $result[] = '<span class="label label-important"><i class="icon-remove icon-white"></i> curl_multi_exec is disabled</span>';
	
	if($_GET['i']==1)
	{
		echo '<h2>Server configuration:</h2><hr/>';
		//return results
		foreach($result as $results){
			echo $results.'<br/>';
		}
		echo '<br/>Score: '.$score.'/'.TOTAL_SCORE;
	}
?>