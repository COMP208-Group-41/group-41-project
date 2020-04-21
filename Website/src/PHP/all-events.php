<?php

    /* This will list all venues in the system */

    session_start();

    require_once "config.php";

    $allEvents = getAllEvents($pdo);

    function getAllEvents($pdo) {
        $getStmt = $pdo->prepare("SELECT EventID,VenueID,EventName, EventStartTime FROM Event WHERE EventID<>'1' ORDER BY EventStartTime");
        $getStmt->execute();
        return $getStmt->fetchAll();
    }

    func

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
            <h1 class='title'>All Events</h1>
            <div class="seperator"></div>
            <?php
              if (sizeof($allEvents) != 0) {
                  foreach($allEvents as $row) {
                      $venueInfo = getVenueInfo($venueID, $pdo);
                      echo "<table>";
                      echo "<tr>";
                      echo "<td>".$row['EventName']."</td>";
                      echo '<td><div class="venue-buttons"><a href="event.php?eventID='.$row['EventID'].'" class="venue-button" style="margin-left: -1px">View Event</a>';
                      echo '<a href="venue?venueID='.$row['VenueID'].'" class="venue-button" style="margin-right: -1px">View Venue</a></div></td>';
                      echo "</tr><tr>";
                      echo "<td>Event Date: ".$row['EventStartTime']."</td>";
                      echo "</tr>";
                      echo "</tr><tr>";
                      echo "<td>Hosted By: ".$venueInfo['VenueName']."</td>";
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
