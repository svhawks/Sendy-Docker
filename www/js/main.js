$(document).ready(function() {
	//------------------------------------------------------//
	//                          INIT                        //
	//------------------------------------------------------//
	//Tooltip
	$('a').tooltip({
		animation : false
	})
	
	//Reports
	$(".recipient-click-export").tooltip("destroy");
	$(".recipient-click-export").tooltip({animation : false, placement: "left"});
	
	//Campaigns
	$(".delete-campaign").tooltip("destroy");
	$(".delete-campaign").tooltip({animation : false, placement: "left"});
	
	//Templates
	$(".delete-template").tooltip("destroy");
	$(".delete-template").tooltip({animation : false, placement: "left"});
	
	//Lists
	$(".delete-list").tooltip("destroy");
	$(".delete-list").tooltip({animation : false, placement: "left"});
	
	//Subscribers
	$(".delete-subscriber").tooltip("destroy");
	$(".delete-subscriber").tooltip({animation : false, placement: "left"});
	
	//Campaigns RSS
	$(".campaigns-rss-btn").tooltip("destroy");
	$(".campaigns-rss-btn").tooltip({animation : false, placement: "left"});
	//------------------------------------------------------//
	//                        BUTTONS                       //
	//------------------------------------------------------//
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//	
	
	jQuery.fn.selectText = function(){
	    var doc = document
	        , element = this[0]
	        , range, selection
	    ;
	    if (doc.body.createTextRange) {
	        range = document.body.createTextRange();
	        range.moveToElementText(element);
	        range.select();
	    } else if (window.getSelection) {
	        selection = window.getSelection();        
	        range = document.createRange();
	        range.selectNodeContents(element);
	        selection.removeAllRanges();
	        selection.addRange(range);
	    }
	};
});