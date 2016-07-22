<?php include('includes/functions.php');?>
<?php if(isset($_COOKIE['logged_in'])){start_app();}?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		<meta name="robots" content="noindex, nofollow">
		<link rel="Shortcut Icon" type="image/ico" href="<?php echo get_app_info('path');?>/img/favicon.png">
		<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path');?>/css/bootstrap.css?3" />
		<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path');?>/css/bootstrap-responsive.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path');?>/css/responsive-tables.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path');?>/css/font-awesome.min.css" />
		<link rel="apple-touch-icon-precomposed" href="<?php echo get_app_info('path');?>/img/sendy-icon.png" />
		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
	    <!--[if lt IE 9]>
	      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	    <![endif]-->
		<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path');?>/css/all.css?7" />
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/jquery-migrate-1.1.0.min.js"></script>
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/jquery-ui-1.8.21.custom.min.js"></script>
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/bootstrap.js"></script>
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/responsive-tables.js"></script>
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/main.js?2"></script>
		<link href='https://fonts.googleapis.com/css?family=Roboto:400,400italic,700,700italic' rel='stylesheet' type='text/css'>
		<title><?php echo get_app_info('company');?></title>
	</head>
	<body>
		<div class="navbar navbar-fixed-top">
		  <div class="separator"></div>
	      <div class="navbar-inner">
	        <div class="container-fluid">
	          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	          </a>
	          	          
	          <!-- Check if sub user -->
	          <?php if(!get_app_info('is_sub_user')):?>
	          <a class="brand" href="<?php echo get_app_info('path');?>/"><img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim(get_app_info('email'))));?>?s=36&d=<?php echo get_app_info('path');?>/img/sendy-avatar.png" title="" class="main-gravatar" onerror="this.src='<?php echo get_app_info('path');?>/img/sendy-avatar.png'"/><?php echo get_app_info('company');?></a>
	          <?php else:?>
	          <?php 
		          $q = 'SELECT brand_logo_filename FROM apps WHERE id = '.get_app_info('app');
		          $r = mysqli_query($mysqli, $q);
		          if ($r) while($row = mysqli_fetch_array($r)) $logo_filename = $row['brand_logo_filename'];  
		          if($logo_filename=='') $logo_image = 'https://www.gravatar.com/avatar/'.md5(strtolower(trim(get_app_info('email')))).'?s=36&d='.get_app_info('path').'/img/sendy-avatar.png';
		          else $logo_image = get_app_info('path').'/uploads/logos/'.$logo_filename;
	          ?>
	          <a class="brand" href="<?php echo get_app_info('path');?>/app?i=<?php echo get_app_info('restricted_to_app');?>"><img src="<?php echo $logo_image;?>" title="" class="main-gravatar"/><?php echo get_app_info('company');?></a>
	          <?php endif;?>
	          
	          <?php if(currentPage()!='login.php' && currentPage()!='two-factor.php' && currentPage()!='_install.php'): ?>
	          <div class="btn-group pull-right">
	            <a class="btn btn-inverse dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)">
	              <i class="icon-user icon-white"></i> <?php echo get_app_info('name');?>
	              <span class="caret"></span>
	            </a>
	            <ul class="dropdown-menu">
	              <li><a href="<?php echo get_app_info('path');?>/settings<?php if(get_app_info('is_sub_user')) echo '?i='.get_app_info('app');?>"><i class="icon icon-cog"></i> <?php echo _('Settings');?></a></li>
	              <li class="divider"></li>
	              <li><a href="<?php echo get_app_info('path');?>/logout"><i class="icon icon-off"></i> <?php echo _('Logout');?></a></li>
	            </ul>
	          </div>
	          
	          
	          <!-- Check if sub user -->
	          <?php if(!get_app_info('is_sub_user')):?>	          
	          <div class="btn-group pull-right">
				  <a class="btn btn-white dropdown-toggle" data-toggle="dropdown" href="#">
				    <?php 
				    	$get_i = isset($_GET['i']) ? mysqli_real_escape_string($mysqli, (int) $_GET['i']) : '';
				    	
					    $q = "SELECT app_name, from_email, brand_logo_filename FROM apps WHERE id = '$get_i'";
					    $r = mysqli_query($mysqli, $q);
					    if ($r && mysqli_num_rows($r) > 0)
					    {
					        while($row = mysqli_fetch_array($r))
					        {
					        	$from_email = explode('@', $row['from_email']);
					  			$get_domain = $from_email[1];
					  			$brand_logo_filename = $row['brand_logo_filename'];
			  			
					  			//Brand logo
					  			if($brand_logo_filename=='') $logo_image = 'https://www.google.com/s2/favicons?domain='.$get_domain;
					  			else $logo_image = get_app_info('path').'/uploads/logos/'.$brand_logo_filename;
					  			
					    		echo '<img src="'.$logo_image.'" style="margin:-4px 5px 0 0; width:16px; height: 16px;"/>'.$row['app_name'];
					        }  
					    }
					    else
					    	echo '<span class="icon icon-th-list"></span> '._('Brands');
				    ?>
				    <span class="caret"></span>
				  </a>
				  <ul class="dropdown-menu">
				  	<?php 
		              $q = 'SELECT id, app_name, from_email, brand_logo_filename FROM apps WHERE userID = '.get_app_info('userID').' ORDER BY app_name ASC';
		              $r = mysqli_query($mysqli, $q);
		              if ($r && mysqli_num_rows($r) > 0)
		              {
		                  while($row = mysqli_fetch_array($r))
		                  {
		                  	$app_id = $row['id'];
		              		$app_name = $row['app_name'];
		              		$from_email = explode('@', $row['from_email']);
				  			$get_domain = $from_email[1];
				  			$brand_logo_filename = $row['brand_logo_filename'];
				  						  			
				  			//Brand logo
				  			if($brand_logo_filename=='') $logo_image = 'https://www.google.com/s2/favicons?domain='.$get_domain;
				  			else $logo_image = get_app_info('path').'/uploads/logos/'.$brand_logo_filename;
				  			
		              		echo '<li';
		              		if($get_i==$app_id)
		              			echo ' class="active"';
		              		echo'><a href="'.get_app_info('path').'/app?i='.$app_id.'"><img src="'.$logo_image.'" style="margin:-4px 5px 0 0; width:16px; height: 16px;"/>'.$app_name.'</a></li>';
		                  }  
		              }
		              else
		              {
			              echo '<li><a href="'.get_app_info('path').'/new-brand" title="">'._('Add a new brand').'</a></li>';
		              }
		            ?>
				  </ul>
				</div>
				<?php endif;?>
				
				
	          <div class="nav-collapse">
	            <ul class="nav">
	              
	            </ul>
	          </div><!--/.nav-collapse -->
	          
	          
	          
	          <?php endif;?>
	          
	        </div>
	      </div>
	    </div>
	    <div class="container-fluid">
	    <?php ini_set('display_errors', 0);?>