<?php


    session_start();

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    $search = $_GET['search'];
    $allEvents = getAllEvents($pdo);
    $allVenues = getAllVenues($pdo);
    // EXPRESSION TO FILTER NEEDED HERE

    function venueIDtoName($venueID, $pdo){
      $getStmt = $pdo->prepare("SELECT VenueName FROM Venue WHERE VenueID=:VenueID");
      $getStmt->bindValue(":VenueID",$venueID);
      $getStmt->execute();
      $result = $getStmt->fetch();
      return $result['VenueName'];

    }

?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Matching Results</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/dashboard.css">
</head>
<body>
    <?php include "navbar.php" ?>
    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='message-wrapper'><div class='success'>" . $_SESSION['message'] . "</div></div>";
        unset($_SESSION['message']);
    }
    ?>
    <div class="wrapper">
        <div class="container">
            <h1 class='title'>Matching Results</h1>
            <?php

              // THESE ARE THE MATCHING EVENTS BY NAME
              if (sizeof($allEvents) != 0) {
                  $count = 0;
                  foreach($allEvents as $row) {
                      if (new DateTime("now") < new DateTime($row['EventEndTime']) && $count < 5) {
                          $currentTagIDs = getEventTagID($row['EventID'],$pdo);
                          echo '<div class="seperator" style="margin-top: 4px">';
                          echo "<table>";
                          echo "<tr>";
                          echo "<td>".$row['EventName']."</td>";
                          echo '<td><div class="venue-buttons"><a href="event.php?eventID='.$row['EventID'].'" class="venue-button" style="margin-left: -1px">View Event</a>';
                          echo '<a href="venue?venueID='.$row['VenueID'].'" class="venue-button" style="margin-right: -1px">View Venue</a></div></td>';
                          echo '<td><div class="tag-container" style="text-align: center">'.getTagsNoEcho($currentTagIDs,$pdo).'</div></td>';
                          echo "</tr><tr>";
                          echo "<td>Event Date: ".$row['EventStartTime']."</td>";
                          echo "</tr>";
                          echo "</tr><tr>";
                          echo "<td>Hosted By: ".venueIDtoName($row['VenueID'], $pdo)."</td>";
                          echo "</tr>";
                          echo "</table>";
                          $count++;
                      }
                  }
              } else {
                echo "<table>";
                echo "<tr>";
                echo "<td>No matching events for this Venue listed</td>";
                echo "</tr>";
                echo "</table>";
              }



            ?>
        </div>
    </div>

</body>
</html>
