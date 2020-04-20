<?php

  session_start();

  error_reporting( E_ALL );
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);

  require_once "config.php";

  if (isset($_GET['venueID'])) {
      $venueID = $_GET['venueID'];
  } else {
      $_SESSION['message'] = "No Venue ID specified!";
      header("location: 404.php");
      exit;
  }

  $result = getVenueInfo($venueID,$pdo);
  $owner = $result['VenueUserID'];
  $name = $result['VenueName'];
  $description = $result['VenueDescription'];
  $address = $result['VenueAddress'];
  $times = $result['VenueTimes'];
  $events = getEvents($venueID,$pdo);
  $currentTagIDs = getTagID($venueID,$pdo);


  // Fetchs type of user and checks if venue user is owner
  if (isset($_SESSION["UserID"])){
    $userID = $_SESSION["UserID"];
  } else if (isset($_SESSION["VenueUserID"]) && $owner == $_SESSION["VenueUserID"]){
    $venueUserID = $_SESSION["VenueUserID"];
  }

  function getTagID($venueID,$pdo) {
      $getVenueTagsStmt = $pdo->prepare("SELECT TagID FROM VenueTag WHERE VenueID=:VenueID");
      $getVenueTagsStmt->bindValue(":VenueID",$venueID);
      $getVenueTagsStmt->execute();
      return $getVenueTagsStmt->fetchAll();
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
            <textarea readonly placeholder="Description of event here"><?php echo "$description" ?></textarea>

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
            <?php
            if (isset($venueUserID)){
              echo '<div class="">';
              echo '<div class=""><a href="event-creation.php?venueID='.$venueID.'" class="event-button" style="margin-right: -1px">Add Event</a>';
            }
            ?>
            <div class="eventlist">
                <?php
                if (sizeof($events) !== false){
                  foreach ($events as $row) {
                      echo '<div class="event">';
                      echo '<div class="event-image"></div>';
                      echo '<div class="event-name">'.$row['EventName']."</div>";
                      echo '<div class="event-buttons"><a href="event.php?eventID='.$row['EventID'].'" class="event-button" style="margin-right: -1px">View Event</a>';
                      if (isset($venueUserID)){
                        echo '<a href="event-edit.php?eventID='.$row['EventID'].'" class="event-button" style="width: 50%">Edit Event</a></div></div>';
                      }
                  }
                } else {
                  echo '<div class="event">';
                  echo '<div class="event-name">No events currently listed</div></div>";';
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
