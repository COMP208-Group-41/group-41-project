<?php

    /* This will list all venues in the system */

    session_start();

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    $allEvents = getAllEvents($pdo);

?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Events</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/all-venues.css">
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
            <h1 class='title'>All Events</h1>
            <?php
              if (sizeof($allEvents) != 0) {
                  foreach($allEvents as $row) {
                      if (new DateTime("now") < new DateTime($row['EventEndTime'])) {
                          $currentTagIDs = getEventTagID($row['EventID'],$pdo);
                          echo '<div class="seperator" style="margin-top: 4px"></div>';
                          echo "<div class='table'>";
                          echo "<div class='table-row'>";
                          $venueUserID = venueIDtoVenueUserID($row['VenueID'],$pdo);
                          $venueImage = "https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/".$venueUserID."/".$row['VenueID']."/".$row['EventID']."/event.jpg";
                          echo "<div class='table-item image' style='background-image: url($venueImage); width: 40%'>";
                          echo "<div class='table-item-wrapper' style='font-size: 20px'>".venueIDtoName($row['VenueID'], $pdo)."</div>";
                          echo "</div>";
                          echo "<div style='display: flex; width: 40%' id='row'>";
                          echo "<div style='display: flex; flex-direction: column; width: 50%;'>";
                          echo "<div class='table-item' style='height: 100%; width: 100%'>".getTagsNoEcho($currentTagIDs,$pdo)."</div>";
                          echo "</div>";
                          echo "<div style='display: flex; flex-direction: column; width: 50%;' >";
                          echo "<div class='table-item' style='height: 35%; width: 100%'>".$row['EventName']."</div>";
                          echo "<div class='table-item' style='height: 35%; width: 100%'>".$row['EventStartTime']."</div>";
                          echo "</div></div></div></div>";
                          echo "<div class='display: flex' style='margin-bottom: 16px'>";
                          echo '<div class="button" style="width: 50%" onclick="location.href="event.php?eventID='.$row['EventID'].'"">View Event</div>';
                          echo '<div class="button" style="width: 50%" onclick="location.href="venue.php?venueID='.$row['VenueID'].'"">View Event</div></div>';

                      }
                  }
              } else {
                echo "<h2 class='title'>No events found!</h2>";
              }
            ?>



        </div>
    </div>

</body>
</html>
