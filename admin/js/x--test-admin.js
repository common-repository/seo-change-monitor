jQuery(document).ready(function($) 
{
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	1. Initiate install
	2. Initiate Data load
	3. Reset install
	4. Set Start and End dates
	5. Error reporting

	*/


  

	/*
	Option to reset full install and start over.
	*/	
    console.log("testing");
	jQuery('#icdash_start_install').on
	(
		{
		 click: function(e) 
			{
				console.log("button clicked");	
					// We can also pass the url value separately from ajaxurl for front end AJAX implementations
				var data = {
					'action': 'my_action',
					'whatever': ajax_object.we_value      // We pass php values differently!
				};					
				alert('Before sending to server: ' + ajax_object.we_value);
				jQuery.post(ajax_object.ajax_url, data, function(response) 
				{
					alert('Got this from the server: ' + response);
					console.log(ajax_object.hook);
					console.log("click2");
				});
			}
		}	 
	);



				
});