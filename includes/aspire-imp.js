jQuery(document).ready(function() {
	jQuery(function() {  
	  jQuery("#aspiration-entry").bind('keyup', function(e) {		
		// validate and process form here 
		// via http://stackoverflow.com/questions/6844952/execute-function-when-enter-is-pressed-if-textbox-has-focus
		if ( e.keyCode === 13 ) {
			var aspirationEntry = jQuery("input#aspiration-entry").val();
			//test
			//alert (dataString);return false;  
			var dataString = 'aspiration-entry='+ aspirationEntry;
			jQuery.ajax({
			
				type: 'POST',
				action: 'tabarAjaxery',
				url: 'admin-ajax.php',
				data: dataString,
				success: function() {
					jQuery('.tabar-loop').prepend('<h5 class="aspiration-title">' + aspirationEntry + '</h5>');
				}
			
			});
			
		}
	  });  
	});  
});