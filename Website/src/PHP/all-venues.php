<?php

    /* This will list all venues in the system */

    session_start();

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    $allVenues = getAllVenues($pdo);

?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Venues</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/all-venues.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
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
                <h1 class='title'>All Venues</h1>
                <div class="seperator" style="margin-top: 4px"></div>
                <?php
                if (sizeof($allVenues) != 0) {

                    foreach($allVenues as $row) {
                        $currentTagIDs = getVenueTagID($row['VenueID'],$pdo);
                        echo "<div class='table'>";
                        echo "<div class='table-row'>";
                        $venueImage = "https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/".$row['VenueUserID']."/".$row['VenueID']."/venue.jpg";
                        echo "<div class='table-item image' style='background-image: url(".$venueImage."); width: 35%'><div class='table-item-wrapper'>".$row['VenueName'];
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
                        echo "</div></div>";
                        echo '<div class="table-item" style="text-align: center; width: 35%">'.getTagsNoEcho($currentTagIDs,$pdo).'</div>';
                        echo '<div class="table-buttons column"><a href="venue.php?venueID='.$row['VenueID'].'" class="table-button">Venue</a>';
                        echo '<a href="upcoming-events.php?venueID='.$row['VenueID'].'" class="table-button">Events</a></div>';
                        echo "</div></div>";
                    }
                } else {
                    echo "<h2 class='title'>No venues found!</h2>";
                }
                ?>
                </div>
        </div>
    </div>

</body>
</html>
