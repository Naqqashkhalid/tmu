jQuery(function($){

	function ajax_loadmore(selectorClass){
		const dataElement = $(selectorClass);
		const query = $("#query");
		let page = 1+parseInt(dataElement.data("page"));
		const last_page = dataElement.data("total-pages");
		const postType = dataElement.data("type");
		const blockType = $( '.'+postType+'-block' );
		const total = dataElement.data("total");

		console.log(query.data("query"));

		const data = {
			'action' : 'search',
			'query': query.data("query"),
			'type': postType,
			'page': dataElement.data("page"),
			'total': total
		}
 
		$.ajax({ // you can also use $.post here
			url : ajax_search_params.ajaxurl, // AJAX handler
			data : data,
			type : 'POST',
			beforeSend : function ( xhr ) {
				blockType.append('<div class="loading"><div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>');
			},
			success : function( data ){
				if( data ) {
					blockType.append(data);
					$('.loading').remove();
					dataElement.data("page", page+1);
					if (page == total) { dataElement.remove(); }
					lazyload();
				}
			}
		})
	}

	$(document).on('click', '.load-more-movies', () => { ajax_loadmore('.load-more-movies');});
	$(document).on('click', '.load-more-tv', () => { ajax_loadmore('.load-more-tv');});
	$(document).on('click', '.load-more-drama', () => { ajax_loadmore('.load-more-drama');});
	$(document).on('click', '.load-more-people', () => { ajax_loadmore('.load-more-people');});
	$(document).on('click', '.load-more-post', () => { ajax_loadmore('.load-more-post');});
});