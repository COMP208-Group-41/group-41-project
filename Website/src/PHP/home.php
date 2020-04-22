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

    if (isset($_POST['search'])){
      header("location: search.php?search=".$_POST['searchText']."");
      exit;
    }

?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <title>OutOut - Edit Venue User Account</title>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/home.css">
    <link rel="stylesheet" type="text/css" href="../css/slideshow.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
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
    <div class="form">
        <form name='search form' method='post'>
            <input type='text' name='searchText' placeholder="Search for venue.." class="searchbar">
            <input type='submit' name='search' value='Search' class='button search-button'>
        </form>
    </div>
    <div class="seperator"></div>
    <h2 class="title">Our Picks</h1>
    <?php
        include "slideshow.php";

        echo '<div class="seperator"></div>';

        if (isset($userID)) {

            echo '<a class="button" href="user-dashboard.php">Your Dashboard</a>';
            echo '<a class="button" href="recommended-venues.php">Recommended Venues for you</a>';
        }

        if (isset($venueUserID)) {
            echo '<a class="button" href="venue-user-dashboard.php">Your Dashboard</a>';
        }

        echo '<a class="button" href="all-venues.php">All Venues</a>';
        echo '<a class="button" href="all-events.php">All Upcoming Events</a>';
    ?>


    <!-- Photo Grid  <div class="row">
        <div class="column">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
        </div>
        <div class="column">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
        </div>
        <div class="column">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
        </div>
        <div class="column">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
            <img src="../Assets/background.jpg" style="width:100%">
        </div>
    </div>-->


</div>
</div>
</body>
</html>
