<?php
  // Credentials for logging into DB
  $servername = "localhost";
  $username = "wwloreyx_admin";
  $password = "heck";
  $dbName = "wwloreyx_SCTEEDB";

  // Create DB connection
  $conn = new mysqli($servername, $username, $password, $dbName);

  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
?>
