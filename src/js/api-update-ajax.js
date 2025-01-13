jQuery(function($){

	$(document).on('click', '.button', function() {
	  if ($(this).hasClass('button')) {
	  	let button = $(this);
	    let buttonType = button.data('type');
	    let buttonAction = button.data('action');

	    const inputSelector = `#${buttonAction}-${buttonType}-post-id, #${buttonAction}-${buttonType}-tmdb-id, #${buttonAction}-${buttonType}-season-no`;
    	const checkboxSelector = `#${buttonAction}-${buttonType}-credits, #${buttonAction}-${buttonType}-images, #${buttonAction}-${buttonType}-videos, #${buttonAction}-${buttonType}-all-seasons, #${buttonAction}-${buttonType}-episodes`;

	    const formData = {
	    	'action' : 'api_update',
			'action_type' : buttonAction,
			'btn_type': buttonType
	    };

    	$(inputSelector).each(function() { formData[$(this).attr('name')] = $(this).val(); });

	    $(checkboxSelector).each(function() {
	      if (this.checked) {
	        formData[$(this).attr('name')] = $(this).val();
	      }
	    });

	    console.log(formData);

	    $.ajax({
			url : api_update_params.ajaxurl,
			data : formData,
			type : 'POST',
			beforeSend : function ( xhr ) {
				$('.loading-'+buttonType+'-'+buttonAction).append('<div class="loading"><div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>');
			},
			error: function(xhr, status, error) {
				console.error('Error:', error);
			},
			success : function( data ){
				if( data ) {
					$('.loading-'+buttonType+'-'+buttonAction).html(data);
					$('.loading').remove();
					console.log(data);
				}
			}
		})
	  }
	});
});