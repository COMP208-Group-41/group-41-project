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
    <title>OutOut - Past Events</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/all-venues.css">
    <link rel="stylesheet" type="text/css" href="../css/all-events.css">
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
            <h1 class='title'>Past Events for <?php echo '<a href="venue.php?venueID='.$venueID.'">'.$venueName.'</a>'; ?></h1>
            <?php
              $count = 0;
            if ($events !== false){
                foreach($events as $row){
                    if (new DateTime("now") < new DateTime($row['EventEndTime'])) {
                        $count++;
                        $currentTagIDs = getEventTagID($row['EventID'],$pdo);
                        echo '<div class="seperator"></div>';
                        echo "<div class='table' style='margin-bottom: -2px'>";
                        echo "<div class='table-row'>";
                        $venueUserID = venueIDtoVenueUserID($row['VenueID'],$pdo);
                        $venueImage = "https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/".$venueUserID."/".$row['VenueID']."/".$row['EventID']."/event.jpg";
                        echo "<div class='table-item image' style='background-image: url($venueImage);'>";
                        echo "<div class='table-item-wrapper' style='font-size: 20px; justify-content: center; align-items: center'>".venueIDtoName($row['VenueID'], $pdo)."</div>";
                        echo "</div>";
                        echo "<div class='table-items' id='row'>";
                        echo "<div class='table-items column'>";
                        echo "<div class='table-item max' style='height: 100%; width: 100%'>".getTagsNoEcho($currentTagIDs,$pdo)."</div>";
                        echo '<a href="venue?venueID='.$row['VenueID'].'" class="button right">View Venue</a>';
                        echo "</div>";
                        echo "<div class='table-items column' ' >";
                        echo "<div class='table-item column'>".$row['EventName']."</div>";
                        echo "<div class='table-item column'>".$row['EventStartTime']."</div>";
                        echo '<a href="event.php?eventID='.$row['EventID'].'" class="button left">View Event</a>';
                        echo "</div></div></div></div>";
                    }
                }
            }
              if ($count == 0) {
                echo "<h2 class='title'>No past events found!</h2>";
              }
              if (sizeof($events) > 5){
                echo '<a href="venue.php?venueID='.$venueID.'" class="button">View Venue</a>';
              }
            ?>
        </div>
    </div>

</body>
</html>
