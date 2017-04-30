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

// Returns if the display of the element with given id is 'none' or not
// (whether or not the element is visible)
function isVisible(id) {
  var elem = document.getElementById(id);
  if(elem.style.display == 'none') {
    return false;
  }
  else {
    return true;
  }
}




/*
function displayElemIfError(id, <?php //echo($hasError); ?>) {
  var elem = document.getElementById(id);
  console.log("HERE");
  if(hasError) {
    elem.style.display = 'block';
  }
  else {
    elem.style.display = 'none';
  }
  return;
}
*/
