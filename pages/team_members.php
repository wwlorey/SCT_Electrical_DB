<?php
  require_once('../php/db_connect.php');
  require_once('../php/main.php');

  // Constants
  define("PAGE_TITLE", "Team Members");

  // Instantiate input variables for submit-new-member form
  $memberName = $DOB = $position = $SSO = $selectSystemName = "";
  $newMemberActive = $viewMemberInfoActive = $submitSuccessful = False;// Used in displaying the correct elements and other content
  $viewSystemInfoActive = False;

  $nameErr = $SSOErr = "";

  // Create SQL Prepared Statements - prepare and bind
  $insertMember = $conn->prepare("INSERT INTO TEAM_MEMBER VALUES (?, ?, ?, ?)");
  $insertMember->bind_param("ssss", $SSO, $memberName, $DOB, $position);

  /*------------Query declarations------------*/
  //Retrieve all columns from the team member table
  $allMemberInfo = "SELECT * FROM TEAM_MEMBER ORDER BY NAME";

  //Retrieve all coumns from the system table
  $allSystemInfo = "SELECT * FROM SYSTEM ORDER BY STATUS";

  // Retrieve information about the drivers of a given car
  // That car's name ("'$selectSystemName';") is appended to the query once it is determined
  $systemAndMemberInfo = "SELECT WORK_ON.SYS_NAME, TEAM_MEMBER.SSO, TEAM_MEMBER.NAME
  FROM SYSTEM, WORK_ON, TEAM_MEMBER WHERE TEAM_MEMBER.SSO = WORK_ON.SSO
  AND SYSTEM.REV_NO = WORK_ON.REV_NO AND SYSTEM.NAME = WORK_ON.SYS_NAME
  AND WORK_ON.SYS_NAME = ";


  // Get input from form and validate it
  // There is new input to process
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(!empty($_POST['submit-new-member'])) { // The new car submission form has new input
      // Make sure we display the correct element, hide the other(s)
      $newMemberActive = True;
      if(empty($_POST["SSO"])) { // The SSO field is empty
        // Set error message if invalid data
        $SSOErr = "SSO is required";
      }
      if(empty($_POST["name"])) {
        $nameErr = "Name is required";
      }

      if($SSOErr == "" && $nameErr == "")
       {
        // Update all variables
        $SSO = correctInput($_POST["SSO"]);
        $memberName = correctInput($_POST["name"]);
        $DOB = correctInput($_POST["DOB"]);
        $position = correctInput($_POST["Pos"]);

        // Push change to the DB
        $insertMember->execute();

        // Mark the submission as successful so the user knows
        $submitSuccessful = True;
      }
    }

    if(!empty($_POST['submit-choose-system'])) { // The choose system form has new input
      // Make sure we display the correct element, hide the other(s)
      $viewSystemInfoActive = True;
      // Update the system select variable
      $selectSystemName = $_POST["systemSelect"];
    }
  }
 ?>
<html>
<?php includeHead(PAGE_TITLE) ?>

<body>
  <?php includeHeader(PAGE_TITLE) ?>

  <div class="form-wrapper">
    <label for="form1"><h2>Record new team member</h2></label>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('submit-new-member');" id="form1"/>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" id="submit-new-member" <?php setDisplay($newMemberActive); ?>>

      <p>Member SSO:</p><input type="text" name = "SSO"/>
      <span class="error">* <?php echo $SSOErr;?></span>
      <br/><br/>

      <p>Member Name:</p><input type="text" name="name"/>
      <span class="error">* <?php echo $nameErr;?></span>
      <br/><br/>

      <p>Member Date of Birth<p><input type="text" name="DOB"/>
      <br/><br/>

      <p>Member Position<p><input type="text" name="Pos"/>
      <br/><br/>

      <input type="submit" name="submit-new-member" value="Submit"/>

      <?php
        if($submitSuccessful)
        {
          echo("<p class='success'>" . SUBMIT_SUCCESS . "</p>");
        }
       ?>
    </form>
  </div>

  <div class="form-wrapper">
    <label for="form2"><h2>View project assignments</h2></label>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('choose-system'); toggleVisible('system-view'); toggleVisible('system-info-err');" id="form2"/>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" id="choose-system" <?php setDisplay($viewSystemInfoActive); ?>>
      <p>Select a project:</p>
      <select name="systemSelect">
        <option value = <?php echo("'" . $selectSystemName . "'"); ?>> <?php echo($selectSystemName) ?> </option>
        <?php
          $result = $conn->query($allSystemInfo);
          // If the query returns results
          if ($result->num_rows > 0) {
              // Display the data in each row as an option
              while($row = $result->fetch_assoc()) {
                echo("<option value='" . $row[NAME] . "'>" . $row[NAME] . "</option>");
              }
          }
        ?>
      </select>
      <br/><br/>

      <!-- Take the system name from the user and use it to display information -->
      <input type="submit" name="submit-choose-system" value="Submit"/>
      <br/><br/>
    </form>

    <table id="system-view" <?php setDisplay($viewSystemInfoActive); ?>>
      <?php
        $sqlCode = $systemAndMemberInfo . "'$selectSystemName';";
        $result = $conn->query($sqlCode);

        if($result->num_rows > 0) {
          // Echo the table header
          echo("<tr><th>System Name</th>
            <th>Member SSO</th>
            <th>Member's Name</th></tr>");

          // Output data of each row
          while($row = $result->fetch_assoc()) {
            echo("<tr><td>" . $row["SYS_NAME"] . "</td>
              <td>" . $row["SSO"] . "</td>
              <td>" . $row["NAME"] . "</td></tr>");
          }
        }
        else if($selectSystemName != "") {
          echo("<p class='error' id='system-info-err'>There are no recorded team members for the given system.</p>");
        }
      ?>
    </table>

  </div>

  <div class="form-wrapper">
    <label for="form3"><h2>View all members</h2></label>
    <input type="image" src="../resources/dropdown_arrow.png" class="show-hide" onclick="toggleVisible('allMembers-table');" id="form3"/>

    <table id="allMembers-table" style="display: none;">
      <tr>
        <th>Name</th>
        <th>SSO</th>
        <th>DOB</th>
        <th>Position</th>
      </tr>
      <?php
        $result = $conn->query($allMemberInfo);

        // If the query returns results
        if ($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
              echo("<tr><td>" . $row["NAME"] . "</td>
                <td>" . $row["SSO"] . "</td><td>" . $row["DOB"] . "</td>
                <td>" . $row["POSITION"] . "</td></tr>");
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
  $insertMember->close();
  $conn->close();
  //In Memoriam: My Sanity 1996-2017
?>
