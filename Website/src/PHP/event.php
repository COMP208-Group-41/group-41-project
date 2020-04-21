<?php

    session_start();

    require_once "config.php";

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    if (isset($_GET['eventID'])) {
        $eventID = $_GET['eventID'];
        if ($eventID == 1) {
            $_SESSION['message'] = "That event does not exist!";
            header("location: 404.php");
            exit;
        }
    } else {
        $_SESSION['message'] = "No Event ID specified!";
        header("location: 404.php");
        exit;
    }

    if (!checkEventExists($eventID,$pdo)) {
        $_SESSION['message'] = "That event does not exist!";
        header("location: 404.php");
        exit;
    }

    $result = getEventInfo($eventID,$pdo);
    $owner = eventToVenueUser($eventID,$pdo);
    $owner = $owner['VenueUserID'];
    $venueID = $result['VenueID'];
    $getVenueDetails = getVenueInfo($venueID,$pdo);
    $venueName = $getVenueDetails['VenueName'];
    $name = $result['EventName'];
    $description = $result['EventDescription'];
    $startTime = str_replace("T"," ",$result['EventStartTime']);
    $endTime = str_replace("T"," ",$result['EventEndTime']);
    $currentTagIDs = getEventTagID($eventID,$pdo);
    $reviews = getEventReviews($eventID,$pdo);
    $priceScore = getPriceScore($venueID,$eventID,$pdo);
    $safetyScore = getSafetyScore($venueID,$eventID,$pdo);
    $atmosphereScore = getAtmosphereScore($venueID,$eventID,$pdo);
    $queueScore = getQueueScore($venueID,$eventID,$pdo);

    if ($priceScore === false || $safetyScore === false || $atmosphereScore === false || $queueScore === false) {
        $totalScore = "No Scores";
        $priceScore = "No Scores";
        $safetyScore = "No Scores";
        $atmosphereScore = "No Scores";
        $queueScore = "No Scores";
    } else {
        $totalScore = ($queueScore + $atmosphereScore + $safetyScore + $priceScore) / 4;
    }

    if (isset($_SESSION['UserID'])) {
        $userID = $_SESSION['UserID'];
        $following = checkInterestedIn($userID,$eventID,$pdo);
    } else if (isset($_SESSION['VenueUserID']) && $owner == $_SESSION['VenueUserID']) {
        $venueUserID = $_SESSION['VenueUserID'];
    }

    $image = checkEventImageOnServer($owner,$venueID,$eventID);

    if (isset($_POST['followForm'])) {
        // Need to unfollow the event
        $unfollowStmt = $pdo->prepare("SELECT");
    }


    function checkInterestedIn($userID,$eventID,$pdo) {
        $getStmt = $pdo->prepare("SELECT InterestedID FROM InterestedIn WHERE UserID=:UserID AND EventID=:EventID");
        $getStmt->bindValue(":UserID",$userID);
        $getStmt->bindValue(":EventID",$eventID);
        $getStmt->execute();
        if ($getStmt->rowCount() == 0) {
            // The user is not following this event, show the follow button
            return false;
        } else {
            return true;
        }
    }

?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <title>OutOut - <?php echo $name; ?></title>
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
            <h1 class="title"><?php echo $name; ?></h1>
                <?php
                    if (isset($following)) {
                        if ($following) {
                            echo "<form id='unfollow' name='unfollowForm' method='post'>";
                            echo "<input type='submit' name='unfollow' value='Unfollow This Event'></form>"
                        } else {
                            echo "<form id='follow' name='followForm' method='post'>";
                            echo "<input type='submit' name='follow' value='Follow This Event'></form>";

                        }
                    }
                ?>
            <div class='image'>
                <?php
                    if ($image) {
                        echo '<div class="seperator"></div>';
                        echo '<img src="https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/'.$owner.'/'.$venueID.'/'.$eventID.'/event.jpg" alt="Event Image">';
                    }
                ?>
            </div>
            <div class="seperator"></div>
            <label>Venue: <?php echo '<a href="venue.php?venueID='.$venueID.'">'.$venueName.'</a>'; ?></label>

            <label>Start Time: <?php echo $startTime; ?></label>
            <label>EndTime: <?php echo $endTime; ?></label>
            <label>Event description:</label>
            <textarea readonly placeholder="Description of event here"><?php echo $description; ?></textarea>

            <label style="text-align: center; margin-top: 16px;"><b>Event Tags:</b></label>
            <div style="display: flex; justify-content: center; ">
                <div class="tag-container" style="text-align: center">
                    <?php getTags($currentTagIDs,$pdo); ?>
                </div>
            </div>
            <div class="seperator"></div>
            <h2 class='title'>Reviews</h2>
            <?php
                if (isset($userID)) {
                    $compareDate = new DateTime($result['EventEndTime']);
                    if (new DateTime("now") > $compareDate) {
                        $checkReview = checkReviewWritten($userID,$eventID,$venueID,$pdo);
                        if ($checkReview === false) {
                            echo '<a href="review-creation.php?eventID='.$eventID.'">Write a Review</a>';
                        } else {
                            echo '<a href="review-edit.php?reviewID='.$checkReview.'">Edit Review</a>';
                        }
                    }
                }
            ?>
            <br>
            <label>Event Score: <?php echo"$totalScore";?></label><br>
            <label>Price Score: <?php echo"$priceScore";?></label><br>
            <label>Safety Score: <?php echo"$safetyScore";?></label><br>
            <label>Atmosphere Score: <?php echo"$atmosphereScore";?></label><br>
            <label>Queue Times Score: <?php echo"$queueScore";?></label><br>

            <div class="seperator"></div>
            <label>All Reviews</label>
            <div class="reviewlist">
                <?php
                if ($reviews !== false){
                  $counter = 0;
                  foreach ($reviews as $row) {
                    if($counter<5){
                      echo "<label>Review left by: ".userIDtoUserName($row['UserID'],$pdo)."</label><br>";
                      echo "<textarea readonly>".$row['ReviewText']."</textarea><br>";
                      echo "<label>Price Score: ".$row['ReviewPrice']."</label><br>";
                      echo "<label>Safety Score ".$row['ReviewSafety']."</label><br>";
                      echo "<label>Atmosphere Score ".$row['ReviewAtmosphere']."</label><br>";
                      echo "<label>Queue Times Score ".$row['ReviewQueue']."</label><br>";
                      echo "<label>Review posted on: ".$row['ReviewDate']."</label><br>";
                      echo '<div class="seperator"></div>';
                    }
                    $counter++;
                  }
                } else {
                  echo '<div class="">';
                  echo '<div class="">No reviews currently posted for this event</div></div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
