jQuery(function ($) {
  var isLoading = false;
  var sort_option;
  $(document).on('click', '.sort-option, .button', function () {
  	let sorting = false;
  	let clicked = $(this);
    if ($(this).hasClass('sort-option') && !$(this).hasClass('active')) {
      // Sorting clicked
      sorting = true;
      sort_option = $(this).data('sort');
      var page = 0; // Reset page for sorting
    } else {
      // Load More or active sort option clicked (prevents reset)
      var page = $('.button').data('page') || 1; // Use existing page if available
    }

    sort_option = sort_option ?? $('.selected').data('current-active');
    console.log(sort_option);
    console.log(page);

    if ($(this).hasClass('button') && isLoading) {
      // Prevent multiple Load More clicks while loading
      return;
    }

    isLoading = true;

    let button = $('.button');
    let container = button.data('for');
    let total = button.data('total');

    let data = {
      'action': 'terms_loadmore',
      'term': button.data('term'),
      'ppp': button.data('items'),
      'page': page,
      'post_type': button.data('type'),
      'sort': sort_option
    };

    $.ajax({
      url: terms_loadmore_params.ajaxurl,
      data: data,
      type: 'POST',
      beforeSend: function (xhr) {
        $('#' + container).append('<div class="loading"><div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>');
      },
      success: function (data) {
        if (data) {
          if (sorting) {
            // Sorting applied, replace content
            $('#' + container).html(data);
            $(".selected").attr("data-current-active", sort_option);
			$('.selectedText').text(clicked.text()); // Update text based on click source
			$('.sort-option.active').removeClass('active');
			clicked.addClass('active');
			if ((page + 1) != total) button.show();
			sorting = false;
          } else {
            // Load More, append content
            $('#' + container).append(data);
            $('.button').data('page', page+1)
            if ((page + 1) == total) { button.hide(); } else { button.show(); }
          }
          
          $('.loading').remove();
          lazyload();
          isLoading = false;
        } else {
          button.hide();
          $('.loading').remove();
        }
      }
    });
  });
});