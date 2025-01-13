jQuery(function($){

	$(document).on('click', '.loadmore', function() {
	  if ($(this).hasClass('loadmore')) {
	  	let button = $(this);
	    let total = button.data('total');
	    let tag = button.data('tag');
	    let page = button.data('page');
	    let total_pages = Math.ceil((total-4)/10);

	    $.ajax({
			url : tag_loadmore_params.ajaxurl,
			data : { 'action' : 'tag_loadmore', 'tag': tag, 'page': page },
			type : 'POST',
			beforeSend : function ( xhr ) {
				$('.tag-posts-list').append('<div class="loading"><div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>');
			},
			success : function( data ){
				if( data ) {
					$('.tag-posts-block').append(data);
					button.data('page',page+1);
					$('.loading').remove();
					lazyload();
					if ((1+page) == total_pages) { button.remove(); }
				} else { button.remove(); $('.loading').remove(); }
			}
		})
	  }
	});
});