<?php

  session_start();

  error_reporting( E_ALL );
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);

  require_once "config.php";

  $venueID = $_GET['venueID'];
  $result = getVenueInfo($venueID,$pdo);
  $owner = $result['VenueUserID'];
  $name = $result['VenueName'];
  $description = $result['VenueDescription'];
  $address = $result['VenueAddress'];
  $times = $result['VenueTimes'];
  $events = getEvents($venueID,$pdo)

  // Fetchs type of user and checks if venue user is owner
  if (isset($_SESSION["UserID"])){
    $userID = $_SESSION["UserID"];
  } elseif (isset($_SESSION["VenueUserID"]) && $owner == $_SESSION["VenueUserID"]){
    $venueUserID = $_SESSION["VenueUserID"];
  }



?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
</head>
<body>
<?php include "navbar.php" ?>
<div class="wrapper">
    <div class="container">
        <div style="display: flex; flex-direction: column">
            <h1 class="title"><?php echo "$name" ?></h1>

            <div class="seperator"></div>

            <img src="../Assets/venue-image.jpg" alt="Venue Image">

            <div class="seperator"></div>

            <label>Venue description:</label>
            <textarea readonly placeholder="Description of event here"><?php echo "$VenueDescription" ?></textarea>

            <label>Opening times:</label>
            <textarea readonly placeholder="Opening times here"><?php echo "$times" ?></textarea>

            <label>Location:</label>
            <label><?php echo "$address" ?></label>

            <label style="text-align: center; margin-top: 16px;"><b>Venue Tags:</b></label>
            <div style="display: flex; justify-content: center; ">
                <div class="tag-container" style="text-align: center">
                    <?php getTags($currentTagIDs,$pdo); ?>
                </div>
            </div>
            <h2>Upcoming Events</h2>
            <div class="eventlist">
                <?php
                foreach ($events as $row) {
                    echo '<div class="event">';
                    echo '<div class="event-image"></div>';
                    echo '<div class="event-name">'.$row['EventName']."</div>";
                    echo '<div class="event-buttons"><a href="event.php?eventID='.$row['EventID'].'" class="event-button" style="margin-right: -1px">View Event</a>';
                    if (isset($venueUserID)){
                      echo '<a href="event-edit.php?eventID='.$row['EventID'].'" class="event-button" style="width: 50%">Edit Event</a></div></div>';
                    }
                }
                echo '<div class=""><a href="" class="event-button" style="">View All EventS</a>';
                ?>
            </div>
            <h2>Reviews</h2>
            <label>Overall Review Scores</label>



            <label>All Reviews</label>
            <div class="reviewlist">
                <?php

                ?>
            </div>

        </div>
    </div>
</div>


</body>
</html>
