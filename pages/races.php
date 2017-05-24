<?php
  // Define database queries and updates, process form data

  // Constants
  define("PAGE_TITLE", "Races");

  require_once('../php/db_connect.php');
  require_once('../php/main.php');

  // Instantiate variables
  $date = $carName = $raceTitle = $location = $dateErr = $carErr = $raceErr = $locErr = $selectRaceName = $selectRaceDate = $dateSelectErr = $raceSelectErr = "";
  $insertRaceActive = $submitSuccessful = $viewRaceInfoActive = $selectRaceInfoActive = $selectDateActive = False; // Used in displaying the correct elements and other content

  // Create SQL Prepared Statements - prepare then bind
  // NOTE: Prepared staements are reserved for database updates NOT queries and retrievals
  $insertRace = $conn->prepare("INSERT INTO COMPETITION VALUES (?, ?, ?, ?)");
  $insertRace->bind_param("ssss", $date, $carName, $raceTitle, $location); // "ssss" - all four parameters are strings

  // Query declarations
  // Retrieve all names of vehicles
  $allCarNames = "SELECT NAME FROM VEHICLE ORDER BY NAME";

  // Retrieve all columns from the competition table
  $allRaceInfo = "SELECT * FROM COMPETITION ORDER BY DATE";

  // Retrieve all names of races
  $allRaceNames = "SELECT DISTINCT TITLE FROM COMPETITION ORDER BY TITLE";

  // Retrieve all race dates given a race name (which needs to be appended later)
  $raceDates = "SELECT DATE FROM COMPETITION WHERE TITLE = ";

  // Retrieve information about a given race
  $raceAwards = "SELECT VEHICLE.NAME, AWARD.AWARD_TITLE FROM COMPETITION, AWARD, VEHICLE
    WHERE AWARD.COMPETITION_DATE = COMPETITION.DATE AND VEHICLE.NAME = COMPETITION.CAR_NAME AND COMPETITION.DATE = ";

  // Get input from the forms and validate it
  // There is new input to process
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(!empty($_POST['submit-new-race'])) { // The new race submission form has new input
      // Make sure we display the correct element, hide the other(s)
      $insertRaceActive = True;
      $selectRaceInfoActive = False;
      $viewRaceInfoActive = False;

      if(empty($_POST["date"])) { // The date field is empty
        $dateErr = "Date is required"; // Set error message if invalid data
      }
      else { // Update the date variable
        $date = correctInput($_POST["date"]); // Validate the raw input
      }

      if(empty($_POST['car'])) {
        $carErr = "Car name is required";
      }
      else {
        $carName = $_POST["car"];
      }

      if(empty($_POST["raceTitle"])) {
        $raceErr = "Race title is required";
      }
      else {
        $raceTitle = correctInput($_POST["raceTitle"]);
      }

      if(empty($_POST["location"])) {
        $locErr = "Location is required";
      }
      else {
        $location = correctInput($_POST["location"]);
      }

      if($dateErr == "" && $carErr == "" && $raceErr == "" && $locErr == "")  { // There are no errors
        // Push change to the DB
        $insertRace->execute();

        // Mark the submission as successful so the user knows
        $submitSuccessful = True;

        // Reset the race attribute variables so they are not displayed
        $date = $carName = $raceTitle = $location = "";
      }
    }



    if(!empty($_POST['submit-choose-race'])) {
      $selectRaceInfoActive = True;
      $viewRaceInfoActive = False;

      if(empty($_POST["raceSelect"])) {
        $raceSelectErr = "Race title is required";
      }
      else {
        $selectRaceName = $_POST["raceSelect"];
        $selectDateActive = True;
      }
    }


    if(!empty($_POST['submit-date'])) {
      $selectRaceInfoActive = True;
      $selectDateActive = True;

      if(empty($_POST['dateSelect'])) {
        $selectDateActive = False;
        $dateSelectErr = "Date is required";
      }
      else {
        $selectRaceName = $_POST["raceSelect"];
        $selectRaceDate = $_POST["dateSelect"];
        $viewRaceInfoActive = True;
      }
    }
  }
  // NOTE: There is no need to validate input from drop down menus
?>

<html>
<?php includeHead(PAGE_TITLE); ?>

