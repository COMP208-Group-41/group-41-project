<?php


session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once "config.php";

if (!isset($_GET['search'])) {
    // search term not provided
    $_SESSION['message'] = "No Search term given!";
    header("location: 404.php");
    exit;
}

if (trim($_GET['search']) == "") {
    // search term not provided
    $_SESSION['message'] = "No Search term given!";
    header("location: 404.php");
    exit;
}

$search = strtolower(trim($_GET['search']));
$allEvents = getAllEvents($pdo);
$allVenues = getAllVenues($pdo);

?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Search Results</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/all-venues.css">
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
        <h1 class='title'>Matching Results for '<?php echo "$search"; ?>'</h1>
        <?php
        $venueCount = $eventCount = 0;
        echo "<div class='seperator'></div>";
        echo "<h2 class='title'>Venues</h2>";
        foreach ($allVenues as $row) {
            //echo $search;
            //echo $row['VenueName'];
            if (strpos(strtolower($row['VenueName']), $search) !== false) {
                $venueCount++;
                //print_r($row);
                $currentTagIDs = getVenueTagID($row['VenueID'], $pdo);
                echo "<div class='table'>";
                echo "<div class='table-row'>";
                $venueImage = "https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/" . $row['VenueUserID'] . "/" . $row['VenueID'] . "/venue.jpg";
                echo "<div class='table-item image' style='background-image: url(" . $venueImage . "); width: 40%;'><div class='table-item-wrapper'>" . $row['VenueName'];

                unset($priceScore);
                unset($safetyScore);
                unset($atmosphereScore);
                unset($queueScore);
                unset($totalScore);
                $priceScore = getPriceScore($row['VenueID'], 1, $pdo);
                $safetyScore = getSafetyScore($row['VenueID'], 1, $pdo);
                $atmosphereScore = getAtmosphereScore($row['VenueID'], 1, $pdo);
                $queueScore = getQueueScore($row['VenueID'], 1, $pdo);
                if (!($priceScore === false || $safetyScore === false || $atmosphereScore === false || $queueScore === false)) {
                    $totalScore = ($queueScore + $atmosphereScore + $safetyScore + $priceScore) / 4;
                    $totalScore = number_format($totalScore, 1);
                    $priceScore = number_format($priceScore, 1);
                    $safetyScore = number_format($safetyScore, 1);
                    $atmosphereScore = number_format($atmosphereScore, 1);
                    $queueScore = number_format($queueScore, 1);
                    echo "<div class='rating-wrapper'>Rating:<div class='rating-square'>$totalScore</div></div>";
                } else {
                    echo "<div class='rating-wrapper'>No Ratings</div>";
                }
                echo "</div></div>";
                echo '<div class="table-item" style="text-align: center; width: 40%">' . getTagsNoEcho($currentTagIDs, $pdo) . '</div>';
                echo '<div class="table-buttons column" style="width: 20%;"><a href="venue.php?venueID=' . $row['VenueID'] . '" class="table-button">Venue</a>';
                echo '<a href="upcoming-events.php?venueID=' . $row['VenueID'] . '" class="table-button">Events</a></div>';
                echo "</div></div>";

            }
        }
        if ($venueCount == 0) {
            echo "<h2 class='title'>No matching venues found!</h2>";
        }

        // Matching Events
        echo "<div class='seperator'></div>";
        echo "<h2 class='title'>Events</h2>";
        foreach ($allEvents as $row) {
            if (strpos(strtolower($row['EventName']), $search) !== false) {
                $eventCount++;
                //print_r($row);
                $currentTagIDs = getEventTagID($row['EventID'], $pdo);
                if (new DateTime("now") < new DateTime($row['EventEndTime'])) {
                    echo "<div class='table'>";
                    echo "<div class='table-row'>";
                    $venueUserID = venueIDtoVenueUserID($row['VenueID'], $pdo);
                    $venueImage = "https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/" . $venueUserID . "/" . $row['VenueID'] . "/" . $row['EventID'] . "/event.jpg";
                    echo "<div class='table-item image' style='background-image: url($venueImage); width: 40%;'>";
                    echo "<div class='table-item-wrapper' style='justify-content: center; align-items: center'>" . $row['EventName'];
                    echo "<div class='rating-wrapper'><div class='rating-square'>Start: " . $row['EventStartTime'] . "</div></div></div></div>";
                    echo '<div class="table-item" style="text-align: center; width: 40%">' . getTagsNoEcho($currentTagIDs, $pdo) . '</div>';
                    echo '<div class="table-buttons column" style="width: 20%"><a href="event.php?eventID=' . $row['EventID'] . '" class="table-button">Event</a>';
                    echo '<a href="upcoming-events.php?venueID=' . $row['VenueID'] . '" class="table-button">View Venue</a></div>';
                    echo "</div></div>";
                }
            }
        }
        if ($venueCount == 0) {
            echo "<h2 class='title'>No matching venues found!</h2>";
        }
        ?>
    </div>
</div>
</body>
</html>
