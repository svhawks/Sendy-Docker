<?php 
	//================= Version 1.0.1 =================//
	//New column in table: campaigns, named wysiwyg
	//=================================================//
	$q = "SHOW COLUMNS FROM campaigns WHERE Field = 'wysiwyg'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table campaigns add column wysiwyg INT (11) DEFAULT \'0\'';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){
		    $q = 'UPDATE campaigns SET wysiwyg=0';
		    $r = mysqli_query($mysqli, $q);
		    if ($r){}
	    }
	}
	//================= Version 1.0.3 =================//
	//New column in table: login, named tied_to & app
	//=================================================//
	$q = "SHOW COLUMNS FROM login WHERE Field = 'tied_to' || Field = 'app' || Field = 'paypal'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table login add (tied_to INT (11), app INT (11), paypal VARCHAR (100))';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	//-------------------------------------------------//
	//New column in table: apps, named currency, delivery_fee & cost_per_recipient
	//-------------------------------------------------//
	$q = "SHOW COLUMNS FROM apps WHERE Field = 'currency' || Field = 'delivery_fee' || Field = 'cost_per_recipient'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table apps add (currency VARCHAR (100), delivery_fee VARCHAR (100), cost_per_recipient VARCHAR (100))';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	//================= Version 1.0.4 =================//
	//New column in table: campaigns, named send_date, lists & timezone
	//=================================================//
	$q = "SHOW COLUMNS FROM campaigns WHERE Field = 'send_date' || Field = 'lists' || Field = 'timezone'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table campaigns add (send_date VARCHAR (100), lists VARCHAR (100), timezone VARCHAR (100))';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	//-------------------------------------------------//
	//New column in table: login, named, cron
	//-------------------------------------------------//
	$q = "SHOW COLUMNS FROM login WHERE Field = 'cron'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table login add (cron INT (11) default 0)';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	//================= Version 1.0.5 =================//
	//New column in table: lists, named opt_in, subscribed_url, unsubscribed_url, thankyou, thankyou_message, goodbye, goodbye_message, unsubscribe_all_list
	//=================================================//
	$q = "SHOW COLUMNS FROM lists WHERE Field = 'opt_in' || Field = 'subscribed_url' || Field = 'unsubscribed_url' || Field = 'thankyou' || Field = 'thankyou_subject' || Field = 'thankyou_message' || Field = 'goodbye' || Field = 'goodbye_subject' || Field = 'goodbye_message' || Field = 'unsubscribe_all_list'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table lists add (opt_in INT (11) DEFAULT \'0\', subscribed_url VARCHAR (100), unsubscribed_url VARCHAR (100), thankyou int (11) DEFAULT \'0\', thankyou_subject VARCHAR(100), thankyou_message MEDIUMTEXT, goodbye INT (11) DEFAULT \'0\', goodbye_subject VARCHAR(100), goodbye_message MEDIUMTEXT, unsubscribe_all_list INT (11) DEFAULT \'1\')';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	//-------------------------------------------------//
	//New column in table: subscribers, named, confirmed
	//-------------------------------------------------//
	$q = "SHOW COLUMNS FROM subscribers WHERE Field = 'confirmed'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table subscribers add (confirmed INT (11) default 1)';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	
	//================= Version 1.0.6 =================//
	//New column in table: campaigns, to_send, to_send_lists
	//=================================================//
	$q = "SHOW COLUMNS FROM campaigns WHERE Field = 'to_send' || Field = 'to_send_lists'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table campaigns ADD COLUMN to_send INT (100) AFTER sent';
	    $q2 = 'alter table campaigns ADD COLUMN to_send_lists VARCHAR (100) AFTER to_send';
	    $r = mysqli_query($mysqli, $q);
	    $r2 = mysqli_query($mysqli, $q2);
	    if ($r && $r2){}
	}
	//-------------------------------------------------//
	//New column in table: confirm_url
	//-------------------------------------------------//
	$q = "SHOW COLUMNS FROM lists WHERE Field = 'confirm_url'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table lists ADD COLUMN confirm_url VARCHAR (100) AFTER opt_in';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	
	//================= Version 1.0.7 =================//
	//New column in table: subscribers, named, complaint
	//=================================================//
	$q = "SHOW COLUMNS FROM subscribers WHERE Field = 'complaint'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table subscribers ADD COLUMN complaint INT (11) DEFAULT \'0\' AFTER bounced';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	
	//================= Version 1.0.8 =================//
	//New column in table: lists & subscribers, custom_fields & custom_fields
	//=================================================//
	$q = "SHOW COLUMNS FROM lists WHERE Field = 'custom_fields'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table lists ADD COLUMN custom_fields MEDIUMTEXT';
	    $q2 = 'alter table subscribers ADD COLUMN custom_fields LONGTEXT AFTER email';
	    $q3 = 'alter table subscribers ADD COLUMN join_date INT (100) AFTER timestamp';
	    $r = mysqli_query($mysqli, $q);
	    $r2 = mysqli_query($mysqli, $q2);
	    $r3 = mysqli_query($mysqli, $q3);
	    if ($r && $r2 && $r3){}
	}
	
	//================= Version 1.0.9 =================//
	//New columns in table: subscribers, login, links and new autoresponders tables
	//=================================================//
	$q = "SHOW COLUMNS FROM subscribers WHERE Field = 'bounce_soft'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table subscribers ADD COLUMN bounce_soft INT (11) DEFAULT \'0\' AFTER bounced, ADD COLUMN last_ares INT (11) AFTER last_campaign';
	    $q2 = 'alter table login ADD COLUMN cron_ares INT (11) DEFAULT \'0\' AFTER cron';
	    $q3 = 'alter table links ADD COLUMN ares_emails_id INT (11) AFTER campaign_id';
	    $q4 = 'CREATE TABLE `ares` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) DEFAULT NULL,
		  `type` int(11) DEFAULT NULL,
		  `list` int(11) DEFAULT NULL,
		  `custom_field` varchar(100) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;';
		$q5 = 'CREATE TABLE `ares_emails` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `ares_id` int(11) DEFAULT NULL,
		  `from_name` varchar(100) DEFAULT NULL,
		  `from_email` varchar(100) DEFAULT NULL,
		  `reply_to` varchar(100) DEFAULT NULL,
		  `title` varchar(500) DEFAULT NULL,
		  `plain_text` mediumtext,
		  `html_text` mediumtext,
		  `time_condition` varchar(100) DEFAULT NULL,
		  `timezone` varchar(100) DEFAULT NULL,
		  `created` int(11) DEFAULT NULL,
		  `recipients` int(100) DEFAULT 0,
		  `opens` longtext,
		  `wysiwyg` int(11) DEFAULT 0,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;';
	    $r = mysqli_query($mysqli, $q);
	    $r2 = mysqli_query($mysqli, $q2);
	    $r3 = mysqli_query($mysqli, $q3);
	    $r4 = mysqli_query($mysqli, $q4);
	    $r5 = mysqli_query($mysqli, $q5);
	    if ($r && $r2 && $r3 && $r4 && $r5){}
	}
	
	//================= Version 1.1.0 =================//
	//New columns in table: login and new table, queue etc
	//=================================================//
	$q = "SHOW COLUMNS FROM login WHERE Field = 'send_rate'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table login ADD COLUMN send_rate INT (100) DEFAULT 0';
	    $q2 = 'alter table login ADD COLUMN timezone VARCHAR (100)';
	    $q3 = 'alter table apps ADD (smtp_host VARCHAR (100), smtp_port VARCHAR (100), smtp_ssl VARCHAR (100), smtp_username VARCHAR (100), smtp_password VARCHAR (100))';
	    $q4 = 'alter table campaigns ADD COLUMN timeout_check VARCHAR (100) AFTER recipients';
	    $r = mysqli_query($mysqli, $q);
	    $r2 = mysqli_query($mysqli, $q2);
	    $r3 = mysqli_query($mysqli, $q3);
	    $r4 = mysqli_query($mysqli, $q4);
	    $r5 = mysqli_query($mysqli, $q4);
	    if ($r && $r2 && $r3 && $r4){}
	    
	    $q = 'SELECT timezone FROM login LIMIT 1';
	    $r = mysqli_query($mysqli, $q);
	    if ($r && mysqli_num_rows($r) > 0)
	    {
	        while($row = mysqli_fetch_array($r))
	        {
	    		if($row['timezone']=='')
	    		{
		    		$q2 = 'UPDATE login SET timezone = "America/New_York" LIMIT 1';
				    mysqli_query($mysqli, $q2);
	    		}
	        }  
	    }
	}
	
	$q = 'SHOW TABLES LIKE "queue"';
    $r = mysqli_query($mysqli, $q);
    if (mysqli_num_rows($r) == 0)
    {
    	$q2 = 'CREATE TABLE `queue` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `query_str` longtext,
		  `campaign_id` int(11) DEFAULT NULL,
		  `subscriber_id` int(11) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
		mysqli_query($mysqli, $q2);
    }
    
    //================= Version 1.1.2 =================//
	//New column in table: subscribers
	//=================================================//
	$q = "SHOW COLUMNS FROM campaigns WHERE Field = 'errors'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table campaigns ADD COLUMN errors LONGTEXT';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	
	//================= Version 1.1.3 =================//
	//New column in table: subscribers
	//=================================================//
	$q = "SHOW COLUMNS FROM queue WHERE Field = 'sent'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table queue ADD COLUMN sent INT (11) DEFAULT 0';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	
	//================= Version 1.1.4 =================//
	//New column in table: subscribers
	//=================================================//
	$q = "SHOW COLUMNS FROM lists WHERE Field = 'confirmation_email'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table lists ADD COLUMN confirmation_email MEDIUMTEXT after goodbye_message';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	$q = "SHOW COLUMNS FROM lists WHERE Field = 'confirmation_subject'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table lists ADD COLUMN confirmation_subject MEDIUMTEXT after goodbye_message';
	    $r = mysqli_query($mysqli, $q);
	    if ($r){}
	}
	
	//================= Version 1.1.5 =================//
	//New column in table: subscribers
	//=================================================//
	$q = "SHOW COLUMNS FROM campaigns WHERE Field = 'bounce_setup'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q = 'alter table login ADD language VARCHAR (100) DEFAULT "en_US", ADD cron_csv INT (11) DEFAULT 0';
	    $q2 = 'alter table lists ADD prev_count INT (100) DEFAULT 0 after custom_fields, ADD currently_processing INT (100) DEFAULT 0, ADD total_records INT (100) DEFAULT 0';
	    $q3 = 'alter table apps ADD bounce_setup INT (11) DEFAULT 0, ADD complaint_setup INT (11) DEFAULT 0';
	    $q4 = 'alter table campaigns ADD bounce_setup INT (11) DEFAULT 0, ADD complaint_setup INT (11) DEFAULT 0';
	    mysqli_query($mysqli, $q);
	    mysqli_query($mysqli, $q2);
	    mysqli_query($mysqli, $q3);
	    mysqli_query($mysqli, $q4);
	}
	//add index to list column in subscribers table
	$q = 'SHOW INDEX FROM subscribers WHERE KEY_NAME = "s_list"';
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
		mysqli_query($mysqli, 'CREATE INDEX s_list ON subscribers (list)');
		mysqli_query($mysqli, 'CREATE INDEX s_unsubscribed ON subscribers (unsubscribed)');
		mysqli_query($mysqli, 'CREATE INDEX s_bounced ON subscribers (bounced)');
		mysqli_query($mysqli, 'CREATE INDEX s_bounce_soft ON subscribers (bounce_soft)');
		mysqli_query($mysqli, 'CREATE INDEX s_complaint ON subscribers (complaint)');
		mysqli_query($mysqli, 'CREATE INDEX s_confirmed ON subscribers (confirmed)');
		mysqli_query($mysqli, 'CREATE INDEX s_timestamp ON subscribers (timestamp)');
		mysqli_query($mysqli, 'CREATE INDEX s_id ON queue (subscriber_id)');
		mysqli_query($mysqli, 'CREATE INDEX st_id ON queue (sent)');
	}
	
	//================= Version 1.1.7 =================//
	//New column in table: apps
	//=================================================//
	$q = "SHOW COLUMNS FROM apps WHERE Field = 'app_key'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $q3 = 'alter table apps ADD COLUMN app_key VARCHAR (100)';
	    $r3 = mysqli_query($mysqli, $q3);
	    if ($r3)
	    {
		    $q4 = 'SELECT id FROM apps';
		    $r4 = mysqli_query($mysqli, $q4);
		    if (mysqli_num_rows($r4) > 0)
		    {
		        while($row = mysqli_fetch_array($r4))
		        {
		        	$cid = $row['id'];
		        	
		    		$q5 = 'UPDATE apps SET app_key = "'.ran_string(30, 30, true, false, true).'" WHERE id = '.$cid;
		    		mysqli_query($mysqli, $q5);
		        }  
		    }
	    }
	}
	
	//================= Version 1.1.7.2 ===============//
	//New index in table: subscribers (email column)
	//=================================================//
	//add index to email column in subscribers table
	$q = 'SHOW INDEX FROM subscribers WHERE KEY_NAME = "s_email"';
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
		mysqli_query($mysqli, 'CREATE INDEX s_email ON subscribers (email)');
	}
	
	//================= Version 1.1.8 ===============//
	//New column in table: login
	//=================================================//
	//Create new 'ses_endpoint' in 'login' table
	$q = "SHOW COLUMNS FROM login WHERE Field = 'ses_endpoint'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    $r2 = mysqli_query($mysqli, 'alter table login ADD COLUMN ses_endpoint VARCHAR (100)');
	    if($r2)
	    {
		    $q3 = 'UPDATE login SET ses_endpoint = "email.us-east-1.amazonaws.com" LIMIT 1';
		    mysqli_query($mysqli, $q3);
	    }
	}
	
	//================= Version 1.1.7.3 ===============//
	//Convert to_send_lists and lists columns to TEXT type
	//=================================================//
	//add index to email column in subscribers table
	$q = 'SHOW COLUMNS FROM campaigns WHERE Field = "to_send_lists" AND Type = "VARCHAR(100)"';
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 1)
	{
		mysqli_query($mysqli, 'ALTER TABLE campaigns MODIFY COLUMN to_send_lists TEXT');
		mysqli_query($mysqli, 'ALTER TABLE campaigns MODIFY COLUMN lists TEXT');
	}
	
	//================= Version 1.1.9 ===============//
	//New column in table: login
	//=================================================//
	//Create new 'ses_endpoint' in 'login' table
	$q = "SHOW COLUMNS FROM apps WHERE Field = 'allocated_quota'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    mysqli_query($mysqli, 'alter table apps ADD COLUMN allocated_quota INT (11) DEFAULT -1');
	    mysqli_query($mysqli, 'alter table apps ADD COLUMN current_quota INT (11) DEFAULT 0');
	    mysqli_query($mysqli, 'alter table apps ADD COLUMN day_of_reset INT (11) DEFAULT 1');
	    mysqli_query($mysqli, 'alter table apps ADD COLUMN month_of_next_reset VARCHAR (3)');
	}
	
	//================= Version 1.1.9.1 ===============//
	//New column in table: apps
	//=================================================//
	//Create new 'test_email' in 'login' table
	$q = "SHOW COLUMNS FROM apps WHERE Field = 'test_email'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    mysqli_query($mysqli, 'alter table apps ADD COLUMN test_email VARCHAR (100)');
	}
	
	//================= Version 1.1.9.4 ===============//
	//New column in table: subscribers
	//=================================================//
	//Create new 'test_email' in 'login' table
	$q = "SHOW COLUMNS FROM subscribers WHERE Field = 'messageID'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    mysqli_query($mysqli, 'alter table subscribers ADD COLUMN messageID VARCHAR (100)');
	}
	
	//================= Version 2.0 ===================//
	//New table in database: template
	//=================================================//
	//Create new 'template' table in database
	$q = "CREATE TABLE IF NOT EXISTS `template` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `userID` int(11) DEFAULT NULL,
	  `app` int(11) DEFAULT NULL,
	  `template_name` varchar(100) DEFAULT NULL,
	  `html_text` mediumtext,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";
	mysqli_query($mysqli, $q);
	
	//Create new 'query_string' in 'campaigns' table
	$q = "SHOW COLUMNS FROM campaigns WHERE Field = 'query_string'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    mysqli_query($mysqli, 'alter table campaigns ADD COLUMN query_string VARCHAR (500) AFTER html_text, ADD COLUMN label VARCHAR (500) AFTER title');
	    mysqli_query($mysqli, 'alter table ares_emails ADD COLUMN query_string VARCHAR (500) AFTER html_text');
	    mysqli_query($mysqli, 'alter table apps ADD COLUMN brand_logo_filename VARCHAR (100)');
	}
	
	//================= Version 2.0.6 ===============//
	//New column in table: apps
	//=================================================//
	//Create new 'allowed_attachments' in 'apps' table
	$q = "SHOW COLUMNS FROM apps WHERE Field = 'allowed_attachments'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    mysqli_query($mysqli, 'alter table apps ADD COLUMN allowed_attachments VARCHAR (100) DEFAULT "jpeg,jpg,gif,png,pdf,zip"');
	}
	
	//================= Version 2.0.8 ===============//
	//New INDEX in table: subscribers and increase VARCHAR to 1500 in `link` column of `links` table
	//=================================================//
	//Create new 'allowed_attachments' in 'apps' table
	$q = "SHOW INDEX FROM subscribers WHERE Key_name = 's_last_campaign'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    mysqli_query($mysqli, 'ALTER TABLE subscribers ADD INDEX s_last_campaign (last_campaign DESC)');
	    mysqli_query($mysqli, 'ALTER TABLE links modify link VARCHAR(1500)');
	    mysqli_query($mysqli, 'ALTER TABLE apps CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
		mysqli_query($mysqli, 'ALTER TABLE ares_emails CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
		mysqli_query($mysqli, 'ALTER TABLE campaigns CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
		mysqli_query($mysqli, 'ALTER TABLE lists CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
		mysqli_query($mysqli, 'ALTER TABLE subscribers CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
		mysqli_query($mysqli, 'ALTER TABLE template CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
	}
	
	//================= Version 2.1.0 ===============//
	//New column in table: auth_enabled, auth_key
	//=================================================//
	//Create new 'allowed_attachments' in 'apps' table
	$q = "SHOW COLUMNS FROM login WHERE Field = 'auth_enabled'";
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) == 0)
	{
	    mysqli_query($mysqli, 'alter table login ADD COLUMN auth_enabled INT (11) DEFAULT 0');
	    mysqli_query($mysqli, 'alter table login ADD COLUMN auth_key VARCHAR (100)');
	}
?>