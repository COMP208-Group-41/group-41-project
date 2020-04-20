<?php

  session_start();

  error_reporting( E_ALL );
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);

    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    /* If the user is logged in but they are not a venue user then they are
     * redirected to home page
     */
    } else if (isset($_SESSION["VenueUserID"])) {
        header("location: venue-user-dashboard.php");
        exit;
    } else if (!isset($_SESSION["UserID"])) {
        header("location: login.php");
        exit;
    }

    // Config file is imported
    require_once "config.php";

    $userID = $_SESSION["UserID"];
    $errorMessage = "";
    $result = getUserInfo($userID,$pdo);
    $name = $result['UserName'];
    $email = $result['UserEmail'];
    $userDOB = $result['UserDOB'];
    $userPrefs = getUserTags($userID,$pdo);
    $interestedIn = getInterested($userID,$pdo);

    function eventToVenueID($eventID,$pdo){
      $getVenuesStmt = $pdo->prepare("SELECT VenueID FROM Event WHERE EventID=:EventID");
      $getVenuesStmt->bindValue(":EventID",$eventID);
      $getVenuesStmt->execute();
      $result = $getVenuesStmt->fetch();
    }


?>
<!DOCTYPE html>
<html lang='en-GB'>
  <head>
    <title>OutOut - User Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
  </head>
  <body>
    <?php include "navbar.php" ?>
    <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='success'>".$_SESSION['message']."</div>";
            unset($_SESSION['message']);
        }
    ?>
    <div class='wrapper'>
        <div class='container'>
            <h1 class='title'>Account Details</h1>
            <table align="center" border="1px" style="width:600px; line-height:40px;">
              <tr>
                <th>User Name</th>
                <td><?php echo "$name"; ?></td>
              </tr>
              <tr>
                <th>Email</th>
                <td><?php echo "$email"; ?></td>
              </tr>
              <tr>
                <th>Date of Birth</th>
                <td><?php echo "$userDOB"; ?></td>
              </tr>
              <tr>
                <th>Your Favourite Tags</th>
              </tr>
              <label style="text-align: center; margin-top: 16px;"><b>Current Tags:</b></label>
              <div style="display: flex; justify-content: center; ">
                  <div class="tag-container" style="text-align: center">
                      <?php getTags($interestedIn,$pdo); ?>
                  </div>
              </div>
            </table>
            <button onclick="location.href='user-edit.php';" class="edit-account">Edit Account Details</button>
            <h2>Your Interested Events</h2>
            <table align="center" border="1px" style="width:600px; line-height:40px;">
            <tr>
              <th>Event</th>
              <th>View Event</th>
            </tr>
            <?php
              foreach ($interestedIn as $row) {
                echo "<tr>";
                  echo "<td>".$row['EventName']."</td>";
                  echo '<td><a href="event.php?eventID='.$row['EventID'].'" class="button">View Event</a>';
                echo "</tr>";
              }
            ?>
          </table>
      </div>
  </div>
  </body>
</html>
