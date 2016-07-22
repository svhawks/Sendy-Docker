<script type="text/javascript">
$(document).ready(function() {
		
	//send test email
	$("#test-form").submit(function(e){
		e.preventDefault(); 
		
		var $form = $(this),
		test_email = $form.find('input[name="test_email"]').val(),
		campaign_id = $form.find('input[name="cid"]').val(),
		webversion = $form.find('input[name="webversion"]').val(),
		url = $form.attr('action');
		//validate email
		AtPos = test_email.indexOf("@")
		StopPos = test_email.lastIndexOf(".")
		
		if(test_email=='')
		{
			$("#test-send-btn").effect("shake", { times:3 }, 60);
		}
		else if (AtPos == -1 || StopPos == -1) 
		{
			$("#test-send-btn").effect("shake", { times:3 }, 60);
		}
		else
		{
			$("#test-send-btn").html("<i class=\"icon icon-envelope-alt\"></i> <?php echo _('Sending');?>...");
			
			$.post(url, { test_email: test_email, campaign_id: campaign_id, webversion: webversion },
			  function(data) {
				  
				  data_array = data.split(",");
				  all_ok = 'ok';
				  for(var i=0; i<data_array.length-1; i++) 
				  	if(data_array[i]!='ok') all_ok = 'failed';
				  
				  if(all_ok=='ok')
				  {
					  $("#test-send").css("display", "block");
				  	  $("#test-send-error").css("display", "none");
				  	  $("#test-send-error2").css("display", "none");
				      $("#test-send-btn").html("<i class=\"icon icon-envelope-alt\"></i> <?php echo _('Test send this newsletter');?>");
				  }
				  else
				  {
					  $("#test-send").css("display", "none");
				  	  $("#test-send-error").css("display", "none");
				  	  $("#test-send-error2").css("display", "block");
				  	  $("#test-send-btn").html("<i class=\"icon icon-envelope-alt\"></i> <?php echo _('Test send this newsletter');?>");
				  	  
				  	  //Show more info & instructions if user's Amazon SES accont is suspended by Amazon
				  	  actual_msg = data.split(": ");
				  	  if(actual_msg[2]=="Sending suspended for this account. For more information, please check the inbox of the email address associated with your AWS account.")
				  	  {
					  	  suspension_msg = "<strong>"+data+"</strong><br/><br/><?php echo _('Please find Amazon\'s email in your inbox as well as spam folder. They will include a reason for the suspension. You will need to reply to that email in order to re-activate your account. If you can\'t find the email, please contact Amazon\'s support at their <a href=\'https://forums.aws.amazon.com/forum.jspa?forumID=90\' target=\'_blank\'>Amazon SES forum</a>');?>.<br/><br/><?php echo _('For more information on Amazon SES suspension, please see <a href=\'http://docs.aws.amazon.com/ses/latest/DeveloperGuide/e-faq-sp.html\' target=\'_blank\'>http://docs.aws.amazon.com/ses/latest/DeveloperGuide/e-faq-sp.html');?></a>";
					  	  $("#test-send-error2-msg").html(suspension_msg);
				  	  }
				  	  else $("#test-send-error2-msg").html("<strong>"+data+"</strong>");
				  }
			  }
			);
		}
	});
	
	//select list count
	$("select#email_list").change(function () {
      var recip = 0;
      $("select#email_list option:selected").each(function () {
            recip += Number($(this).data('quantity'));
          });
      $("#recipients").text(recip);
      
      //if user have AWS keys, check quota
      if($("#aws_keys_available").val()=='true' || $("#is_sub_user").val())
	  {
	      //check if user is sending to more than SES allows
	      if(recip > $("#ses_sends_left").val() && $("#ses_sends_left").val()!='-1')
	      {
	      	  $("#over-limit").slideDown("fast");
		      $("#recipients").css("color", "#FF0000");
		      $("#recipients").css("font-weight", "bold");
		      $("#real-btn").addClass("disabled");
		      $("#real-btn").attr("disabled", "disabled");
		      $("#schedule-btn").addClass("disabled");
		      $("#schedule-btn").attr("disabled", "disabled");
		      $("#pay-btn").addClass("disabled");
		      $("#pay-btn").attr("disabled", "disabled");
	      }
	      else
	      {
	          $("#over-limit").slideUp("fast");
		      $("#recipients").css("color", "#000000");
		      $("#recipients").css("font-weight", "normal");
		      $("#real-btn").removeClass("disabled");
		      $("#real-btn").removeAttr("disabled");
		      $("#schedule-btn").removeClass("disabled");
		      $("#schedule-btn").removeAttr("disabled");
		      $("#pay-btn").removeClass("disabled");
		      $("#pay-btn").removeAttr("disabled");
	      }
	   }
	   
	   $("#grand_total").text(
      	number_format(
	      	Number($("#delivery_fee").text()) + 
	      	(Number($("#recipient_fee").text()) * recip)
      	, 3, '.', ',')
      );
      $("#grand_total_val").val(
      	number_format(
	      	Number($("#delivery_fee").text()) + 
	      	(Number($("#recipient_fee").text()) * recip)
      	, 2, '.', ',')
      );
    })
    .trigger('change');
	
	//number format function
	function number_format( number, decimals, dec_point, thousands_sep ) {
	    var n = number, c = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
	    var d = dec_point == undefined ? "," : dec_point;
	    var t = thousands_sep == undefined ? "." : thousands_sep, s = n < 0 ? "-" : "";
	    var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
	    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
	}
	
	//send later
	$("#send-later-btn").click(function(){
		if($("#schedule-form-wrapper").is(":visible"))
		{
			$("#real-btn").slideDown("fast");
			$("#pay-btn").slideDown("fast");
			$("#schedule-form-wrapper").slideUp("fast");
			$("#send-later-btn").text("<?php echo _('Schedule this campaign?');?>");
		}
		else
		{
			$("#real-btn").slideUp("fast");
			$("#pay-btn").slideUp("fast");
			$("#schedule-form-wrapper").slideDown("fast");
			$("#send-later-btn").html("&larr; <?php echo _('Back');?>");
		}
	});
	
	$('#datepicker').datepicker({
		format: 'mm-dd-yyyy',
		weekStart: 1
	});
	
	$("#schedule-form").submit(function(){
		$("#email_lists").val($('select#email_list').val());
		$("#grand_total_val2").val($('#grand_total_val').val());
		$("#total_recipients2").val($("#recipients").text());
	});
});
</script>