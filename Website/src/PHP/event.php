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

    if (isset($_POST['follow'])) {
        if (follow($userID,$eventID,$pdo)) {
            // Follow Successful!
            $_SESSION['message'] = "You are now following this event!";
            $following = true;
        }
    }

    if (isset($_POST['unfollow'])) {
        if (unfollow($userID,$eventID,$pdo)) {
            $_SESSION['message'] = "You have unfollowed this event!";
            $following = false;
        }
    }

    function unfollow($userID,$eventID,$pdo) {
        // Need to unfollow the event
        $unfollowStmt = $pdo->prepare("DELETE FROM InterestedIn WHERE UserID=:UserID AND EventID=:EventID");
        $unfollowStmt->bindValue(":UserID",$userID);
        $unfollowStmt->bindValue(":EventID",$eventID);
        if ($unfollowStmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    function follow($userID,$eventID,$pdo) {
        $followStmt = $pdo->prepare("INSERT INTO InterestedIn (UserID,EventID) VALUES (:UserID,:EventID)");
        $followStmt->bindValue(":UserID",$userID);
        $followStmt->bindValue(":EventID",$eventID);
        if ($followStmt->execute()) {
            return true;
        } else {
            return false;
        }
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
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/events.css">
    <link rel="stylesheet" type="text/css" href="../css/review.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            <h1 class="title" style="margin-bottom: 8px"><?php echo $name; ?></h1>
            <h2 class="title" style="text-decoration: none">@ <?php echo '<a href="venue.php?venueID='.$venueID.'">'.$venueName.'</a>'; ?></h2>
                <?php
                    if (isset($following)) {
                        if ($following) {
                            echo '<form id="unfollow" name="unfollowForm" method="post">';
                            echo '<input type="submit" name="unfollow" value="Unfollow This Event" class="button" style="width: 100%"></form>';
                        } else {
                            echo '<form id="follow" name="followForm" method="post">';
                            echo '<input type="submit" name="follow" value="Follow This Event" class="button" style="width: 100%"></form>';
                        }
                    }
                ?>
            <div class='image'>
                <?php
                    if ($image) {
                        echo '<div class="seperator"></div>';
                        echo '<img src="https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/'.$owner.'/'.$venueID.'/'.$eventID.'/event.jpg" alt="Event Image" class="title-img">';
                    }
                ?>
            </div>
            <div class="seperator"></div>
            <div style="display: flex;">
                <label class="text">Starts: <?php echo $startTime; ?></label>
                <label class="text">Ends: <?php echo $endTime; ?></label></div>
            <label>Event description:</label>
            <textarea readonly placeholder="Description of event here"><?php echo $description; ?></textarea>
            <label style="text-align: center; margin-top: 16px;"><b>Event Tags:</b></label>
            <div style="display: flex; justify-content: center; ">
                <div class="tag-container" style="text-align: center">
                    <?php getTags($currentTagIDs,$pdo); ?>
                </div>
            </div>
            <div class="seperator"></div>
            <h2 class='title'>Overall Event Scores</h2>
            <div class="flex-wrap">
                <div class="section" id="Venue Score">
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
                            <div class="label">Queuing Score:</div>
                            <div class="score"> <?php echo "$queueScore"; ?></div>
                        </div>
                    </div>
                    <div class="seperator"></div>
                    <?php
                    if (isset($userID)) {
                        $compareDate = new DateTime($result['EventEndTime']);
                        if (new DateTime("now") > $compareDate) {
                            $checkReview = checkReviewWritten($userID,$eventID,$venueID,$pdo);
                            if ($checkReview === false) {
                                echo '<a class="button" style="width: 100%;" href="review-creation.php?eventID='.$eventID.'">Write a Review</a>';
                            } else {
                                echo '<a class="button" style="width: 100%;" href="review-edit.php?reviewID='.$checkReview.'">Edit Review</a>';
                            }
                        }
                    }
                    ?>
                </div>
                <div class="section" id="All Reviews" style="flex-grow: 10">
                    <h2 class="title">All Reviews</h2>
                        <?php
                        if ($reviews !== false){
                            echo '<div class="reviewlist">';
                          $counter = 0;
                          foreach ($reviews as $row) {
                            if($counter<5){
                                echo "<div class='review'>";
                                echo "<label>Review left by:<b> " . userIDtoUserName($row['UserID'], $pdo) . "</b></label>";
                                echo "<textarea readonly onchange='this.style.height = \"\";this.style.height = this.scrollHeight + 3 + \"px\"'>" . $row['ReviewText'] . "</textarea>";
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
                          echo '<label>No reviews currently posted for this event</label>';
                        }
                        ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
