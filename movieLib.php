<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="style.css">
  <script src='functions.js'></script>
  <title>HTML Assignment 4 Part 2</title>
</head>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
include 'configuration.php';

$mysqli = new mysqli($servername, $username, $password, $database);
if ($mysqli->connect_errno) {
  echo "Unable to connect to MYSQL <br>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (count($_POST) > 0 && isset($_POST['movTitle']) && isset($_POST['genre']) && isset($_POST['movLength'])) {

    if (!($stmt = $mysqli->prepare("INSERT INTO movies(name, category, length, rented) VALUES (?, ?, ?, ?)"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    $movTitle = $_POST['movTitle'];
    $genre = $_POST['genre']; 
    $movLength = $_POST['movLength'];
    $rented = false;

    if (!$stmt->bind_param("ssii", $movTitle, $genre, $movLength, $rented)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }
  else if (count($_POST) > 0 && isset($_POST['movDelete'])) {

    if (!($stmt = $mysqli->prepare("DELETE FROM movies WHERE name=?"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    $movTitle = $_POST['movDelete'];
    if (!$stmt->bind_param("s", $movTitle)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }
  else if (count($_POST) > 0 && isset($_POST['checkout']) && isset($_POST['name'])) {
 


    if (!($stmt = $mysqli->prepare("UPDATE movies SET rented=? WHERE name=?"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    $movTitle = $_POST['name'];
    $rented = $_POST['checkout'] === "true" ? 1 : 0;
    if (!$stmt->bind_param("is", $rented, $movTitle)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }
  else if (count($_POST) > 0 && isset($_POST['delAll']) && $_POST['delAll'] === 'true') {

    if (!($stmt = $mysqli->prepare("DELETE FROM movies"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }
}

?>
<body>
  <div>
    <form action="https://web.engr.oregonstate.edu/~etterm/cs290-assignment4part2/movieLib.php" method="POST" onsubmit="return verifyAdd();">
      <fieldset>
        <legend>Add New Movie:</legend>
        <fieldset>
          <label>Title: </label>
          <input type="text" id="movTitle" name="movTitle"><br>
        </fieldset>
        <fieldset>
          <label>Genre: </label>
          <input type="text" id="genre" name="genre"><br>
        </fieldset>   
        <fieldset>
          <label>Length: </label>
          <input type="number" id="movLength" name="movLength"><br>
        </fieldset>   
        <fieldset>
        <input type="submit" value="Update Library">
        </fieldset>
      </fieldset>
    </form>
  </div>
  <div>
    <br>
    <input type="button" value="Delete Library" onclick="deleteLibrary();"> 
  </div>
  <h1>Movie Library:</h1>
  <div id="movLib">
    <div id="movLib-table">
      <?php
        $filtering = false;
        if (count($_POST) > 0 && isset($_POST['filter']) && $_POST['filter'] !== 'showAll') {
          $filtering = true;
          $selectStatm = "SELECT name, category, length, rented FROM movies WHERE category=?";
        }
        else {
          $selectStatm = "SELECT name, category, length, rented FROM movies";
        }

        if (!($stmt = $mysqli->prepare($selectStatm))) {
          echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        
        if ($filtering === true)
        {
          $categories = $_POST['filter'];
          if (!$stmt->bind_param("s", $categories)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
          }
        }

        if (!$stmt->execute()) {
          echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        
        if (!($res = $stmt->get_result())) {
          echo "Getting results failed: (" . $stmt->errno . ") " . $stmt->error;
        }

        if (!$filtering) {
          generateFilter();
        }
        else {
          generateFilter($categories);
        }

        echo "<table>\n";

        echo "<tr>\n"
        ."<td>Title</td>\n"
        ."<td>Genre</td>\n"
        ."<td>Length</td>\n"
        ."<td>Availability</td>\n"
        ."</tr>\n";

        for ($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
          $res->data_seek($row_no);
          $row = $res->fetch_assoc();

          $movTitle = $row['name'];
          $genre = $row['category']; 
          $movLength = $row['length'];
          $rented = $row['rented']; 

          if ($rented === 0) {
            $rented = 'In Stock';
            $checkoutBtnText = 'Check out';
          }
          else {
            $rented = 'Out of Stock';
            $checkoutBtnText = 'Check in';
          }

          echo "<tr>\n"
          ."<td>$movTitle</td>\n"
          ."<td>$genre</td>\n"
          ."<td>$movLength</td>\n"
          ."<td>$rented</td>\n"
          ."<td><input type=\"button\" value=\"$checkoutBtnText\" onclick=\"updateCheckout('$checkoutBtnText', '$movTitle');\"></td>\n"
          ."<td><input type=\"button\" value=\"Delete\" onclick=\"deleteVid('$movTitle');\"></td>\n"
          ."</tr>\n";
        }

        echo "</table>\n";
      ?>
    </div>
  </div>
</body>

</html>
<?php
  function generateFilter($categories = null) {
    echo "<label>Category: </label>";

    echo "<select id=\"category\">\n";
    generateFilterOptions($categories);
    echo "</select>\n"; 

    echo "<input type=\"button\" value=\"Apply Selection\" onclick=\"categorySelect();\">";
  }

  function generateFilterOptions ($categories) {
    global $mysqli;

    if (!($stmt = $mysqli->prepare("SELECT distinct(category) FROM movies WHERE category != null or category != ''"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    if (!$stmt->execute()) {
      echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    if (!($res = $stmt->get_result())) {
      echo "Getting result set failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    if ($res->num_rows === 0) {
      return;
    }


    if (!isset($categories)) {
      echo "<option value=\"showAll\">Show All Categories</option>\n";
    }
    else {
      for ($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
        $res->data_seek($row_no);
        $row = $res->fetch_assoc();

        if ($categories !== $row['category']) {
          continue;
        }

        $genre = $row['category']; 

        echo "<option value=\"$genre\">$genre</option>\n";
        break;
      }
    }

    for ($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
      $res->data_seek($row_no);
      $row = $res->fetch_assoc();

      if (isset($categories) && $row['category'] === $categories){
        continue;
      }

      $genre = $row['category']; 

      echo "<option value=\"$genre\">$genre</option>\n";
    }

    if (isset($categories)) {
      echo "<option value=\"showAll\">Show All Categories</option>\n";
    }
  }
?>
 