jQuery(function($){

  const rateThisButton = $('.rate-this-button');
  const rateThisPopModal = $('.rate-this-pop-modal');
  const closeButton = $('.close-pop-modal'); // Assuming you have a close button with this class
  const selectStarsRating = $('.select-stars-rating');
  const rateSubmitButton = $('.rate-submit-button');

  // display modal on click button
  rateThisButton.click(function() { rateThisPopModal.show(); });
  // Close modal on close button click
  closeButton.click(function() { rateThisPopModal.hide(); });

  // Get selected star value on submit button click
  rateSubmitButton.click(function() {
    const selectedStar = selectStarsRating.find('input:checked');
    if (selectedStar.length) { // Checks if any star is selected
      const selectedValue = selectedStar.val();
      console.log('Selected value:', selectedValue);
      rateThisPopModal.hide();

      rateThisButton.html('Your Rating '+ selectedValue +'/10');

      $.ajax({
      url : rating_params.ajaxurl,
      data : { 'action' : 'rating', 'rating': selectedValue, 'postID': rateSubmitButton.data('post-id') },
      type : 'POST',
      beforeSend : function ( xhr ) {
        // $('#'+container).append('<div class="loading"><div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>');
      },
      success : function( data ){
        // rateThisPopModal.hide();
        if( data ) {
          let objData = JSON.parse(data);
          document.getElementById("avg-rating").innerHTML = objData.average;
          document.getElementById("ttl-votes").innerHTML = objData.count + ' Votes';
        }
      }
    })

    }
  });

});