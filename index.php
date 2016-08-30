<!DOCTYPE HTML>  
<html>
  <head>
    <style>
      .error {color: #FF0000;}
      table, th, td {border: 1px solid black;}
    </style>
  </head>
  
  <body>  
    
  <?php

    // The database settings are kept in a separate file
    include ("database_vars.php");

    // define variables and set to "empty" values
    $dateErr = $emailErr = $commentErr = $email = $comment = $message ="";
    $nowDateTime = new DateTime('NOW');
    $displayFormat = "d-m-Y H:i";
    $mysqlFormat = "Y-m-d H:i:s";
    $current = $nowDateTime->format($displayFormat);
    $dateValidated = $emailValidated = $commentValidated = $result = false;

    // Starting the validation process when the formdata is posted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

      // Check the date field
      if (empty($_POST["date"])) {
        $dateErr = "Datum is verplicht";
      } else {
        if ($_POST["date"] == $current) {
          $date = $current;
          $dateValidated = true;
        } else {
          // Clean the entered data
          $date = clean_input($_POST["date"]);
          // Check if the entered value is a valid date in the format we want
          if (!is_valid_date($_POST["date"], "d-m-Y H:i")) {
            $dateErr = "Ongeldige datum";

          } else {
            $dateValidated = true;
          }
        }
      }

      // Check the email field
      if (empty($_POST["email"])) {
        $emailErr = "Email is verplicht";
      } else {
        // Clean the entered data
        $email = clean_input($_POST["email"]);
        // check if e-mail address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $emailErr = "Ongeldig email formaat"; 
        } else {
          $emailValidated = true;
        }
      }

      // Check the comment field
      if (empty($_POST["comment"])) {
        $commentErr = "Bericht is verplicht";
      } else {
        $comment = clean_input($_POST["comment"]);
        $commentValidated = true;
      }

      // If everything has been validated we need to write the data to the database
      if ($dateValidated && emailValidated && $commentValidated) {

        // Open a connection to the database (with variables defined in included file)
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // To write the date as a DateTime field in the database it needs the correct format
        $mysqlDate = date($mysqlFormat, strtotime($date));
        $sql = "INSERT INTO sinnerg (form_date, email, comment) "
                . "VALUES ('$mysqlDate', '$email', '$comment')";

        if ($conn->query($sql) === TRUE) {
            $result = true;
            $message = "Succes! Nieuw record opgeslagen in de database";
        } else {
            $result = false;
            $message = "Error: " . $sql . "<br />" . $conn->error;
        }

        // Close the connection
        $conn->close();

        // We no longer need the data entered in the form after saving it successfully
        if ($result) {
          $date = $email = $comment = "";
        }
      }
    }

    function is_valid_date($date, $format) {
      $test = DateTime::createFromFormat($format, $date);
      $valid = DateTime::getLastErrors();         
      return ($valid['warning_count']==0 and $valid['error_count']==0);
    }

    function clean_input($data) {
      $data = trim($data); // Strip unnecessary characters
      $data = stripslashes($data); // Remove backslashes
      $data = htmlspecialchars($data); // Convert special characters to HTML entities
      return $data;
    }

  ?>
  
  <!-- Building the form -->    
  
  
  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
      
    <h2>Proefopdracht SinnerG BV</h2>
    
    <span class="error">* Veld is verplicht</span>
    <br /><br />
    
    Datum: 
    <input type="text" name="date" value="<?php echo empty($date) ? $current : $date ;?>">
    <span class="error">* <?php echo $dateErr;?></span>
    <br /><br />
    
    E-mail:
    <input type="text" name="email" value="<?php echo $email;?>">
    <span class="error">* <?php echo $emailErr;?></span>
    <br /><br />
    
    Bericht: <textarea name="comment" rows="5" cols="40" style = "vertical-align: top"​>
      <?php echo $comment;?></textarea>
    <span class="error">* <?php echo $commentErr;?></span>
    <br /><br />
    
    <input type="submit" name="submit" value="Verzenden"> 

  </form>
  <br /><br />
  
  <?php echo $message ?> 
  <br /><br />
  <hr>
  <br /><br />
  
  
  
  <!-- Next part retrieves the data from the database and displays it in a table -->
  
  <?php

    // Open a connection to the database (with variables defined in included file)
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } 

    $sql = "SELECT * FROM sinnerg ORDER BY fid DESC"; // We want the newest on top
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<table><tr><th width="50px">ID</th><th width="200px">Datum</th>'
            . '<th width="200px">Email</th><th width="200px">Bericht</th></tr>';
        // Output data of each row
        while($row = $result->fetch_assoc()) {
          // Date in the datatabase is in a different format then we want to display
          $displayDate = date($displayFormat, strtotime($row["form_date"]));
            echo "<tr><td>" . $row["fid"] . "</td><td>" . $displayDate. "</td><td>"
                    . $row["email"] . "</td><td>" . $row["comment"] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
    $conn->close();
  ?>  
  

</body>
</html>