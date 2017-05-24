<?php
  // Define database queries and updates, process form data

  require_once('../php/db_connect.php');
  require_once('../php/main.php');

  // Constants
  define("PAGE_TITLE", "Systems/Components");
  define("GOOD", "Yo fam we good");
  define("OK", "Hey man things are decent");
  define("BAD", "Brother we have seen better days");

  // Instantiate variables
  $sysName = $revNo = $deadline = $status = $selectSysName = "";
  $noStockErr = $valueErr = $suppNoErr = $typeErr = $mfgNoErr = $nameErr = $revErr = $deadlineErr = $statusErr = "";
  $newComponentActive = $newSystemActive = $viewSystemInfoActive = $submitSuccessfulComponent = $submitSuccessfulSystem = False; // Used in displaying the correct elements and other content

  // Create SQL Prepared Statements - prepare then bind
  // NOTE: Prepared staements are reserved for database updates NOT queries and retrievals
  $insertSystem = $conn->prepare("INSERT INTO SYSTEM VALUES (?, ?, ?, ?)");
  $insertSystem->bind_param("ssss", $sysName, $revNo, $deadline, $status); // "siss" denotes the first parameter is a string, second parmeter is an int, string, string

  $insertComponent = $conn->prepare("INSERT INTO COMPONENT VALUES (?, ?, ?, ?, ?)");
  $insertComponent->bind_param("sssdi", $mfgNo, $type, $suppNo, $value, $noStock);

  // Query declarations
  // Retrieve all columns from the system table
  $allSystems = "SELECT * FROM SYSTEM ORDER BY NAME";

  // Retrieve all names of systems
  $systemNames = "SELECT NAME FROM SYSTEM ORDER BY NAME";

  // Retrieve all columns from the component table
  $allComponents = "SELECT * FROM COMPONENT ORDER BY MFG_PART_NO";

  // Retrieve information about the components for a given system
  // That system's name ("'$selectSysName';") is appended to the query once it is determined (from user input)
  $componentsOnSystem = "SELECT COMPONENT.MFG_PART_NO, TYPE, SUPP_PART_NO, VALUE, NO_STOCK, QUANTITY
    FROM SYSTEM, COMPONENT, USE_ON WHERE COMPONENT.MFG_PART_NO = USE_ON.MFG_PART_NO
    AND SYSTEM.NAME = USE_ON.SYS_NAME AND SYSTEM.REV_NO = USE_ON.REV_NO AND SYSTEM.NAME = ";

  // Get input from the forms and validate it
  // There is new input to process
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(!empty($_POST['submit-new-system'])) { // The new system submission form has new input
      // Make sure we display the correct element, hide the other(s)
      $newSystemActive = True;
      $viewSystemInfoActive = False;
      if(empty($_POST["name"])) { // The system name field is empty
        // Set error message if invalid data
        $nameErr = "System name is required";
      }
      else {
        // Update the add new system variables
        $sysName = correctInput($_POST["name"]); // Validate the raw input
      }

      if(empty($_POST["revNo"])) {
        $revErr = "Revision number is required";
      }
      else {
        $revNo = correctInput($_POST["revNo"]);
      }

      if(empty($_POST["deadline"])) {
        $deadlineErr = "Deadline is required";
      }
      else {
        $deadline = correctInput($_POST["deadline"]);
      }

      if(empty($_POST["status"])) {
        $statusErr = "Status is required";
      }
      else {
        $status = $_POST["status"];
      }

      if($nameErr == "" && $revErr == "" && $deadlineErr == "" && $stausErr == "") {
        // Push change to the DB
        $insertSystem->execute();
        // Mark the submission as successful so the user knows
        $submitSuccessfulSystem = True;
      }
    }

    if(!empty($_POST['submit-new-component'])) { // The new component form has new input
      $newComponentActive = True;
      $newSystemActive = False;
      $viewSystemInfoActive = False;

      if(empty($_POST["mfgNo"])) {
        $mfgNoErr = "Manufacturer part number is required";
      }
      else {
        $mfgNo = correctInput($_POST["mfgNo"]);
      }

      if(empty($_POST["type"])) {
        $typeErr = "Type number is required";
      }
      else {
        $type = $_POST["type"];
      }

      if(empty($_POST["suppNo"])) {
        $suppNoErr = "Supplier part number is required";
      }
      else {
        $suppNo = correctInput($_POST["suppNo"]);
      }

      if(empty($_POST["value"])) {
        $valueErr = "Value is required";
      }
      else {
        $value = correctInput($_POST["value"]);
      }

      if(empty($_POST["noStock"])) {
        $noStockErr = "Number in stock is required";
      }
      else {
        $noStock = correctInput($_POST["noStock"]);
      }

      if($mfgNoErr == "" && $typeErr == "" && $suppNoErr == "" && $valueErr == "" && $noStockErr == "") {
        // Push change to the DB
        $insertComponent->execute();
        // Mark the submission as successful so the user knows
        $submitSuccessfulComponent = True;
      }
    }
  }
  // NOTE: There is no need to validate input from drop down menus
