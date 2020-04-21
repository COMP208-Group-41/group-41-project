<?php

    /* This will list all venues in the system */

    session_start();

    require_once "config.php";

    $allVenues = getAllVenues($pdo);

    function getAllVenues($pdo) {
        $getStmt = $pdo->prepare("SELECT VenueID,VenueUserID,VenueName FROM Venue ORDER BY VenueName");
        $getStmt->execute();
        return $getStmt->fetchAll();
    }

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
            <div class="seperator"></div>
            <?php
              if (sizeof($allVenues) != 0) {
                  echo "<table>";
                  foreach($allVenues as $row) {
                      echo "<tr>";
                      echo "<td>".$row['VenueName']."</td>";
                      echo '<td><div class="venue-buttons"><a href="venue.php?venueID='.$row['VenueID'].'" class="venue-button" style="margin-right: -1px" width=100%>View Event</a></td>';
                      echo "</tr>";
                  }
                  echo "</table>";
              } else {
                echo "</tr><tr>";
                echo "<td>No Upcoming events for this Venue listed</td>";
                echo "</tr>";
              }
            ?>



        </div>
    </div>

</body>
</html>
