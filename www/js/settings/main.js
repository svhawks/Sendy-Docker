$(document).ready(function() {
	$("#company, #personal_name, #email, #password, #timezone, #language, #aws_key, #aws_secret, #ses_endpoint, #send_rate, #paypal").keypress(function(e) {
	    if(e.keyCode == 13) {
			e.preventDefault();
			$("#settings-form").submit();
	    }
	});
	$("#settings-form").submit(function(e){
		e.preventDefault(); 
		
		var $form = $(this),
		uid = $form.find('input[name="uid"]').val(),
		personal_name = $form.find('input[name="personal_name"]').val(),
		company = $form.find('input[name="company"]').val(),
		email = $form.find('input[name="email"]').val(),
		password = $form.find('input[name="password"]').val(),
		aws_key = $form.find('input[name="aws_key"]').val(),
		aws_secret = $form.find('input[name="aws_secret"]').val(),
		paypal = $form.find('input[name="paypal"]').val(),
		timezone = $form.find('#timezone').val(),
		language = $form.find('#language').val(),
		ses_endpoint = $form.find('#ses_endpoint').val(),
		from_name = $form.find('input[name="from_name"]').val(),
		from_email = $form.find('input[name="from_email"]').val(),
		reply_to = $form.find('input[name="reply_to"]').val(),
		send_rate = $form.find('input[name="send_rate"]').val(),
		ses_send_rate = $form.find('input[name="ses_send_rate"]').val(),
		url = $form.attr('action');
		
		//validate email
		AtPos = email.indexOf("@")
		StopPos = email.lastIndexOf(".")
		if (AtPos == -1 || StopPos == -1) email_valid = false;
		else email_valid = true;
		
		if(personal_name!="" && company!="" && email!="" && email_valid==true)
		$.post(url, { uid: uid, personal_name: personal_name, company: company, email: email, password: password, aws_key: aws_key, aws_secret: aws_secret, paypal: paypal, timezone: timezone, language:language, ses_endpoint:ses_endpoint, from_name: from_name, from_email: from_email, reply_to: reply_to, send_rate: send_rate, ses_send_rate: ses_send_rate },
		  function(data) {
		      if(data)
		      {
		      	if(data=="email exists") $("#alert-error2").css("display", "block");
				else window.location = $("#redirect").val();
		      }
		      else
		      {
		      	$("#alert-error1").css("display", "block");
		      }
		  }
		);
	});
});