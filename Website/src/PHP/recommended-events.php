<?php

    session_start();

    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    if (isset($_SESSION['VenueUserID'])) {
        $_SESSION['message'] = "Venue Users cannot use the recommended page";
        header("location: venue-user-dashboard.php");
        exit;
    }

    if (!isset($_SESSION['UserID'])) {
        $_SESSION['message'] = "You must be logged in to view recommended Events";
        header("location: login.php");
        exit;
    }

    require_once "config.php";

    $userID = $_SESSION['UserID'];

    $allEvents = getAllEvents($pdo);
    $userPrefs = getUserTags($userID,$pdo);
    $sortedArray = (array) null;
    foreach($allEvents as $row){
      $event = $emptyArray = (array) null;
      $eventTags = getEventTagID($row['EventID'],$pdo);
      $count = 0;
      foreach($userPrefs as $pref){
        if(in_array($pref, $eventTags)){
          $count++;
        }
      }
      if ($count > 0){
        $event['Count'] = $count;
        $event['EventID'] = $row['EventID'];
        $event['EventName'] = $row['EventName'];
        array_push($sortedArray,$event);
      }
    }
    sortArray($sortedArray);
    $sortedArray = array_reverse($sortedArray);


?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Recommended Events</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/all-venues.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <?php include "navbar.php" ?>
  <div class="wrapper">
    <div class="container">
        <h1 class="title">Recommended Events For You</h1>
        <?php
          if (sizeof($sortedArray) != 0) {
              //echo print_r($sortedArray);
              foreach($sortedArray as $row) {
                  echo '<div class="seperator" style="margin-top: 4px">';
                  $currentTagIDs = getEventTagID($row['EventID'],$pdo);
                  echo "This event matches ".$row['Count']." of your preferred tags";
                  echo "<div class='table'>";
                  echo "<div class='table-row'>";
                  $eventImage = "https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/".$row['VenueUserID']."/".$row['VenueID']."/".$row['EventID']."/event.jpg";
                  echo "<div class='table-item image' style='background-image: url(".$eventImage.")'><div class='table-item-wrapper'>".$row['EventName']."</div></div>";
                  echo '<div class="table-item">'.getTagsNoEcho($currentTagIDs,$pdo).'</div>';
                  echo '<div class="table-buttons"><a href="venue.php?venueID='.$row['EventID'].'" class="table-button" style="margin-left: -1px">Event</a>';
                  echo "</div></div>";
              }
          } else {
            echo "<table>";
            echo "<tr>";
            echo "<td>No upcoming events which match your user preferences</td>";
            echo "</tr>";
            echo "</table>";
          }
        ?>
    </div>
  </div>
</body>
</html>
