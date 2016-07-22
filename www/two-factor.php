<?php 
	//Exit this page if user is already logged in, or not logged in yet
	session_start();
	if(isset($_COOKIE['logged_in']) || !isset($_SESSION['cookie'])) 
	{
		header("Location: .");
		exit;
	}
?>
<?php include('includes/header.php');?>
<?php
	//Redirection
	if(isset($_GET['redirect'])) 
	{
		$redirect_array = explode('redirect=', $_SERVER['REQUEST_URI']);
		$redirect = $redirect_array[1];
	}
	else $redirect = '';
	
	//Check error
	$error = isset($_GET['e']) ? $_GET['e'] : '';
?>
<style type="text/css">
	#wrapper 
	{		
		height: 70px;	
		margin: -150px 0 0 -172px;
		position: absolute;
		top: 50%;
		left: 50%;
	}
	h2
	{
		margin-top: -10px;
	}
	#otp_code{
		float: left;
		margin: 0 0 0 45px;
		text-align: center; 
	}
	.btn{
		float: left;
		margin: 0 0 0 90px;
	}
</style>
<script type="text/javascript">
	$(document).ready(function() {
		$("#otp_code").focus();
	});
</script>
<div>
    <div id="wrapper">
	    <?php if($error==1):?><div class="alert alert-danger" id="e1">OTP code must be numeric.</div><?php endif;?>
	    <?php if($error==2):?><div class="alert alert-danger" id="e2">OTP code is incorrect.</div><?php endif;?>
	    <form class="well form-inline" method="post" action="<?php echo get_app_info('path')?>/includes/login/two-factor.php">
	      <h2><span class="icon icon-key" style="margin: 7px 7px 0 0; "></span><?php echo _('Two Factor Authentication');?></h2><br/>
		  <input type="text" class="input" placeholder="<?php echo _('OTP Code');?>" name="otp_code" id="otp_code" autocomplete="off"><br/><br/><br/>
		  <input type="hidden" name="redirect" value="<?php echo htmlentities($redirect, ENT_QUOTES);?>"/>
		  <button type="submit" class="btn"><i class="icon icon-signin"></i> <?php echo _('Verify and login');?></button><br/><br/>
		  <p class="recovery"><a href="#<?php echo $_SESSION['userID']==1 ? 'unable-to-authenticate' : 'unable-to-authenticate2'?>" data-toggle="modal" target="_blank"><?php echo _('Unable to authenticate?');?></a></p>
		  
		  <?php if($_SESSION['userID']==1):?>
		  
			  <!-- I have trouble logging in with two-factor authentication -->
				<div id="unable-to-authenticate" class="modal hide fade">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal">&times;</button>
					  <h3><i class="icon icon-exclamation-sign" style="margin-top: 5px;"></i> <?php echo _('I have trouble logging in with two-factor authentication');?></h3>
					</div>
					<div class="modal-body">
						<p>If you're no longer able to login with 'Two-factor authentication' due to lost of your authentication device or any other reasons, here's what you can do to turn off two-factor authentication via your MySQL database:</p>
						<ol>
							<li>Login to your Sendy's MySQL database via phpmyadmin (usually via your hosting control panel)</li>
							<li>Once logged in, go to the <code>login</code> table</li>
							<li>Change the value of <code>auth_enabled</code> to <code>0</code></li>
						</ol>
						<p>You're now able to login with just your email/password combination. You can then re-setup two-factor authentication again at any time via Sendy's main settings.</p>
					</div>
				</div>
				<!-- I have trouble logging in with two-factor authentication -->
			
			<?php else:
				//Get admin's email address
				$r = mysqli_query($mysqli, 'SELECT username FROM login WHERE id = 1');
				if ($r) while($row = mysqli_fetch_array($r)) $email = $row['username'];
			?>
				
				<!-- I have trouble logging in with two-factor authentication -->
				<div id="unable-to-authenticate2" class="modal hide fade">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal">&times;</button>
					  <h3><i class="icon icon-exclamation-sign" style="margin-top: 5px;"></i> <?php echo _('I have trouble logging in with two-factor authentication');?></h3>
					</div>
					<div class="modal-body">
						<p><?php echo _("If you're no longer able to login with 'Two-factor authentication' due to lost of your authentication device or any other reasons, please contact the administrator at <a href='mailto:$email' style='text-decoration:underline;'>$email</a> to disable two-factor authentication for your account.");?></p>
					</div>
				</div>
				<!-- I have trouble logging in with two-factor authentication -->
			
			<?php endif;?>
			
		</form>
    </div>   
</div>

</body>
</html>