?>

<html>
<?php includeHead(PAGE_TITLE); ?>

<body>
  <?php includeHeader(PAGE_TITLE); ?>

  <!-- Submit new component form  -->
  <div class="form-wrapper">
    <label for="form1"><h2>Record new system</h2></label>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('submit-new-system');" id="form1"/>

    <!-- Each interactive form element's display is set based on which form the user is using with setDisplay(...) (see input processing above) -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" id="submit-new-system" <?php setDisplay($newSystemActive); ?>>
      <p>System Name:</p><input type="text" name="name"/>
      <span class="error">* <?php echo($nameErr);?></span>
      <br/><br/>

      <p>Revision number:</p><input type="text" name="revNo"/>
      <span class="error">* <?php echo($revErr);?></span>
      <br/><br/>

      <p>Deadline (date):</p><input type="text" name="deadline"/>
      <span class="error">* <?php echo($deadlineErr);?></span>
      <br/><br/>

      <p>Status:</p>
      <select name="status">
      <?php
        echo("<option value=''></option>");
        echo("<option value='" . GOOD . "'>" . GOOD . "</option>");
        echo("<option value='" . OK . "'>" . OK . "</option>");
        echo("<option value='" . BAD . "'>" . BAD . "</option>");
      ?>
      </select>
      <span class="error">* <?php echo($statusErr);?></span>
      <br/><br/>

      <input type="submit" name="submit-new-system" value="Submit"/>
      <br/><br/>
      <?php
        // Show the user their update was successful
        if($submitSuccessfulSystem)
          echo(SUBMIT_SUCCESS);
      ?>
    </form>
  </div>

  <!-- See all systems in the database -->
  <div class="form-wrapper">
    <label for="form2"><h2>View all systems</h2></label>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('all-systems');" id="form2"/>

    <table id="all-systems" style="display: none;">
      <tr>
        <th>System Name</th>
        <th>Revision Number</th>
        <th>Deadline</th>
        <th>Status</th>
      </tr>
      <?php
        $result = $conn->query($allSystems);

        // If the query returns results
        if($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
              echo("<tr><td>" . $row["NAME"] . "</td>
                <td>" . $row["REV_NO"] . "</td>
                <td>" . $row["DEADLINE"] . "</td>
                <td>" . $row["STATUS"] . "</td></tr>");
            }
        }
      ?>
    </table>
  </div>

  <!-- Submit new component form  -->
  <div class="form-wrapper">
    <label for="form3"><h2>Record new component</h2></label>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('submit-new-component');" id="form3"/>

    <!-- Each interactive form element's display is set based on which form the user is using with setDisplay(...) (see input processing above) -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" id="submit-new-component" <?php setDisplay($newComponentActive); ?>>
      <p>Manufacturer part no:</p><input type="text" name="mfgNo"/>
      <span class="error">* <?php echo($mfgNoErr);?></span>
      <br/><br/>

      <p>Type:</p>
      <select name="type">
      <?php
        echo("<option value=''></option>");
        echo("<option value='RES'>RES</option>");
        echo("<option value='CAP'>CAP</option>");
        echo("<option value='IND'>IND</option>");
        echo("<option value='OTR'>OTR</option>");
      ?>
      </select>
      <span class="error">* <?php echo($typeErr);?></span>
      <br/><br/>

      <p>Supplier par no:</p><input type="text" name="suppNo"/>
      <span class="error">* <?php echo($suppNoErr);?></span>
      <br/><br/>

      <p>Value:</p><input type="text" name="value"/>
      <span class="error">* <?php echo($valueErr);?></span>
      <br/><br/>

      <p>No in stock:</p><input type="text" name="noStock"/>
      <span class="error">* <?php echo($noStockErr);?></span>
      <br/><br/>

      <input type="submit" name="submit-new-component" value="Submit"/>
      <br/><br/>
      <?php
        // Show the user their update was successful
        if($submitSuccessfulComponent)
          echo(SUBMIT_SUCCESS);
      ?>
    </form>
  </div>

  <!-- See all components in the database -->
  <div class="form-wrapper">
    <label for="form4"><h2>View all components</h2></label>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('all-components');" id="form4"/>

    <table id="all-components" style="display: none;">
      <tr>
        <th>Manufacturer part no.</th>
        <th>Type</th>
        <th>Supplier par no.</th>
        <th>Value</th>
        <th>No. in stock</th>
      </tr>
      <?php
        $result = $conn->query($allComponents);

        // If the query returns results
        if($result->num_rows > 0) {
          // Output data of each row
          while($row = $result->fetch_assoc()) {
            echo("<tr><td>" . $row["MFG_PART_NO"] . "</td>
              <td>" . $row["TYPE"] . "</td>
              <td>" . $row["SUPP_PART_NO"] . "</td>
              <td>" . $row["VALUE"] . "</td>
              <td>" . $row["NO_STOCK"] . "</td></tr>");
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
  $insertSystem->close();
  $insertComponent->close();
  $conn->close();
?>
