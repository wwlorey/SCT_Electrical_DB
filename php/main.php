<!-- General functions used accross all pages -->

<?php
  // Function declarations
  function correct_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
?>
