<?php

session_start();

error_reporting(E_ALL);
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

if (!checkVenueExists($venueID, $pdo)) {
    $_SESSION['message'] = "That venue does not exist!";
    header("location: 404.php");
    exit;
}

$result = getVenueInfo($venueID, $pdo);
$owner = $result['VenueUserID'];
$name = $result['VenueName'];
$description = $result['VenueDescription'];
$address = $result['VenueAddress'];
$times = $result['VenueTimes'];
$website = $result['ExternalSite'];
$events = getEvents($venueID, $pdo);
$currentTagIDs = getVenueTagID($venueID, $pdo);
$reviews = getVenueReviews($venueID, $pdo);
$priceScore = getPriceScore($venueID, 1, $pdo);
$safetyScore = getSafetyScore($venueID, 1, $pdo);
$atmosphereScore = getAtmosphereScore($venueID, 1, $pdo);
$queueScore = getQueueScore($venueID, 1, $pdo);

if ($priceScore === false || $safetyScore === false || $atmosphereScore === false || $queueScore === false) {
    $totalScore = "No Scores";
    $priceScore = "No Scores";
    $safetyScore = "No Scores";
    $atmosphereScore = "No Scores";
    $queueScore = "No Scores";
} else {
    $totalScore = ($queueScore + $atmosphereScore + $safetyScore + $priceScore) / 4;
    $totalScore = number_format($totalScore,1);
    $priceScore = number_format($priceScore,1);
    $safetyScore = number_format($safetyScore,1);
    $atmosphereScore = number_format($atmosphereScore,1);
    $queueScore = number_format($queueScore,1);
}

// Fetchs type of user and checks if venue user is owner
if (isset($_SESSION["UserID"])) {
    $userID = $_SESSION["UserID"];
} else if (isset($_SESSION["VenueUserID"]) && $owner == $_SESSION["VenueUserID"]) {
    $venueUserID = $_SESSION["VenueUserID"];
}

$image = checkVenueImageOnServer($owner, $venueID);


?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
    <title>OutOut - <?php echo $name; ?></title>
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/review.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include "navbar.php" ?>
<div class="message-wrapper">
    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='success'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message']);
    }
    ?>
