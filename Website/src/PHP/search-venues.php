<?php


    session_start();

    require_once "config.php";

    $search = $_GET['search'];
    $allVenues = getAllVenues($pdo);
    // EXPRESSION TO FILTER NEEDED HERE

?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Matching Venues</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/all-venues.css">
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
            <div class="section">
                <h1 class='title'>Matching Venues</h1>
                <?php
                if (sizeof($allVenues) != 0) {
                    echo "<div class='list'>";
                    foreach($allVenues as $row) {
                        $currentTagIDs = getVenueTagID($row['VenueID'],$pdo);
                        echo "<div class='venue'>";
                        echo "<div class='venue-name'>".$row['VenueName'];
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
                            echo "<div class='rating-wrapper'>Rating:<div class='rating-square'>$totalScore</div></div>";
                        } else {
                            echo "<div class='rating-wrapper'>No Ratings</div>";
                        }
                        echo "</div>";
                        echo '<div class="venue-tags" style="text-align: center">'.getTagsNoEcho($currentTagIDs,$pdo).'</div>';
                        echo '<div class="venue-buttons"><a href="venue.php?venueID='.$row['VenueID'].'" class="venue-button" style="margin-bottom: -2px">Venue</a>';
                        echo '<a href="upcoming-events.php?venueID='.$row['VenueID'].'" class="venue-button">Events</a></div>';
                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<h2 class='title'>No matching venues found!</h2>";
                }
                ?>
            </div>
        </div>
    </div>

</body>
</html>
