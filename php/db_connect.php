<!-- Establishes connection to the mySQL database -->

<?php
  // Credentials for logging into the DB
  $servername = "localhost";
  $username = "wwloreyx_admin";
  $password = "heck";
  $dbName = "wwloreyx_SCTEEDB";

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbName);

  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
?>
