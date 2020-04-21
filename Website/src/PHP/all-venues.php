<?php

    /* This will list all venues in the system */

    session_start();

    require_once "config.php";

    $allVenues = getAllVenues($pdo);


?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Venues</title>
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
            <h1 class='title'>All Venues</h1>
            <?php
              if (sizeof($allVenues) != 0) {
                  foreach($allVenues as $row) {
                      echo '<div class="seperator" style="margin-top: 4px">';
                      $currentTagIDs = getVenueTagID($row['VenueID'],$pdo);
                      echo "<table>";
                      echo "<tr>";
                      echo "<td>".$row['VenueName']."</td>";
                      echo '<td><div class="venue-buttons"><a href="venue.php?venueID='.$row['VenueID'].'" class="venue-button" style="margin-left: -1px">View Venue</a>';
                      echo '<a href="upcoming-events.php?venueID='.$row['VenueID'].'" class="venue-button" style="margin-right: -1px">View Upcoming Events</a></div></td>';
                      echo '<td><div class="tag-container" style="text-align: center">'.getTagsNoEcho($currentTagIDs,$pdo).'</div></td>';
                      echo "</tr>";
                      echo "</table>";
                  }
              } else {
                echo "<table>";
                echo "</tr><tr>";
                echo "<td>No Upcoming events for this Venue listed</td>";
                echo "</tr>";
                echo "</table>";
              }
            ?>



        </div>
    </div>

</body>
</html>
