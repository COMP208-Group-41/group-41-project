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
            <h1 class='title'>All Venues</h1>
            <?php
              if (sizeof($allVenues) != 0) {
                  echo "<div class='list'>";
                  foreach($allVenues as $row) {
                      $currentTagIDs = getVenueTagID($row['VenueID'],$pdo);
                      echo "<div class='venue'>";
                      echo "<div class='venue-name'>".$row['VenueName'];
                      echo "<div class='rating-wrapper'>Rating:<div class='rating-square'>5</div></div></div>'";
                      echo '<div class="venue-buttons"><a href="venue.php?venueID='.$row['VenueID'].'" class="venue-button" style="margin-bottom: -2px">View Venue</a>';
                      echo '<a href="upcoming-events.php?venueID='.$row['VenueID'].'" class="venue-button">View Upcoming Events</a></div>';
                      echo '<div class="venue-tags" style="text-align: center">'.getTagsNoEcho($currentTagIDs,$pdo).'</div>';
                      echo "</div>";
                  }
                  echo "</div>";
              } else {
                echo "<h2 class='title'>No venues found!</h2>";
              }
            ?>
        </div>
    </div>

</body>
</html>
