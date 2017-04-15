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
