<?php


    session_start();

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    if (isset($_GET['venueID'])){
      $venueID = $_GET['venueID'];
      $events = getEvents($venueID, $pdo);
      $venueDetails = getVenueInfo($venueID,$pdo);
      $venueName = $venueDetails['VenueName'];
    } else {
      $_SESSION['message'] = "Venue ID was not set and page could not be found!";
      header("location: 404.php");
      exit;
    }

?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Upcoming Events</title>
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
            <h1 class='title'>Upcoming Events for <?php echo '<a href="venue.php?venueID='.$venueID.'">'.$venueName.'</a>'; ?></h1>
            <?php
              $count = 0;
              if ($events !== false){
                foreach($events as $row){
                    if (new DateTime("now") < new DateTime($row['EventEndTime'])) {
                        $count++;
                        $currentTagIDs = getEventTagID($row['EventID'],$pdo);
                        echo '<div class="seperator" style="margin-top: 4px"></div>';
                        echo "<div class='table'>";
                        echo "<div class='table-row'>";
                        $venueUserID = venueIDtoVenueUserID($row['VenueID'],$pdo);
                        $venueImage = "https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/".$venueUserID."/".$row['VenueID']."/".$row['EventID']."/event.jpg";
                        echo "<div class='table-item image' style='background-image: url($venueImage); width: 40%'>";
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
              }
              if ($count == 0) {
                  echo "<h2 class='title'>No upcoming events found!</h2>";
              }
              if (sizeof($events) > 5){
                echo '<div class="venue-buttons"><a href="venue.php?venueID='.$venueID.'" class="venue-button" style="margin-right: -1px">View Venue</a></div>';
              }
            ?>
        </div>
    </div>

</body>
</html>
