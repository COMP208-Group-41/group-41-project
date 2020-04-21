<?php


    session_start();

    require_once "config.php";

    if (isset($_GET['venueID'])){
      $venueID = $_GET['venueID'];
      $events = getEvents($venueID, $pdo);
      $venueDetails = getVenueInfo($venueID,$pdo);
    } else {
      $_SESSION['message'] = "Venue ID was not set and page could not be found!";
      header("location: 404.php");
      exit;
    }

?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Upcoming</title>
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
            <h1 class='title'>Upcoming Events for <?php $venueDetails['VenueName']; ?></h1>
            <div class="seperator"></div>
            <?php
              echo '<div class="venue-buttons"><a href="venue.php?venueID='.$venueID.'" class="venue-button" style="margin-right: -1px">View Venue</a></div>';
              if ($events !== false){
                foreach($events as $row){
                  echo "<table>";
                    echo "<tr>";
                      echo "<td>".$row['EventName']."</td>";
                      echo '<td><div class="venue-buttons"><a href="event.php?eventID='.$row['EventID'].'" class="venue-button" style="margin-right: -1px">View Event</a></td>';
                    echo "</tr><tr>";
                      echo "<td>Event Date: ".$row['EventStartTime']."</td>";
                    echo "</tr>";
                  echo "</table>";
                }
              } else {
                echo "</tr><tr>";
                  echo "<td>No Upcoming events for this Venue listed</td>";
                echo "</tr>";
              }
              echo '<div class="venue-buttons"><a href="venue.php?venueID='.$venueID.'" class="venue-button" style="margin-right: -1px">View Venue</a></div>';
            ?>
        </div>
    </div>

</body>
</html>
