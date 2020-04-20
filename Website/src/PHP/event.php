<?php

    session_start();

    require_once "config.php";

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    if (isset($_GET['eventID'])) {
        $eventID = $_GET['eventID'];
    } else {
        $_SESSION['message'] = "No Event ID specified!";
        header("location: 404.php");
        exit;
    }

    $result = getEventInfo($eventID,$pdo);
    $owner = eventToVenueUser($eventID,$pdo);
    $venueID = $result['VenueID'];
    $getVenueDetails = getVenueInfo($venueID,$pdo);
    $venueName = $getVenueDetails['VenueName'];
    $name = $result['EventName'];
    $description = $result['EventDescription'];
    $startTime = str_replace("T"," ",$result['EventStartTime']);
    $endTime = str_replace("T"," ",$result['EventEndTime']);
    $currentTagIDs = getEventTagID($eventID,$pdo);

    if (isset($_SESSION['UserID'])) {
        $userID = $_SESSION['UserID'];
    } else if (isset($_SESSION['VenueUserID']) && $owner == $_SESSION['VenueUserID']) {
        $venueUserID = $_SESSION['VenueUserID'];
    }

?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <title>OutOut - <?php echo $name; ?></title>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
</head>
<body>
<?php include "navbar.php" ?>
<?php
    if (isset($_SESSION['message'])) {
        echo "<div class='success'>".$_SESSION['message']."</div>";
        unset($_SESSION['message']);
    }
?>
<div class="wrapper">
    <div class="container">
        <div style="display: flex; flex-direction: column">
            <h1 class="title"><?php echo $name; ?></h1>
            <label>Image:</label>
            <img src="../Assets/event-image.jpg">
            <div class="seperator"></div>
            <label>Venue: <?php echo '<a href="venue.php?venueID='.$venueID.'">'.$venueName.'</a>'; ?></label>

            <label>Start Time: <?php echo $startTime; ?></label>
            <label>EndTime: <?php echo $endTime; ?></label>
            <label>Event description:</label>
            <textarea readonly placeholder="Description of event here"><?php echo $description; ?></textarea>

            <label style="text-align: center; margin-top: 16px;"><b>Venue Tags:</b></label>
            <div style="display: flex; justify-content: center; ">
                <div class="tag-container" style="text-align: center">
                    <?php getTags($currentTagIDs,$pdo); ?>
                </div>
            </div>
            <h2>Reviews</h2>
            <?php
                if (isset($userID)) {
                    $checkReview = checkReviewWritten($userID,$eventID,$venueID,$pdo);
                    if ($checkReview === false) {
                        echo '<a href="review-creation.php?eventID='.$eventID.'">Write a Review</a>';
                    } else {
                        echo '<a href="review-edit.php?reviewID='.$checkReview.'">Write a Review</a>';
                    }
                }

            ?>
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
