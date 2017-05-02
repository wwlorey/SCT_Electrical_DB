<!-- General functions and constants used accross all pages -->

<?php
  // Constants
  define("SUBMIT_SUCCESS", "<p class='success'>Your update has been submitted successfully!</p>");

  // Function declarations
  // correct_input(...) verifys plain text (string) input from forms
  function correctInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

  // setDisplay(...) echos the 'block;' or 'none;' display property depending on the input bool.
  // This function is used for displaying the correct content to the user on PHP pages.
  function setDisplay($bool) {
    if($bool)
      echo("style = 'display: block;'");
    else
      echo("style = 'display: none;'");
  }

  // includeHead(...) echos the uniform html head used accross all PHP pages
  // inserting the given $title (a string) as the title of the page
  function includeHead($title) {
    echo('<head>
      <link href="../styles/main.css" type="text/css" rel="stylesheet"/>
      <link href="../resources/icon.png" rel="icon"/>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>SCT | ' . $title . '</title>
      </head>');
  }

  // includeHeader(...) echos the uniform html header used accross all PHP pages
  // inserting the given $title (a string) as the main heading of the page
  function includeHeader($title) {
    echo('<header>
      <a href="../index.html"><img src="../resources/large_sunburst.png"></a>
      <h1>SCT | ' . $title . '</h1>

      <div id="nav">
        <a href="team_members.php">Team Members</a>
        <a href="cars.php">Cars</a>
        <a href="systems.php">Systems</a>
        <a href="races.php">Races</a>
      </div>
      </header>');
  }
?>
