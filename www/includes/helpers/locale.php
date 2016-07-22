<?php 
	//Include library
	require_once('gettext/gettext.inc');
	
	//--------------------------------------------------------------//
	function set_locale($locale)
	//--------------------------------------------------------------//
	{
		//Set language
		putenv("LC_ALL=$locale");
		T_setlocale(LC_MESSAGES, $locale);
		T_bindtextdomain('default', 'locale');
		T_bind_textdomain_codeset('default', 'UTF-8');
		T_textdomain('default');
	}
?>