
jQuery(function(){




	jQuery("#tags, #tagtypes").select2();

	//Possible start of forcing select2 to sort in order items were added (not quite working with removing and readding)
	// jQuery("#tags, #tagtypes").on('select2:select', function(e){

	// 	console.log("woo");

	// 	var id = e.params.data.id;
	// 	var option = jQuery(e.target).children('[value='+id+']');
	// 	option.detach();
	// 	jQuery(e.target).append(option).change();
	// });
});
