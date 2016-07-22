<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/list/main.php');?>

<script src="<?php echo get_app_info('path');?>/js/ckeditor/ckeditor.js?7"></script>
<script src="<?php echo get_app_info('path');?>/js/lists/editor.js?7"></script>

<form action="<?php echo get_app_info('path')?>/includes/list/edit.php" method="POST" accept-charset="utf-8" class="form-vertical">

<div class="row-fluid">
	<div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    
    <div class="span10">
    	
    	<div class="row-fluid">
	    	<div class="span12">
				<div>
			    	<p class="lead"><?php echo get_app_data('app_name');?></p>
		    	</div>
		    	<h2><?php echo _('Edit list');?></h2><br/>
			</div>
    	</div>
    
	    <div class="row-fluid">
	    
		    <div class="span12">
		    	
		    	<label class="control-label" for="list_name"><?php echo _('List name');?></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="list_name" name="list_name" placeholder="<?php echo _('The list name');?>" value="<?php echo get_lists_data('name', $_GET['l']);?>">
		            </div>
		        </div>
			    
		    </div>   
		    
	    </div>
	    
	    <hr/>
	    
	    <div class="row-fluid">
		    <div class="span12">
			    <h2><?php echo _('Subscribe settings');?></h2><br/>
		    </div>
	    </div>
	    
	    <div class="row-fluid">
	    
	    	<div class="span4">
		    	<label class="control-label"><strong><?php echo _('List type');?></strong></label>
		        <div class="well">
		        	<p><?php echo _('If you select double opt-in, users will be required to click a link in a confirmation email they\'ll receive when they sign up via the subscribe form or API.');?></p>
		        	<p>
				    	<div class="btn-group" data-toggle="buttons-radio">
						  <a href="javascript:void(0)" title="" class="btn" id="single"><i class="icon icon-angle-right"></i> <?php echo _('Single Opt-In');?></a>
						  <a href="javascript:void(0)" title="" class="btn" id="double"><i class="icon icon-double-angle-right"></i> <?php echo _('Double Opt-In');?></a>
						</div>
						<script type="text/javascript">
							$(document).ready(function() {
								<?php 
									$opt_in = get_lists_data('opt_in', $_GET['l']);
									if($opt_in==0):
								?>
								$("#single").button('toggle');
								$("#opt_in").val("0");
								<?php else:?>
								$("#double").button('toggle');
								$("#opt_in").val("1");
								<?php endif;?>
								
								$("#single").click(function(){
									$("#opt_in").val("0");
								});
								$("#double").click(function(){
									$("#opt_in").val("1");
								});
							});
						</script>
			    	</p>
		        </div>
		        
		        <label class="control-label" for="subscribed_url"><strong><?php echo _('Subscribe success page');?></strong></label>
		        <div class="well">
		        	<p><?php echo _('When users subscribe through the subscribe form, they\'ll be sent to a generic subscription confirmation page. To redirect users to a page of your preference, enter the link below. If you chose double opt-in as your List Type, this page will tell them a confirmation email has been sent to them.');?></p>
		        	<label class="control-label" for="subscribed_url"><?php echo _('Page URL');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="subscribed_url" name="subscribed_url" placeholder="http://" style="width: 98%;" value="<?php echo get_lists_data('subscribed_url', $_GET['l']);?>">
			            </div>
			        </div>
			        <div class="well" style="background: #FFFDEC;">
			        	<p><?php echo _('You can also pass \'Email\' and \'listID\' data into the \'Subscribe success page\' like so');?>:</p>
			        	<p><?php echo _('Example');?>:<br/>http://domain.com/subscribed.php?email=%e&listid=%l</p>
			        	<p><?php echo _('<code>%e</code> will be converted into the \'email\' and <code>%l</code> will be converted into the \'listID\' that subscribed');?>.</p>
		        	</div>
		        </div>
		        
		        <label class="control-label" for="subscribed_url"><strong><?php echo _('Subscription confirmed page');?></strong> (<?php echo _('only applies for double opt-ins');?>)</label>
		        <div class="well">
		        	<p><?php echo _('If your List Type is double opt-in, users who clicked the confirmation URL will be sent to a generic confirmation page. To redirect users to a page of your preference, enter the link below.');?></p>
		        	<label class="control-label" for="confirm_url"><?php echo _('Page URL');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="confirm_url" name="confirm_url" placeholder="http://" style="width: 98%;" value="<?php echo get_lists_data('confirm_url', $_GET['l']);?>">
			            </div>
			        </div>
			        <div class="well" style="background: #FFFDEC;">
			        	<p><?php echo _('You can also pass \'Email\' and \'listID\' data into the \'Subscription confirmed page\' like so');?>:</p>
			        	<p><?php echo _('Example');?>:<br/>http://domain.com/confirmed.php?email=%e&listid=%l</p>
			        	<p><?php echo _('<code>%e</code> will be converted into the \'email\' and <code>%l</code> will be converted into the \'listID\' that subscribed');?>.</p>
		        	</div>
		        </div>
	    	</div>
		    
		    <div class="span8">
			    
			    <div class="control-group">
			        <div class="controls">
			          <label class="checkbox">
			          	<?php $thankyou = get_lists_data('thankyou', $_GET['l']); ?>
			            <input type="checkbox" id="thankyou_email" name="thankyou_email" <?php if($thankyou == 1){echo 'checked';}?>>
			            <?php echo _('Send user a thank you email after they subscribe through the subscribe form or API?');?>
			          </label>
			        </div>
			      </div>
			  
			  <label class="control-label" for="thankyou_subject"><strong><?php echo _('Thank you email subject');?></strong></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="thankyou_subject" name="thankyou_subject" placeholder="<?php echo _('Email subject');?>" style="width: 98%;" value="<?php echo get_lists_data('thankyou_subject', $_GET['l']);?>">
		            </div>
		        </div>
			  
			  <label class="control-label" for="thankyou_message"><strong><?php echo _('Thank you email message');?></strong></label>
			  <div class="control-group">
			    	<div class="controls">
			          <textarea class="input-xlarge" id="thankyou_message" name="thankyou_message" rows="10" placeholder="<?php echo _('Email message');?>">
				          <?php echo get_lists_data('thankyou_message', $_GET['l']);?>
			          </textarea>
			        </div>
			    </div>
			  
			  <br/>
			  
			  <label class="control-label" for="confirmation_subject"><strong><?php echo _('Confirmation email subject');?></strong> (<?php echo _('only applies for double opt-ins');?>)<br/><em>* <?php echo _('A generic subject line will be used if you leave this field empty.');?></em></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="confirmation_subject" name="confirmation_subject" placeholder="<?php echo _('Subject of confirmation email');?>" style="width: 98%;" value="<?php echo get_lists_data('confirmation_subject', $_GET['l']);?>">
		            </div>
		        </div>
			    
			  <label class="control-label" for="confirmation_email"><strong><?php echo _('Double Opt-In confirmation message');?></strong> (<?php echo _('only applies for double opt-ins');?>)<br/><em>* <?php echo _('A generic email message will be used if you leave this field empty.');?></em><br/><em>* <?php echo _('Don\'t forget to include the confirmation link tag');?> </em><code id="confirmation_link_tag">[confirmation_link]</code><em> <?php echo _('somewhere in your message');?></em>.</label>
				<script type="text/javascript">
				$(document).ready(function() {
					$("#confirmation_link_tag").click(function(){
						$(this).selectText();
					});
				});
				</script>
			  <div class="control-group">
			    	<div class="controls">
			          <textarea class="input-xlarge" id="confirmation_email" name="confirmation_email" rows="10" placeholder="<?php echo _('Email message');?>">
				          <?php echo get_lists_data('confirmation_email', $_GET['l']);?>
			          </textarea>
			        </div>
			    </div>
			    
		    </div> 
		</div>
		
		<br/>
		
		<hr/>
		
		<div class="row-fluid">
		    <div class="span12">
			    <h2><?php echo _('Unsubscribe settings');?></h2><br/>
		    </div>
	    </div>
	    
	    <div class="row-fluid">
	    
	    	<div class="span4">
		    	<label class="control-label"><strong><?php echo _('Unsubscribe user');?></strong></label>
		        <div class="well">
		        	<p><?php echo _('When a user unsubscribes from a newsletter or through the API, choose whether to unsubscribe them from this list only, or unsubscribe them from all lists in this brand.');?></p>
		        	<p>
				    	<div class="btn-group" data-toggle="buttons-radio">
						  <a href="javascript:void(0)" title="" class="btn" id="this-list"><i class="icon icon-minus"></i> <?php echo _('Only this list');?></a>
						  <a href="javascript:void(0)" title="" class="btn" id="all-list"><i class="icon icon-reorder"></i> <?php echo _('All lists');?></a>
						</div>
						<script type="text/javascript">
							$(document).ready(function() {
								<?php 
									$ual = get_lists_data('unsubscribe_all_list', $_GET['l']);
									if($ual==0):
								?>
								$("#this-list").button('toggle');
								$("#unsubscribe_all_list").val("0");
								<?php else:?>
								$("#all-list").button('toggle');
								$("#unsubscribe_all_list").val("1");
								<?php endif;?>
								
								$("#this-list").click(function(){
									$("#unsubscribe_all_list").val("0");
								});
								$("#all-list").click(function(){
									$("#unsubscribe_all_list").val("1");
								});
							});
						</script>
			    	</p>
		        </div>
		        
		         <label class="control-label" for="unsubscribed_url"><strong><?php echo _('Unsubscribe confirmation page');?></strong></label>
		        <div class="well">
		        	<p><?php echo _('When users unsubscribe from a newsletter, they\'ll be sent to a generic unsubscription confirmation page. To redirect users to a page of your preference, enter the link below.');?></p>
		        	<label class="control-label" for="subscribed_url"><?php echo _('Page URL');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="unsubscribed_url" name="unsubscribed_url" placeholder="http://" style="width: 98%;" value="<?php echo get_lists_data('unsubscribed_url', $_GET['l']);?>">
			            </div>
			        </div>
			        <div class="well" style="background: #FFFDEC;">
			        	<p><?php echo _('You can also pass \'Email\' and \'listID\' data into the \'Unsubscribe confirmation page\' like so');?>:</p>
			        	<p><?php echo _('Example');?>:<br/>http://domain.com/unsubscribed.php?email=%e&listid=%l</p>
			        	<p><?php echo _('<code>%e</code> will be converted into the \'email\' and <code>%l</code> will be converted into the \'listID\' that unsubscribed');?>.</p>
		        	</div>
		        </div>
	    	</div>
		    
		    <div class="span8">
			    
			    <div class="control-group">
			        <div class="controls">
			          <label class="checkbox">
			          	<?php $goodbye = get_lists_data('goodbye', $_GET['l']); ?>
			            <input type="checkbox" id="goodbye_email" name="goodbye_email" <?php if($goodbye == 1){echo 'checked';}?>>
			            <?php echo _('Send user a confirmation email after they unsubscribe from a newsletter or through the API?');?>
			          </label>
			        </div>
			      </div>
			      
			  <label class="control-label" for="goodbye_subject"><strong><?php echo _('Goodbye email subject');?></strong></label>
		    	<div class="control-group">
			    	<div class="controls">
		              <input type="text" class="input-xlarge" id="goodbye_subject" name="goodbye_subject" placeholder="<?php echo _('Email subject');?>" style="width: 98%;" value="<?php echo get_lists_data('goodbye_subject', $_GET['l']);?>">
		            </div>
		        </div>
			  
			  <label class="control-label" for="goodbye_message"><strong><?php echo _('Goodbye email message');?></strong></label>
			  <div class="control-group">
			    	<div class="controls">
			          <textarea class="input-xlarge" id="goodbye_message" name="goodbye_message" rows="10" placeholder="<?php echo _('Email message');?>">
				          <?php echo get_lists_data('goodbye_message', $_GET['l']);?>
			          </textarea>
			        </div>
			    </div>
			    
			    <input type="hidden" name="id" value="<?php echo $_GET['i'];?>">
		        <input type="hidden" name="list" value="<?php echo $_GET['l'];?>">
		        <input type="hidden" name="opt_in" id="opt_in" value="">
		        <input type="hidden" name="unsubscribe_all_list" id="unsubscribe_all_list" value="">
			    
		    </div> 
		</div>
	</div>
    
</div>

<div class="row-fluid">
	<div class="span2"></div>
	<div class="span10">
		<button type="submit" class="btn btn-inverse" style="float:right;"><i class="icon-ok icon-white"></i> <?php echo _('Save');?></button>
	</div>
</div>

</form>
<?php include('includes/footer.php');?>
