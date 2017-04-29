<?php
  include '../php/db_connect.php';
  include '../php/main.php';

  // Create SQL Prepared Statements - prepare and bind
  $insertVehicle = $conn->prepare("INSERT INTO VEHICLE VALUES (?, ?)");
  $insertVehicle->bind_param("si", $carName, $yearCompleted);

  // Instantiate input variables for car-form form
  $carName = $yearCompleted = "";
  $pushData = true;
  $hasError = false;

  $nameErr = "";

  // Get input from form and validate it
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty($_POST["name"])) {
      //Set error messages if invalid data
      $nameErr = "Name is required";
      $pushData = false;
      $hasError = true;
    }
    else {
      $pushData = true;
      $hasError = false;
    }

    if($pushData) {
      $carName = correct_input($_POST["name"]);
      $yearCompleted = correct_input($_POST["year"]);
      $insertVehicle->execute();
    }
  }
?>

<html>
<head>
  <link href="../styles/main.css" type="text/css" rel="stylesheet"/>

  <link href="../resources/icon.png" rel="icon"/>
  <title>SCT | Cars</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

  <header>
    <a href="../index.html"><img src="../resources/large_sunburst.png"></a>
    <h1>SCT | Cars</h1>

    <div id="nav">
      <a href="team_members.php">Team Members</a>
      <a href="cars.php">Cars</a>
      <a href="systems.php">Systems</a>
      <a href="races.php">Races</a>
    </div>
  </header>

  <div class="form-wrapper">
    <h2>Record new car</h2>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('car-form');"/>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" id="car-form" style="display: none;">
    <p>Car Name:</p><input type="text" name="name"/>
    <span class="error">* <?php echo $nameErr;?></span>
    <br><br>

    <p>Year Completed:</p>
    <select name="year">
    <?php
      for($i = 2018; $i >=1993; $i--) {
        echo("<option value='" . $i . "'>" . $i . "</option>");
      }
    ?>
    </select>
    <br><br>

    <input type="submit" name="submit" value="Submit"/>
    </form>
  </div>

  <div class="form-wrapper">
    <h2>View solar cars</h2>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('car-table');"/>

    <table id="car-table" style="display: none;">
      <tr>
        <th>Car Name</th>
        <th>Year Completed</th>
      </tr>
      <?php
        $sql_code = "SELECT * FROM VEHICLE ORDER BY YEAR_COMPLETED";
        $result = $conn->query($sql_code);

        // If the query returns results
        if ($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
              echo("<tr><td>" . $row["NAME"] . "</td>
                <td>" . $row["YEAR_COMPLETED"] . "</td></tr>");
            }
        }
      ?>
    </table>
  </div>
  <br/><br/>

  <?php //echo("<script src='../main.js'>window.onload = displayElemIfError('car-form'," .$hasError. "); console.log(" . $hasError . "11)</script>");  ?>
  <script src="../main.js"></script>
</body>
</html>

<?php
  // Close the prepared statements and DB connection
  $insertVehicle->close();
  $conn->close();
?>
