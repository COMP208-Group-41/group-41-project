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
                          echo '<div class="seperator" style="margin-top: 4px"></div>';
                          echo "<div class='table'>";
                          echo "<div class='table-row'>";
                          echo "<div class='table-item image' style='background-image: url(../Assets/background2.jpg); width: 40%'>Content here</div>";
                          echo "<div style='display: flex' id='row'> ";
                          echo "<div style='display: flex; flex-direction: column'>";
                          echo "<div class='table-item'>".getTagsNoEcho($currentTagIDs,$pdo)."</div>";
                          echo "<div class='table-item'>".$row['EventName']."</div></div>";
                          echo "<div style='display: flex; flex-direction: column'>";
                          echo "<div class='table-item'>".$row['EventStartTime']."</div>";
                          echo "<div class='table-item'>".venueIDtoName($row['VenueID'], $pdo)."</div></div>";
                          echo "</div>";
                          echo "</div></div>";
                          echo '<a href="event.php?eventID='.$row['EventID'].'" class="button" style="margin-left: -1px">View Event</a>';
                          echo '<a href="venue?venueID='.$row['VenueID'].'" class="button" style="margin-right: -1px">View Venue</a>';
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

