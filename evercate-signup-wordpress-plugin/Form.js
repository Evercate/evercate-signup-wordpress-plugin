
jQuery(function(){

	
	jQuery(".ec-signup-form").on("submit", function(e){

		e.preventDefault();
		var submittedForm = jQuery(this);
		var formId = submittedForm.data("formId");
		
		jQuery("#submit-button-"+formId).prop("disabled",true).hide();

		jQuery.post({
			url: constants.ajaxUrl, 
			data: jQuery(this).serialize(), 
		}).done(function(response) {
			jQuery("#evercate-signup-done-"+formId).show();
		})
		.fail(function(data) {
			console.log(data);
			jQuery("#evercate-signup-error-"+formId).show();
		})
		.always(function() {
			submittedForm.hide();
		});

	});
});
