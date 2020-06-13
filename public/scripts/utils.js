/**
 * Slugify a string
 * @param s
 * @returns {string}
 */
function slugify(s) {
  return s.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, "") //remove diacritics
    .toLowerCase()
    .replace(/\s+/g, '-') //spaces to dashes
    .replace(/&/g, '-and-') //ampersand to and
    .replace(/[^\w\-]+/g, '') //remove non-words
    .replace(/\-\-+/g, '-') //collapse multiple dashes
    .replace(/^-+/, '') //trim starting dash
    .replace(/-+$/, ''); //trim ending dash
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
    text = text[0].toUpperCase() + text.slice(1);
  }
  return text;
}

/**
 * Handle confirmation dialog for element deletion
 */
function confirmDelete() {
  $('.confirm-delete').confirm({
    title: 'Confirmation requise !',
    content: 'Confirmez-vous la suppression de cet élément ?',
    buttons: {
      confirm: {
        text: 'Oui, supprimer',
        action: function () {
          location.href = this.$target.attr('href');
          return true;
        }
      },
      cancel: {
        text: 'Non',
        action: function () {
          return true;
        }
      }
    }
  });
}