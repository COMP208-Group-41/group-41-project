<?php

    /* This will list all venues in the system */

    session_start();

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    $allEvents = getAllEvents($pdo);

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
    <title>OutOut - Events</title>
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
            <?php
              if (sizeof($allEvents) != 0) {
                  foreach($allEvents as $row) {
                      if (new DateTime("now") < new DateTime($row['EventEndTime'])) {
                          $currentTagIDs = getEventTagID($row['EventID'],$pdo);
                          echo '<div class="seperator" style="margin-top: 4px">';
                          echo "<div class='table'>";
                          echo "<div class='table-row'>";
                          echo "<div class='table-item image' style='background-image: url(../Assets/background2.jpg); width: 40%'>Content here</div>";
                          echo "<div style='display: flex' id='row'> ";
                          echo "<div style='display: flex; flex-direction: column'>";
                          echo "<div class='table-item'>Grid 1</div><div class='table-item'>Grid 2</div></div>";
                          echo "<div style='display: flex; flex-direction: column'>";
                          echo "<div class='table-item'>Grid 3</div><div class='table-item'>Grid 4</div></div>";
                          echo "</div>";
                          echo "</div></div>";
                          echo '<a href="event.php?eventID='.$row['EventID'].'" class="button" style="margin-left: -1px">View Event</a>';
                          echo '<a href="venue?venueID='.$row['VenueID'].'" class="button" style="margin-right: -1px">View Venue</a>';
                          echo '<div><div class="tag-container" style="text-align: center">'.getTagsNoEcho($currentTagIDs,$pdo).'</div></div>';
                          echo "</div><div>";
                          echo "<div><div>Event Date: ".$row['EventStartTime']."</div>\"<div>Hosted By: ".venueIDtoName($row['VenueID'], $pdo)."</div>";
                          echo "</div>";
                          echo "</div><div>";
                          echo "<div>Hosted By: ".venueIDtoName($row['VenueID'], $pdo)."</div>";
                          echo "</div>";
                          echo "</div>";
                      }
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

".$row['EventName']."