<body>
  <?php includeHeader(PAGE_TITLE); ?>


  <!-- Submit new race form  -->
  <div class="form-wrapper">
    <label for="form1"><h2>Record new race</h2></label>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('submit-new-race');" id="form1"/>

    <!-- Each interactive form element's display is set based on which form the user is using with setDisplay(...) (see input processing above) -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" id="submit-new-race" <?php setDisplay($insertRaceActive); ?>>
      <p>Race date:</p>
      <input type="text" name="date" value="<?php echo($date); ?>"/>
      <span class="error">* <?php echo $dateErr;?></span>
      <br/><br/>

      <p>Name of car that raced:</p>
      <select name="car">
      <?php // Display all car names as options
        // First, display their current choice
        echo("<option value='" . $carName . "'>" . $carName . "</option>");

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
      <span class="error">* <?php echo $carErr;?></span>
      <br/><br/>

      <p>Race Title:</p><input type="text" name="raceTitle" value="<?php echo($raceTitle); ?>"/>
      <span class="error">* <?php echo $raceErr;?></span>
      <br/><br/>

      <p>Location:</p><input type="text" name="location" value="<?php echo($location); ?>"/>
      <span class="error">* <?php echo $locErr;?></span>
      <br/><br/>

      <input type="submit" name="submit-new-race" value="Submit"/>
      <br/><br/>
      <?php
        // Show the user their update was successful
        if($submitSuccessful)
        {
          echo(SUBMIT_SUCCESS);
        }
      ?>
    </form>
  </div>


  <!-- View car and their awards form - the user can select a car to see more info. about -->
  <div class="form-wrapper">
    <label for="form2"><h2>View awards</h2></label>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('choose-race'); toggleVisible('race-info-view'); toggleVisible('award-select-err');" id="form2"/>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" id="choose-race" <?php setDisplay($selectRaceInfoActive); ?>>
      <p>Select race name:</p>
      <select name="raceSelect">
        <!-- Display the user's current choice -->
        <option value = <?php echo("'" . $selectRaceName . "'"); ?>> <?php echo($selectRaceName) ?> </option>
        <?php
          // Display each <option> from the query
          $result = $conn->query($allRaceNames);
          // If the query returns results
          if($result->num_rows > 0) {
            // Display the data in each row as an option
            while($row = $result->fetch_assoc()) {
              echo("<option value='" . $row[TITLE] . "'>" . $row[TITLE] . "</option>");
            }
          }
        ?>
      </select>
      <br/><br/>

      <input type="submit" name="submit-choose-race" value="Narrow down your search"/>
      <br/><br/>

      <span class="error"><?php echo $raceSelectErr;?></span>
      <span class="error"><?php echo $dateSelectErr;?></span>

      <div id="select-date"  <?php setDisplay($selectDateActive); ?>>
        <p>Select race date:</p>
        <select name="dateSelect">
          <!-- Display the user's current choice -->
          <option value='<?php echo($selectRaceDate); ?>'><?php echo($selectRaceDate); ?></option>
          <?php
            if($selectRaceName != "") {
              // Complete the raceDates query
              $sqlCode = $raceDates . "'" . $selectRaceName . "';";

              // Display each <option> from the query
              $result = $conn->query($sqlCode);
              // If the query returns results
              if ($result->num_rows > 0) {

                  // Display the data in each row as an option
                  while($row = $result->fetch_assoc()) {
                    echo("<option value='" . $row[DATE] . "'>" . $row[DATE] . "</option>");
                  }
              }
            }
          ?>
        </select>
        <br/><br/>

        <input type="submit" name="submit-date" value="See results"/>
        <br/><br/>
      </div>
    </form>

    <table id="race-info-view" <?php setDisplay($viewRaceInfoActive); ?>>
      <?php
        if($selectRaceDate != "") {
          $sqlCode = $raceAwards . "'$selectRaceDate';";
          $result = $conn->query($sqlCode);

          if($result->num_rows > 0) {
            // Echo the table header
            echo("<tr><th>Car Name</th>
              <th>Award</th></tr>");

            // Output data of each row
            while($row = $result->fetch_assoc()) {
              echo("<tr><td>" . $row["NAME"] . "</td>
                <td>" . $row["AWARD_TITLE"] . "</td></tr>");
            }
          }
          else if($selectRaceDate != "") {
            echo("<p class='error' id='award-select-err'>There is no information for the given race.</p>");
          }
        }
      ?>
    </table>
  </div>

  <!-- See all races in the database -->
  <div class="form-wrapper">
    <label for="form3"><h2>View all races</h2></label>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('all-races');" id="form3"/>

    <table id="all-races" style="display: none;">
      <tr>
        <th>Date</th>
        <th>Car Name</th>
        <th>Race Name</th>
        <th>Location</th>
      </tr>
      <?php
        $result = $conn->query($allRaceInfo);

        // If the query returns results
        if($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
              echo("<tr><td>" . $row["Date"] . "</td>
                <td>" . $row["CAR_NAME"] . "</td>
                <td>" . $row["TITLE"] . "</td>
                <td>" . $row["LOCATION"] . "</td></tr>");
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
  $insertRace->close();
  $conn->close();
?>
