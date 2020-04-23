<?php

// Start the session
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once "config.php";

if (isset($_SESSION['UserID'])) {
    $userID = $_SESSION['UserID'];
}

if (isset($_SESSION['VenueUserID'])) {
    $venueUserID = $_SESSION['VenueUserID'];
}

?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <title>OutOut</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/home.css">
    <link rel="stylesheet" type="text/css" href="../css/slideshow.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <div style="display: flex;width: 100%; justify-content: center;align-items: center">
            <img src="../Assets/outout.svg" width="120" alt="OutOut"></div>
        <div class="seperator"></div>
        <h2 class="title">Our Picks</h1>
            <?php
            include "slideshow.php";

            echo '<div class="seperator"></div>';

            if (isset($userID)) {
                $allVenues = getAllVenues($pdo);
                $userPrefs = getUserTags($userID, $pdo);
                $sortedArray = (array)null;
                foreach ($allVenues as $row) {
                    $venue = $emptyArray = (array)null;
                    $venueTags = getVenueTagID($row['VenueID'], $pdo);
                    $count = 0;
                    foreach ($userPrefs as $pref) {
                        if (in_array($pref, $venueTags)) {
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        $event['Count'] = $count;
                        $event['VenueUserID'] = $row['VenueUserID'];
                        $event['VenueID'] = $row['VenueID'];
                        $event['VenueName'] = $row['VenueName'];
                        array_push($sortedArray, $event);
                    }
                }
                sortArray($sortedArray);
                $sortedArray = array_reverse($sortedArray);

                echo '<a class="button" href="user-dashboard.php">Your Dashboard</a>';
                echo '<div class="seperator"></div>';

                echo '<h2 class="title">Recommended venues for you</h2>';
                if (sizeof($sortedArray) != 0) {
                    $mostRecommended = $sortedArray[0];
                    $venueUserIDforPic = $mostRecommended['VenueUserID'];
                    $venueIDforPic = $mostRecommended['VenueID'];
                    $path = "https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/" . $venueUserIDforPic . "/" . $venueIDforPic . "/venue.jpg";
                    // $mostRecommended['VenueName'] - This gives the venue name
                    echo "<div class='slideshow-container'><a href='venue?venueID=" . $row['VenueID'] . "' style='position: relative'>";
                    echo "<img src='$path' class='title-img' style='max-height: 300px'></a>";
                    echo '<a class="button" href="recommended-venues.php">View more recommended venues</a>';
                    echo '<div class="text-wrapper"><div class="text">'.$mostRecommended['VenueName'].'</div></div></div>';
                } else {
                    echo "<h2 class='title'>No venue recommendations found!</h2>";
                }

                echo '<div class="seperator"></div>';
                echo '<h2 class="title">Recommended events for you</h2>';
                $sortedArray = (array)null;
                $allEvents = getAllEvents($pdo);
                reset($userPrefs);
                foreach ($allEvents as $row) {
                    $event = $emptyArray = (array)null;
                    $eventTags = getEventTagID($row['EventID'], $pdo);
                    $count = 0;
                    foreach ($userPrefs as $pref) {
                        if (in_array($pref, $eventTags)) {
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        $event['Count'] = $count;
                        $event['VenueID'] = $row['VenueID'];
                        $event['EventID'] = $row['EventID'];
                        $event['EventName'] = $row['EventName'];
                        array_push($sortedArray, $event);
                    }
                }
                sortArray($sortedArray);
                $sortedArray = array_reverse($sortedArray);

                if (sizeof($sortedArray) != 0) {
                    $mostRecommended = $sortedArray[0];
                    $venueIDforPic = $mostRecommended['VenueID'];
                    $venueUserIDforPic = venueIDtoVenueUserID($venueIDforPic, $pdo);
                    $eventIDforPic = $mostRecommended['EventID'];
                    $path = "https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/" . $venueUserIDforPic . "/" . $venueIDforPic . "/" . $eventIDforPic . "/event.jpg";
                    echo "<div class='slideshow-container'><a href='event?eventID=" . $row['EventID'] . "'>";
                    echo "<img src='$path' class='title-img' style='max-height: 300px'></a>";
                    echo '<div class="text-wrapper"><div class="text">'.$mostRecommended['EventName'].'</div></div><div>';
                    echo '<a class="button" href="recommended-events.php">View more recommended events</a>';
                } else {
                    echo "<h2 class='title'>No event recommendations found!</h2>";
                }
                echo '<div class="seperator"></div>';
            }

            if (isset($venueUserID)) {
                echo '<a class="button" href="venue-user-dashboard.php">Your Dashboard</a>';
            }


            echo '<div style="display: flex; width: 100%"><a class="button left" href="all-venues.php" style="width: 50%">All Venues</a>';
            echo '<a class="button right" href="all-events.php" style="width: 50%;">All Upcoming Events</a></div>';
            ?>

    </div>
</div>
</body>
</html>
