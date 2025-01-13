const items = document.querySelectorAll('.item');
const contentItems = document.querySelectorAll('.item-content');

items.forEach(item => {
  item.addEventListener('click', () => {
    // Remove active class from all items
    items.forEach(item => item.classList.remove('active'));

    // Add active class to the clicked item
    item.classList.add('active');

    // Get the target content ID from the clicked item's 'for' attribute
    const targetId = item.dataset.for;

    // Hide all content items
    contentItems.forEach(contentItem => {
      contentItem.style.display = 'none';
    });

    // Show the target content item
    const targetContent = document.getElementById(targetId);
    targetContent.style.display = 'block';
  });
});

jQuery(function($){

  $(document).on('click', '.button', function() {
    if ($(this).hasClass('button')) {
      let button = $(this);
      let buttonType = button.data('type');
      let buttonSection = button.data('section');
      let selectorID = button.data('selector');
      let description = '';

      if (buttonSection === 'single' && (buttonType === 'movie' || buttonType === 'tv' || buttonType === 'drama')) {
        description = {released: $('#'+selectorID+'-released-description').val(), upcoming: $('#'+selectorID+'-upcoming-description').val()};
      } else {
        description = $('#'+selectorID+'-description').val();
      }

      console.log($('#support-email').val());

      const formData = {
        'action'     : 'seo_options',
        'section'    : buttonSection,
        'btn_type'   : buttonType,
        'selector'   : selectorID,
        'title'      : $('#'+selectorID+'-title').val(),
        'description': description,
        'keywords'   : $('#'+selectorID+'-keywords')?.val(),
        'robots'     : $('input[name="'+selectorID+'-robots"]:checked').val(),
        'email'      : (selectorID === 'homepage' ? $('#support-email').val() : ''),
       };

      console.log(formData);

      $.ajax({
      url : seo_options_params.ajaxurl,
      data : formData,
      type : 'POST',
      beforeSend : function ( xhr ) {
        button.css('background', '#bfbfbf!important');
        // $('.loading-'+buttonType+'-'+buttonSection).append('<div class="loading"><div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>');
      },
      error: function(xhr, status, error) {
        console.error('Error:', error);
      },
      success : function( data ){
        if( data ) {
          // $('.loading-'+buttonType+'-'+buttonSection).html(data);
          // $('.loading').remove();
          // lazyload();
          console.log(data);
        }
      }
    })
    }
  });
});