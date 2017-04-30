function submitCarSelection() {
  var name = $("#nameSelect").val();
  $.post("../php/cars_submit.php", { name: name },
  function(data) {
    $('#results').html(data);
    $('#pick-car')[0].reset();
  });
}
