jQuery(function($){

	function ajax_loadmore(diff){
		const dataElement = $(".load_more_box");
		let page = diff === 0 ? 1 : diff+parseInt(dataElement.data("page"));
		const last_page = dataElement.data("total-pages");

		const data = {
			'action' : 'loadmore',
			'language': dataElement.data("lang"),
			'genre': dataElement.data("genre"),
			'country': dataElement.data("country"),
			'channel': dataElement.data("channel"),
			'year': dataElement.data("year"),
			'ppp': dataElement.data("posts-per-page"),
			'totalPages': dataElement.data("total-pages"),
			'page': page,
			'sort_by': dataElement.data("sort_by"),
			'post_type': dataElement.data("post-type")
		}

		console.log(data);
 
		$.ajax({ // you can also use $.post here
			url : ajax_loadmore_params.ajaxurl, // AJAX handler
			data : data,
			type : 'POST',
			beforeSend : function ( xhr ) {
				$('#module_1').append('<div class="loading"><div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>');
			},
			success : function( data ){
				if( data ) {
					$( '#module_1' ).html(data);
					$('.loading').remove();
					lazyload();
				}
			}
		})
	}

	$(document).on('click', '#loadnext', () => { ajax_loadmore(1); });
	$(document).on('click', '#loadprev', () => { ajax_loadmore(-1); });

	$(document).on('click', '.clear-button-modals', function() {
	  if ($(this).hasClass('clear-button-modals')) {
	    const dataElementValue = $(this).data('element');
	    // console.log(dataElementValue);
	    clearFilters(dataElementValue);
		ajax_loadmore(0);
	  }
	});

	$(document).on('click', '.applly-button-modals', function() {
	  if ($(this).hasClass('applly-button-modals')) {
	    const dataElementValue = $(this).data('element');
	    // console.log(dataElementValue);
	    applyFilters(dataElementValue);
	    ajax_loadmore(0);
	  }
	});

	$(document).on('click', '.clearAllButton', function() {
	  if ($(this).hasClass('clearAllButton')) {
	  	clearAllFilters();
	    ajax_loadmore(0);
	  }
	});
});
