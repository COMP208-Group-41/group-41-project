<?php

  session_start();

  error_reporting( E_ALL );
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);

  require_once "config.php";

  if (isset($_GET['venueID'])) {
      $venueID = $_GET['venueID'];
      if ($venueID == 1) {
          $_SESSION['message'] = "That venue does not exist!";
          header("location: 404.php");
          exit;
      }
  } else {
      $_SESSION['message'] = "No Venue ID specified!";
      header("location: 404.php");
      exit;
  }

  if (!checkVenueExists($venueID,$pdo)) {
      $_SESSION['message'] = "That venue does not exist!";
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
  $currentTagIDs = getVenueTagID($venueID,$pdo);
  $reviews = getVenueReviews($venueID,$pdo);
  $priceScore = getPriceScore($venueID,1,$pdo);
  $safetyScore = getSafetyScore($venueID,1,$pdo);
  $atmosphereScore = getAtmosphereScore($venueID,1,$pdo);
  $queueScore = getQueueScore($venueID,1,$pdo);

  if ($priceScore === false || $safetyScore === false || $atmosphereScore === false || $queueScore === false) {
      $totalScore = "No Scores";
      $priceScore = "No Scores";
      $safetyScore = "No Scores";
      $atmosphereScore = "No Scores";
      $queueScore = "No Scores";
  } else {
      $totalScore = ($queueScore + $atmosphereScore + $safetyScore + $priceScore) / 4;
  }

  // Fetchs type of user and checks if venue user is owner
  if (isset($_SESSION["UserID"])){
    $userID = $_SESSION["UserID"];
  } else if (isset($_SESSION["VenueUserID"]) && $owner == $_SESSION["VenueUserID"]){
    $venueUserID = $_SESSION["VenueUserID"];
  }

  $image = checkVenueImageOnServer($owner,$venueID);



?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
    <title>OutOut - <?php echo $name; ?></title>
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
    <link rel="stylesheet" type="text/css" href="../css/review.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
</head>
<body>
<?php include "navbar.php" ?>
<div class="message-wrapper">
<?php
    if (isset($_SESSION['message'])) {
        echo "<div class='success'>".$_SESSION['message']."</div>";
        unset($_SESSION['message']);
    }
?>
</div>
<div class="wrapper">
    <div class="container">
        <div style="display: flex; flex-direction: column">
            <h1 class="title"><?php echo "$name" ?></h1>
            <?php
                if ($image) {
                    echo '<div class="seperator"></div>';
                    echo '<img src="https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/'.$owner.'/'.$venueID.'/venue.jpg" alt="Venue Image" style="max-height: 300px">';
                }
            ?>

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
            <div class="seperator"></div>
            <h2 class='title'>Upcoming Events</h2>
            <?php
            if (isset($venueUserID)){
              echo '<a href="event-creation.php?venueID='.$venueID.'" class="button" style="width: 100%; margin-bottom: 16px">Add a new Event</a>';
            }
            ?>
            <div class="eventlist" style="margin-bottom: 16px">
                <?php
                if ($events !== false){
                  $counter = 0;
                  foreach ($events as $row) {
                    if($counter<5){
                      echo '<div class="event">';
                      echo '<div class="event-image"></div>';
                      echo '<div class="event-name">'.$row['EventName']."</div>";
                      echo '<div class="event-buttons"><a href="event.php?eventID='.$row['EventID'].'" class="event-button" style="margin-right: -1px">View</a>';
                      if (isset($venueUserID)){
                        echo '<a href="event-edit.php?eventID='.$row['EventID'].'" class="event-button" style="width: 50%">Edit</a></div></div>';
                      }
                    }
                    $counter++;
                  }
                  echo '</div>';
                } else {
                  echo '<div class="event">';
                  echo '<div class="event-name">No events currently listed</div></div></div>';
                }
                echo '<div><a href="upcoming-events.php?venueID='.$venueID.' "class="button" style="width: 50%;  margin-bottom: 12px">View All Events</a>';
                echo '<a href="past-events.php?venueID='.$venueID.'" class="button" style="width: 50%; margin-bottom: 12px">View Past Events</a></div>';
                ?>
            <div class="seperator"></div>
            <h2 class='title'>Venue score</h2>
            <?php
                if (isset($userID)) {
                    $checkReview = checkReviewWritten($userID,1,$venueID,$pdo);
                    if ($checkReview === false) {
                        echo '<a href="review-creation.php?venueID='.$venueID.'">Write a Review</a>';
                    } else {
                        echo '<a href="review-edit.php?reviewID='.$checkReview.'">Edit Review</a>';
                    }
                }
            ?>
            <div class="review-scores">
                <div class="review-score">
                    <div class="label">Overall Score: <?php echo"$totalScore";?></div>
                    <div class="score">5</div>
                </div>
                <div class="review-score">
                    <div class="label">Price Score: <?php echo"$priceScore";?></div>
                    <div class="score">3</div>
                </div>
                <div class="review-score">
                    <div class="label">Safety Score: <?php echo"$priceScore";?></div>
                    <div class="score">4</div>
                </div>
                <div class="review-score">
                    <div class="label">Atmosphere Score: <?php echo"$priceScore";?></div>
                    <div class="score">5</div>
                </div>
                <div class="review-score">
                    <div class="label">Queuing Score: <?php echo"$priceScore";?></div>
                    <div class="score">2</div>
                </div>
            </div>
            <div class="seperator"></div>
            <h3 class="title">All Reviews</h3>
            <div class="reviewlist">
                <?php
                if ($reviews !== false){
                  $counter = 0;
                  foreach ($reviews as $row) {
                    if($counter<5){
                      echo "<div class='review'>";
                      echo "<label>Review left by:<b> ".userIDtoUserName($row['UserID'],$pdo)."</b></label>";
                      echo "<textarea readonly>".$row['ReviewText']."</textarea>";
                      echo "<div class='review-scores'>";
                      echo "<div class='review-score'><div class='label'>Price Score:</div><div class='score'>".$row['ReviewPrice']."</div></div>";
                      echo "<div class='review-score'><div class='label'>Safety Score:</div><div class='score'> ".$row['ReviewSafety']."</div></div>";
                      echo "<div class='review-score'><div class='label'>Atmosphere Score:</div><div class='score'> ".$row['ReviewAtmosphere']."</div></div>";
                      echo "<div class='review-score'><div class='label'>Queue Times Score:</div><div class='score'> ".$row['ReviewQueue']."</div></div></div>";
                      echo "<label>Review posted on: ".$row['ReviewDate']."</label></div>";
                    }
                    $counter++;
                  }
                } else {
                  echo '<label style="font-size: 20px">No reviews currently posted for this venue</label>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
