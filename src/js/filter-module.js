// Define resetModalPopup function (if needed)
function clearAllFilters() {
  clearFilters('channel');
  clearFilters('country');
  clearFilters('genre');
  clearFilters('language');
  clearFilters('year');
  clearFilters('sort_by');
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

  const countElement = document.getElementById(formElementId+'SelectCnt');
  countElement.textContent = formElementId == 'sort_by' ? 'Sort by: '+document.querySelector(`label[for="${form.querySelector('input[type="radio"]:checked').id}"]`).textContent : (checkedCount ? checkedCount : '');

  const activeItemsButton = document.getElementById(formElementId+'HighlightActive');
  const selectedItems = [];

  for (const checkbox of checkedBoxes) {
    selectedItems.push(checkbox.value);
  }

  const selectedItemsButton = document.getElementById('loadmore_container');
  if (formElementId==='channel') {selectedItemsButton.dataset.channel = JSON.stringify(selectedItems);}
  if (formElementId==='country') {selectedItemsButton.dataset.country = JSON.stringify(selectedItems);}
  if (formElementId==='genre') {selectedItemsButton.dataset.genre = JSON.stringify(selectedItems);}
  if (formElementId==='language') {selectedItemsButton.dataset.lang = JSON.stringify(selectedItems);}
  if (formElementId==='year') {selectedItemsButton.dataset.year = JSON.stringify(selectedItems);}
  if (formElementId==='sort_by') {selectedItemsButton.dataset.sort_by = form.querySelector('input[type="radio"]:checked').value;}
  

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