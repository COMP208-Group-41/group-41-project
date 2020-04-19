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
    $userDOB = $result['VenueUserDOB'];
    $userPrefs = getUserTags($userID,$pdo);
    $interestedIn = getInterested($userID,$pdo);

    function getUserTags($userID,$pdo){
      $infoStmt = $pdo->prepare("SELECT TagID FROM UserPreferences WHERE UserID=:UserID");
      $infoStmt->bindValue(":UserID",$userID);
      $infoStmt->execute();
      return $infoStmt->fetchAll();
    }

    function getInterested($userID,$pdo){
      $infoStmt = $pdo->prepare("SELECT EventID FROM InterestedIn WHERE UserID=:UserID");
      $infoStmt->bindValue(":UserID",$userID);
      $infoStmt->execute();
      return $infoStmt->fetchAll();
    }

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
    <link rel="stylesheet" href="../css/venue-user-dashboard.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
  </head>
  <body>
    <?php include "navbar.php" ?>
    <h1>Account Details</h1>
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
      <tr>
        <?php
          foreach ($userPrefs as $tag) {
            echo "<td>".$tag."</td>";
          }
        ?>
      </tr>
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
  </body>
</html>
