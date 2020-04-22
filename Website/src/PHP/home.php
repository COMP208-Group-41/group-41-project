<?php

    // Start the session
    session_start();

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
            <input type='text' name='search' placeholder="Search for venue.." class="searchbar">
            <input type='submit' value='Search' class='button search-button'>
        </form>
    </div>
    <!-- Slideshow from W3Schools https://www.w3schools.com/howto/howto_js_slideshow.asp -->
    <div class="slideshow-container">
        <div class="mySlides fade">
            <div class="numbertext">1 / 3</div>
            <img src="../Assets/background.jpg">
            <div class="text">Caption Text</div>
        </div>

        <div class="mySlides fade">
            <div class="numbertext">2 / 3</div>
            <img src="../Assets/background1.jpg">
            <div class="text">Caption Two</div>
        </div>

        <div class="mySlides fade">
            <div class="numbertext">3 / 3</div>
            <img src="../Assets/background2.jpg">
            <div class="text">Caption Three</div>
        </div>

        <!-- Next and previous buttons -->
        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
    </div>

    <div style="text-align:center; margin-top: 8px">
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
        <span class="dot" onclick="currentSlide(3)"></span>
    </div>
    <script src="../js/slideshow.js"></script>
    <br>

    <?php
        if (isset($userID)) {
            echo '<a class="button" href="recommended-venues.php">Recommended Venues for you</a>';
            echo '<a class="button" href="user-dashboard.php">Your Dashboard</a>';
        }

        if (isset($venueUserID)) {
            echo '<a class="button" href="venue-user-dashboard.php">Your Dashboard</a>';
        }

        echo '<a class="button" href="all-venues.php">All Venues</a>';
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
