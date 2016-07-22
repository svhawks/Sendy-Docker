$(document).ready(
	function()
	{
		ckEditor = CKEDITOR.replace( 'html', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php',
			height: '570px',
			extraPlugins: 'codemirror'
		});
		
		// wait until the editor is done initializing
		ckEditor.on("instanceReady",function() {
		  // overwrite the default save function
		  ckEditor.addCommand( "save", {
		    modes : { wysiwyg:1, source:1 },
		    exec : function () {
		      // get the editor content
		      var theData = ckEditor.getData();
		      $("#campaign-save-only-btn").click();
		    }
		   });
		})
		
		//Save campaign only
		$("#campaign-save-only-btn").click(function(e){
	        e.preventDefault(); 
	    	$('<input>').attr({type: 'hidden',id: 'save-only',name: 'save-only',value: 1}).appendTo('form');
			$("#edit-form").submit();
	    });
	}
);