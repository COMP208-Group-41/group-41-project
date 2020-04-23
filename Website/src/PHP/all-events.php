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
                          echo "<div class='table-item image' style='background-image: url(../Assets/background2.jpg); width: 40%'>";
                          echo "</div>";
                          echo "<div style='display: flex; width: 40%' id='row'>";
                          echo "<div style='display: flex; flex-direction: column; width: 50%;'>";
                          echo "<div class='table-item' style='height: 100%; width: 100%'>".getTagsNoEcho($currentTagIDs,$pdo)."</div>";
                          echo "</div>";
                          echo "<div style='display: flex; flex-direction: column; width: 50%;' >";
                          echo "<div class='table-item' style='height: 35%; width: 100%'>".$row['EventName']."</div>";
                          echo "<div class='table-item' style='height: 35%; width: 100%'>".$row['EventStartTime']."</div>";
                          echo "<div class='table-item' style='height: 30%;  width: 100%'>".venueIDtoName($row['VenueID'], $pdo)."</div></div>";
                          echo "</div>";
                          echo "</div></div>";
                          echo "<div class='display: flex' style='margin-bottom: 16px'>";
                          echo '<a href="event.php?eventID='.$row['EventID'].'" class="button" style="width: 50%; margin-right:3px">View Event</a>';
                          echo '<a href="venue?venueID='.$row['VenueID'].'" class="button" style="width: 50%">View Venue</a></div>';
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

