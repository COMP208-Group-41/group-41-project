<?php


    session_start();

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    if (!isset($_GET['search'])) {
        // search term not provided
        $_SESSION['message'] = "No Search term given!";
        header("location: 404.php");
        exit;
    }

    if (trim($_GET['search']) == "") {
        // search term not provided
        $_SESSION['message'] = "No Search term given!";
        header("location: 404.php");
        exit;
    }

    $search = strtolower(trim($_GET['search']));
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

              // Matching Venues
              if (sizeof($allVenues) != 0) {
                  print_r($allVenues);
                  // echo "<h2 class='title'>No matching events found!</h2>";
                  echo "<div class='table'>";
                  foreach($allVenues as $row) {
                      echo $search;
                      if (strpos($row['VenueName'],$search) !== false) {
                          print_r($row);
                          $currentTagIDs = getVenueTagID($row['VenueID'],$pdo);
                          echo "<div class='table-row'>";
                          echo "<div class='table-item'>".$row['VenueName'];
                          unset($priceScore);
                          unset($safetyScore);
                          unset($atmosphereScore);
                          unset($queueScore);
                          unset($totalScore);
                          $priceScore = getPriceScore($row['VenueID'], 1, $pdo);
                          $safetyScore = getSafetyScore($row['VenueID'], 1, $pdo);
                          $atmosphereScore = getAtmosphereScore($row['VenueID'], 1, $pdo);
                          $queueScore = getQueueScore($row['VenueID'], 1, $pdo);
                          if (!($priceScore === false || $safetyScore === false || $atmosphereScore === false || $queueScore === false)) {
                              $totalScore = ($queueScore + $atmosphereScore + $safetyScore + $priceScore) / 4;
                              echo "<div class='rating-wrapper'>Rating:<div class='rating-square'>$totalScore</div></div>";
                          } else {
                              echo "<div class='rating-wrapper'>No Ratings</div>";
                          }
                          echo "</div>";
                          echo '<div class="venue-tags" style="text-align: center">'.getTagsNoEcho($currentTagIDs,$pdo).'</div>';
                          echo '<div class="table-buttons"><a href="venue.php?venueID='.$row['VenueID'].'" class="table-button" style="margin-bottom: -2px">Venue</a>';
                          echo '<a href="upcoming-events.php?venueID='.$row['VenueID'].'" class="table-button">Events</a></div>';
                          echo "</div>";
                      }
                }
                echo "</div>";
            } else {
                echo "<h2 class='title'>No matching venues found!</h2>";
            }

            // Matching Events
            // if (sizeof($) != 0) {
            //     echo "<div class='table'>";
            //     foreach($ as $row) {
            //
            //     }
            //     echo "</div>";
            // } else {
            //     echo "<h2 class='title'>No matching events found!</h2>";
            // }


            ?>
        </div>
    </div>

</body>
</html>
