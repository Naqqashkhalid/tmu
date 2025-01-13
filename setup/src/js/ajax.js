jQuery(function($){
	var checkboxes = $('input[type="checkbox"]');
	checkboxes.change(function() {
		let option = $(this).attr('id');
		let value = $(this).is(':checked') ? 'on': 'off';
	    $.ajax({
			url : tmu_settings_params.ajaxurl,
			data : { 'action' : 'tmu_settings', 'option' : option, 'value': value },
			type : 'POST'
		})
	});
});