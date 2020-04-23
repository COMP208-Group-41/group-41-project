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
    <title>OutOut - Edit Venue User Account</title>
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
        echo "<div class='message-wrapper'><div class='success'>".$_SESSION['message']."</div></div>";
        unset($_SESSION['message']);
    }
?>
<div class="wrapper">
<div class="container">

    <div class="seperator"></div>
    <h2 class="title">Our Picks</h1>
    <?php
        include "slideshow.php";

        echo '<div class="seperator"></div>';

        if (isset($userID)) {

            echo '<a class="button" href="user-dashboard.php">Your Dashboard</a>';
            echo '<div class="seperator"></div>';
            echo '<h2 class="title">Recommended venues for you</h2>';
            //content here
            echo '<a class="button" href="recommended-venues.php">View more recommended venues</a>';
            echo '<div class="seperator"></div>';
            echo '<h2 class="title">Recommended venues for you</h2>';
            //content here
            echo '<a class="button" href="recommended-events.php">View more recommended events</a>';
            echo '<div class="seperator"></div>';

        }

        if (isset($venueUserID)) {
            echo '<a class="button" href="venue-user-dashboard.php">Your Dashboard</a>';
        }


        echo '<div style="display: flex; width: 100%"><a class="button" href="all-venues.php" style="width: 50%">All Venues</a>';
        echo '<a class="button" href="all-events.php" style="width: 50%;">All Upcoming Events</a></div>';
    ?>

</div>
</div>
</body>
</html>
