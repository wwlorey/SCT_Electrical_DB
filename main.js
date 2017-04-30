// JS functions uniform accross all pages

// toggleVisible(...) toggles the CSS display property of an element with given
// ID between 'block' and 'none'.
// It is used when drop-down arrows are pressed in the PHP pages.
function toggleVisible(id) {
  var elem = document.getElementById(id);
  if(elem.style.display == 'none') {
    elem.style.display = 'block';
  }
  else {
    elem.style.display = 'none';
  }
  return;
}