</div>
<div class="wrapper">
    <div class="container">
        <div class="flex-wrap">
            <div class="section" id="Venue Details" style="flex-grow: 10">
                <h1 class="title"><?php echo "$name" ?></h1>
                <?php
                if ($image) {
                    echo '<div class="seperator"></div>';
                    echo '<img src="https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/' . $owner . '/' . $venueID . '/venue.jpg" alt="Venue Image" class="title-img">';
                }
                ?>

                <div class="seperator"></div>

                <label>Venue description:</label>
                <div class="review-text" style='margin: 8px 0'><?php echo "$description" ?></div>

                <label>Opening times:</label>
                <div class="review-text" style='margin: 8px 0'><?php echo "$times" ?></div>

                <label>Location:</label>
                <label><?php echo "$address" ?></label>
                <br>
                <?php
                  if ($website != ""){
                    echo '<label>External Site:</label>';
                    echo '<label><a href="'.$website.'">Link</a></label>';
                  }
                ?>

                <label style="text-align: center; margin-top: 16px;"><b>Venue Tags:</b></label>
                <div style="display: flex; justify-content: center; ">
                    <div class="tag-container" style="text-align: center">
                        <?php getTags($currentTagIDs, $pdo); ?>
                    </div>
                </div>
            </div>
            <div class="section" id="Upcoming Events">
                <h2 class='title'>Upcoming Events</h2>
                <?php
                if (isset($venueUserID)) {
                    echo '<a href="event-creation.php?venueID=' . $venueID . '" class="button" style="width: 100%; margin-bottom: 16px">Add a new Event</a>';
                }
                ?>
                <div class="table" style="margin-bottom: 16px">
                    <?php
                    if ($events !== false) {
                        $counter = 0;
                        foreach ($events as $row) {
                            if ($counter < 10) {
                                echo '<div class="table-row">';
                                echo '<div class="table-item">' . $row['EventName'] . "</div>";
                                echo '<div class="table-buttons"><a href="event.php?eventID=' . $row['EventID'] . '" class="table-button">View</a>';
                                if (isset($venueUserID)) {
                                    echo '<a href="event-edit.php?eventID=' . $row['EventID'] . '" class="table-button" style="width: 50%">Edit</a></div></div>';
                                } else {
                                    echo '</div></div>';
                                }
                            }
                            $counter++;
                        }
                        echo '</div>';
                    } else {
                        echo '<div class="table-row">';
                        echo '<div class="table-item">No events currently listed</div></div></div>';
                    }
                    echo '<div style="display: flex">';
                    echo '<a href="upcoming-events.php?venueID=' . $venueID . ' "class="button" style="width: 50%;  margin-right: -4px">View All Events</a>';
                    echo '<a href="past-events.php?venueID=' . $venueID . '" class="button" style="width: 50%;">View Past Events</a></div>';
                    ?>
                </div>
            </div>
        <div class="flex-wrap">
            <div class="section" id="Venue Score">
                <h2 class='title'>Overall Venue Scores</h2>
                <div class="review-scores">
                    <div class="review-score">
                        <div class="label">Overall Score:</div>
                        <div class="score"> <?php echo "$totalScore"; ?></div>
                    </div>
                    <div class="review-score">
                        <div class="label">Price Score:</div>
                        <div class="score"><?php echo "$priceScore"; ?></div>
                    </div>
                    <div class="review-score">
                        <div class="label">Safety Score:</div>
                        <div class="score"> <?php echo "$safetyScore"; ?></div>
                    </div>
                    <div class="review-score">
                        <div class="label">Atmosphere Score:</div>
                        <div class="score"> <?php echo "$atmosphereScore"; ?></div>
                    </div>
                    <div class="review-score">
                        <div class="label">Queue Times Score:</div>
                        <div class="score"> <?php echo "$queueScore"; ?></div>
                    </div>
                </div>
                <?php
                if (isset($userID)) {
                    $checkReview = checkReviewWritten($userID, 1, $venueID, $pdo);
                    if ($checkReview === false) {
                        echo '<a class="button" style="width: 100%;" href="review-creation.php?venueID=' . $venueID . '">Write a Review</a>';
                    } else {
                        echo '<a class="button" style="width: 100%;" href="review-edit.php?reviewID=' . $checkReview . '">Edit Review</a>';
                    }
                }
                ?>
            </div>
            <div class="section" id="All Reviews" style="flex-grow: 10">
                <h2 class="title">All Reviews</h2>
                <?php
                if ($reviews !== false) {
                    $counter = 0;
                    echo '<div class="reviews">';
                    $reviews = array_reverse($reviews);
                    foreach ($reviews as $row) {
                        if ($counter < 5) {
                            echo "<div class='review'>";
                            echo "<label>Review left by:<b> " . userIDtoUserName($row['UserID'], $pdo) . "</b></label>";
                            echo "<div class='review-text' >" . $row['ReviewText'] . "</div>";
                            echo "<div class='review-scores'>";
                            echo "<div class='review-score'><div class='label'>Price Score:</div><div class='score'>" . $row['ReviewPrice'] . "</div></div>";
                            echo "<div class='review-score'><div class='label'>Safety Score:</div><div class='score'> " . $row['ReviewSafety'] . "</div></div>";
                            echo "<div class='review-score'><div class='label'>Atmosphere Score:</div><div class='score'> " . $row['ReviewAtmosphere'] . "</div></div>";
                            echo "<div class='review-score'><div class='label'>Queue Times Score:</div><div class='score'> " . $row['ReviewQueue'] . "</div></div></div>";
                            echo "<label>Review posted on: " . $row['ReviewDate'] . "</label></div>";
                        }
                        $counter++;
                    }
                    echo '</div>';
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
