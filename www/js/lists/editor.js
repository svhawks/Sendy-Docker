$(document).ready(
	function()
	{
		CKEDITOR.replace( 'thankyou_message', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php',
			height: '290px',
			extraPlugins: 'codemirror'
			
		});
		CKEDITOR.replace( 'goodbye_message', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php',
			height: '377px',
			extraPlugins: 'codemirror'
		});
		CKEDITOR.replace( 'confirmation_email', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php',
			height: '289px',
			extraPlugins: 'codemirror'
		});
	}
);