<?php
  // Define database queries and updates, process form data

  require_once('../php/db_connect.php');
  require_once('../php/main.php');

  // Instantiate variables
  $newCarName = $yearCompleted = $selectCarName = $nameErr = $newCarOpen = $carInfoOpen = "";
  $newCarActive = $viewCarInfoActive = $submitSuccessful = False; // Used in displaying the correct elements and other content

  // Create SQL Prepared Statements - prepare then bind
  // NOTE: Prepared staements are reserved for database updates NOT queries and retrievals
  $insertVehicle = $conn->prepare("INSERT INTO VEHICLE VALUES (?, ?)");
  $insertVehicle->bind_param("si", $newCarName, $yearCompleted); // "si" denotes the first parameter is a string, second parmeter is an int

  // Query declarations
  // Retrieve all columns from the vehicle table
  $allCarInfo = "SELECT * FROM VEHICLE ORDER BY YEAR_COMPLETED";

  // Retrieve all names of vehicles
  $allCarNames = "SELECT NAME FROM VEHICLE ORDER BY NAME";

  // Retrieve information about the drivers of a given car
  // That car's name ("'$selectCarName';") is appended to the query once it is determined
  $carAndDriverInfo = "SELECT CAR_NAME, TEAM_MEMBER.NAME, POSITION
  FROM TEAM_MEMBER, DRIVE, VEHICLE WHERE DRIVE.CAR_NAME = VEHICLE.NAME
  AND DRIVE.SSO = TEAM_MEMBER.SSO AND VEHICLE.NAME = ";

  // Get input from the forms and validate it
  // There is new input to process
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(!empty($_POST['submit-new-car'])) { // The new car submission form has new input
      // Make sure we display the correct element, hide the other(s)
      $newCarActive = True;
      $viewCarInfoActive = False;
      if(empty($_POST["name"])) { // The car name field is empty
        // Set error message if invalid data
        $nameErr = "Name is required";
      }
      else {
        // Update the add new car variables
        $newCarName = correctInput($_POST["name"]); // Validate the raw input
        $yearCompleted = $_POST["year"];

        // Push change to the DB
        $insertVehicle->execute();

        // Mark the submission as successful so the user knows
        $submitSuccessful = True;
      }
    }

    if(!empty($_POST['submit-choose-car'])) { // The select car name form has new input
      // Make sure we display the correct element, hide the other(s)
      $viewCarInfoActive = True;
      $newCarActive = False;
      // Update the select car variable
      $selectCarName = $_POST["nameSelect"];
    }
  }
  // NOTE: There is no need to validate input from drop down menus
?>

<html>
<?php includeHead("Cars"); ?>

<body>
  <?php includeHeader("Cars"); ?>

  <!-- Submit new car form  -->
  <div class="form-wrapper">
    <h2>Record new car</h2>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('submit-new-car');"/>

    <!-- Each interactive form element's display is set based on which form the user is using with setDisplay(...) (see input processing above) -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" id="submit-new-car" <?php setDisplay($newCarActive); ?>>
      <p>Car Name:</p><input type="text" name="name"/>
      <span class="error">* <?php echo $nameErr;?></span>
      <br/><br/>

      <p>Year Completed:</p>
      <select name="year">
      <?php
        // Display a list of years from 2018 to 1993 as options
        for($i = 2018; $i >=1993; $i--) {
          echo("<option value='" . $i . "'>" . $i . "</option>");
        }
      ?>
      </select>
      <br/><br/>

      <input type="submit" name="submit-new-car" value="Submit"/>
      <br/><br/>
      <?php
        // Show the user their update was successful
        if($submitSuccessful)
          echo(SUBMIT_SUCCESS);
      ?>
    </form>
  </div>


  <!-- View car drivers form - the user can select a car to see more info. about -->
  <div class="form-wrapper">
    <h2>View drivers</h2>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('choose-car'); toggleVisible('driver-view');"/>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" id="choose-car" <?php setDisplay($viewCarInfoActive); ?>>
      <p>Select car name:</p>
      <select name="nameSelect">
        <option value = <?php echo("'" . $selectCarName . "'"); ?>> <?php echo($selectCarName) ?> </option>
        <?php
          $result = $conn->query($allCarNames);
          // If the query returns results
          if($result->num_rows > 0) {
              // Display the data in each row as an option
              while($row = $result->fetch_assoc()) {
                echo("<option value='" . $row[NAME] . "'>" . $row[NAME] . "</option>");
              }
          }
        ?>
      </select>
      <br/><br/>

      <!-- Take the car name from the user and use it to display information -->
      <input type="submit" name="submit-choose-car" value="Submit"/>
      <br/><br/>
    </form>

    <table id="driver-view" <?php setDisplay($viewCarInfoActive); ?>>
      <?php
        $sqlCode = $carAndDriverInfo . "'$selectCarName';";
        $result = $conn->query($sqlCode);

        if($result->num_rows > 0) {
          // Echo the table header
          echo("<tr><th>Car Name</th>
            <th>Driver Name</th>
            <th>Driver's Position on Team</th></tr>");

          // Output data of each row
          while($row = $result->fetch_assoc()) {
            echo("<tr><td>" . $row["CAR_NAME"] . "</td>
              <td>" . $row["NAME"] . "</td>
              <td>" . $row["POSITION"] . "</td></tr>");
          }
        }
        else if($selectCarName != "") {
          echo("<p class='error'>There are no recorded drivers for the given vehicle.</p>");
        }
      ?>
    </table>
  </div>


  <!-- See all cars in the database -->
  <div class="form-wrapper">
    <h2>View all solar cars</h2>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('all-cars');"/>

    <table id="all-cars" style="display: none;">
      <tr>
        <th>Car Name</th>
        <th>Year Completed</th>
      </tr>
      <?php
        $result = $conn->query($allCarInfo);

        // If the query returns results
        if($result->num_rows > 0) {
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

  <script src="../main.js"></script>
</body>
</html>

<?php
  // Close the prepared statements and DB connection
  $insertVehicle->close();
  $conn->close();
?>
