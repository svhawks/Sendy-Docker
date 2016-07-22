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
	
	unlog_session();
?>
<style type="text/css">
	#wrapper 
	{		
		height: 70px;	
		margin: -150px 0 0 -130px;
		position: absolute;
		top: 50%;
		left: 50%;
	}
	h2
	{
		margin-top: -10px;
	}
	#forgot-form {
		width:262px;
		height: 158px;
		left:59%;
		overflow: hidden;
	}
	.session_error{
		width: 210px;
	}
</style>
<div>
    <div id="wrapper">
	    
	    <?php //if (!is_writable(session_save_path())) : ?>
<!--
	    <div class="alert alert-danger">
		    <p class="session_error">Your server's <code>session.save_path</code> is either not set or is not writable. Please contact your hosting support to check that your <code>session.save_path</code> is set in your php.ini and is writable.</p>
		    </div>
-->
	    <?php //endif;?>
	    
    	<?php if($error==1):?><div class="alert alert-danger" id="e1">Please fill in both email and password.</div><?php endif;?>
    	<?php if($error==2):?><div class="alert alert-danger" id="e2" style="width: 208px;">Incorrect password or user does not exist.</div><?php endif;?>
	    <form class="well form-inline" method="post" action="<?php echo get_app_info('path')?>/includes/login/main.php">
	      <h2><span class="icon icon-lock" style="margin: 7px 7px 0 0;"></span><?php echo _('Login');?></h2><br/>
		  <input type="text" class="input" placeholder="<?php echo _('Email');?>" name="email" id="email"><br/><br/>
		  <input type="password" class="input" placeholder="<?php echo _('Password');?>" name="password"><br/><br/>
		  <input type="hidden" name="redirect" value="<?php echo htmlentities($redirect, ENT_QUOTES);?>"/>
		  <button type="submit" class="btn"><i class="icon icon-signin"></i> <?php echo _('Sign in');?></button><br/><br/>
		  <p><a href="#forgot-form" title="" data-toggle="modal" class="recovery" id="forgot-btn"><?php echo _('Forgot password?');?></a></p>
		</form>
    </div>   
    
    <div id="forgot-form" class="modal hide fade">
	    <form class="well form-inline" method="post" action="<?php echo get_app_info('path')?>/includes/login/forgot.php" id="forgot">
	      <h2><span class="icon icon-meh"></span> <?php echo _('Forgot password?');?></h2><br/>
		  <input type="text" class="input" placeholder="<?php echo _('Your email');?>" name="email" id="forgot-email"><br/><br/>
		  <button type="submit" class="btn" id="send-pass-btn"><i class="icon icon-key"></i> <?php echo _('Send password');?></button>
		</form>
		<script type="text/javascript">
			$(document).ready(function() {
				$("#email").focus();
				$("#forgot-btn").click(function(){
					$("#forgot-email").val($("#email").val());
				});
				$("#forgot").submit(function(e){
					e.preventDefault(); 
					
					$("#send-pass-btn").html("<i class=\"icon icon-envelope\"></i> <?php echo _('Sending');?>..");
					
					var $form = $(this),
					email = $form.find('input[name="email"]').val(),
					url = $form.attr('action');
					
					$.post(url, { email: email },
					  function(data) {
					      if(data)
					      {
					      	$("#send-pass-btn").html("<i class=\"icon icon-key\"></i> <?php echo _('Send password');?>");
					      	
					      	if(data=='<?php echo _('Email does not exist.');?>')
					      		alert('<?php echo _('Email does not exist.');?>');
					      	else if(data=='main_user')
					      	{
						      	$('#forgot-form').modal('hide');
						      	$('#password-sent').modal('show');
					      	}
					      	else $('#forgot-form').modal('hide');
					      }
					      else
					      {
					      	alert("<?php echo _('Sorry, unable to reset password. Please try again later!');?>");
					      }
					  }
					);
				});
			});
		</script>
    </div>
    
    <div id="password-sent" class="modal hide fade">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h3><span class="icon icon-envelope"></span> <?php echo _('Password reset email has been sent to you');?></h3>
    </div>
    <div class="modal-body">
	    <p>Your password has been reset, a password reset email has been sent to your email address. Please check your inbox as well as your spam folder for the email. <br/><br/> If you don't receive the password reset email, please <a href="https://sendy.co/troubleshooting#forgot-password" target="_blank" style="text-decoration: underline;">see this troubleshooting tip</a>.</p>
    </div>
    <div class="modal-footer">
      <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign" style="margin-top: 5px;"></i> <?php echo _('Close');?></a>
    </div>
    </div>
</div>

</body>
</html>