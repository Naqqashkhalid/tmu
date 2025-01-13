jQuery(function($){

  function ajax_loadmore(diff){
    const dataElement = $(".load_more_box");
    let page = diff === 0 ? 1 : diff+parseInt(dataElement.data("page"));
    const last_page = dataElement.data("total-pages");

    const data = {
      'action' : 'people',
      'sort_by': dataElement.data("sort_by"),
      'profession': dataElement.data("profession"),
      'networth': dataElement.data("networth"),
      'country': dataElement.data("country"),
      'ppp': dataElement.data("posts-per-page"),
      'totalPages': dataElement.data("total-pages"),
      'page': page
    }

    console.log(data);
 
    $.ajax({ // you can also use $.post here
      url : ajax_people_params.ajaxurl, // AJAX handler
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


// Define resetModalPopup function (if needed)
function clearAllFilters() {
  clearFilters('profession');
  clearFilters('sort_by');
  clearFilters('networth');
  clearFilters('country');
}

function countCheckedBoxes(formElementId) {
  const form = document.getElementById(formElementId+'_button_modals');
  if (!form) return; // Handle case where form doesn't exist
  const checkedBoxes = form.querySelectorAll('input[type="checkbox"]:checked');
  return checkedBoxes.length;
}

function updateCount(formElementId) {
  const form = document.getElementById(formElementId+'_button_modals');
  if (!form) return; // Handle case where form doesn't exist
  const checkedBoxes = form.querySelectorAll('input[type="checkbox"]:checked');
  const checkedCount = checkedBoxes.length;

  const activeItemsButton = document.getElementById(formElementId+'HighlightActive');
  const selectedItems = [];

  for (const checkbox of checkedBoxes) {
    selectedItems.push(checkbox.value);
  }

  const countElement = document.getElementById(formElementId+'SelectCnt');
  countElement.textContent = formElementId == 'sort_by' ? 'Sort by: '+document.querySelector(`label[for="${form.querySelector('input[type="radio"]:checked').id}"]`).textContent : (checkedCount ? checkedCount : '');

  const selectedItemsButton = document.getElementById('loadmore_container');
  if (formElementId==='profession') {selectedItemsButton.dataset.profession = JSON.stringify(selectedItems);}
  if (formElementId==='sort_by') {selectedItemsButton.dataset.sort_by = form.querySelector('input[type="radio"]:checked').value;}
  if (formElementId==='networth') {selectedItemsButton.dataset.networth = JSON.stringify(selectedItems);}
  if (formElementId==='country') {selectedItemsButton.dataset.country = JSON.stringify(selectedItems);}
  

  if (checkedCount === 0) {
    activeItemsButton.classList.remove('myBtn_multi_active');
  } else {
    activeItemsButton.classList.add('myBtn_multi_active');
  }
}

function clearFilters(formElementId) {
  const form = document.getElementById(formElementId+'_button_modals');
  if (!form) return; // Handle case where form doesn't exist
  if (formElementId == 'sort_by') {
    const sortby = form.querySelector('input[type="radio"]:checked');
    if(sortby) sortby.checked = false;
    document.getElementById(formElementId+'SelectCnt').textContent = 'Sort By';
  } else {
    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
    for (const checkbox of checkboxes) {
      checkbox.checked = false;
    }
    updateCount(formElementId); // Update elment count after clearing filters
  }
  
  form.style.display = 'none';
}

function applyFilters(formElementId) {
  const form = document.getElementById(formElementId+'_button_modals');
  if (!form) return; // Handle case where form doesn't exist
  const checkboxes = form.querySelectorAll('input[type="checkbox"]');
  updateCount(formElementId);
  form.style.display = 'none';
}

function select_filters(formElementId) {
    const form = document.getElementById(formElementId+'_button_modals');
    form.style.display = "block";
}

for (const button of document.querySelectorAll('.close_multi')) {
  button.addEventListener('click', function() {
    document.getElementById(this.dataset.close+'_button_modals').style.display = 'none';
  });
}

