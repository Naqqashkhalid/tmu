jQuery(function($){

	$(document).on('click', '.button', function() {
	  if ($(this).hasClass('button')) {
	  	let button = $(this);
	    let container = button.data('for');
	    let total = button.data('total');
	    let page = button.data('page');
	    let profession = button.data('profession') ? button.data('profession') : '';


		let data = {
			'action' : 'sidebar_loadmore',
			'term': button.data('term'),
			'ppp': button.data('ppp'),
			'page': page,
			'post_type': button.data('type'),
			'post_id': button.data('post'),
			'profession': button.data('profession')
		}

	    $.ajax({
			url : sidebar_loadmore_params.ajaxurl,
			data : data,
			type : 'POST',
			beforeSend : function ( xhr ) {
				$('#'+container).append('<div class="loading"><div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>');
			},
			success : function( data ){
				if( data ) {
					$('#'+container).append(data);
					button.data('page',page+1);
					$('.loading').remove();
					if (page == button.data('total')) { button.remove(); }
				} else {
					$('.loading').remove();
					button.remove();
				}
			}
		})
	  }
	});
});