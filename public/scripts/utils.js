/**
 * Slugify a string
 * @param s
 * @returns {string}
 */
function slugify(s) {
  return s.toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, "") //   remove diacritics
    .toLowerCase()
    .replace(/\s+/g, '-') //              spaces to dashes
    .replace(/&/g, '-and-') //            ampersand to and
    .replace(/[^\w\-]+/g, '') //          remove non-words
    .replace(/\-\-+/g, '-') //            collapse multiple dashes
    .replace(/^-+/, '') //                trim starting dash
    .replace(/-+$/, '') //                trim ending dash
    ;
}

/**
 * Capitalize first letter
 * @param text
 * @returns {string|string}
 */
function ucFirst(text) {
  return (text[0].toUpperCase() + text.slice(1)) || '';
}

/**
 * Cleanup text
 * @param text
 * @returns {string}
 */
function cleanText(text) {
  if (text !== "") {
    text = text.replace(/\n\n/g, "§");
    text = text.replace(/\n/g, " ");
    text = text.replace(/'/g, "’");
    text = text.replace(/§/g, "\n");
    text = ucFirst(text);
  }
  return text;
}

/**
 * Handle confirmation dialog for element deletion
 */
function confirmDelete(formTitle) {
  $('.confirm-delete').on('click', function(e) {
    e.preventDefault();
    const targetUrl = this.href;
    let item = this.dataset.description || '';
    if (item !== '') item = '<br>- ' + item;
    bootbox.confirm({
      title: formTitle,
      message: 'Confirmez-vous la suppression de cet élément ?' + item,
      buttons: {
        confirm: {
          label: 'Oui, supprimer',
          className: 'btn-primary'
        },
        cancel: {
          label: 'Non',
          className: 'btn-dark'
        }
      },
      callback: function(confirmed) {
        if (confirmed) location.href = targetUrl;
      }
    });
  });
}

/**
 * Enable select2 override
 * @param cssClass
 */
function select2(cssClass) {
  cssClass = cssClass || 'select2';
  if (!cssClass.startsWith('.')) cssClass = "." + cssClass;
  const dropdowns = document.querySelectorAll('select' + cssClass);
  for (const dropdown of dropdowns) {
    $(dropdown).select2();
  }
}
