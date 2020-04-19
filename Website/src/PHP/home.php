<?php

    // Start the session
    session_start();

    require_once "config.php";

?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <title>OutOut - Edit Venue User Account</title>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/home.css">
    <link rel="stylesheet" type="text/css" href="../css/slideshow.css">
</head>
<body>
<?php include "navbar.php" ?>
<?php
    if (isset($_SESSION['message'])) {
        echo "<div class='success'>".$_SESSION['message']."</div>";
        unset($_SESSION['message']);
    }
?>
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
    <div class="container2">
        <section class="cms-boxes">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4 cms-boxes-outer">
                        <div class="cms-boxes-items cms-features">
                            <div class="boxes-align">
                                <div class="small-box">
                                    <h3>bars close to you</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 cms-boxes-outer">
                        <div class="cms-boxes-items cms-security">
                            <div class="boxes-align">
                                <div class="small-box">
                                    <h3>Highly <br> recommended</h3>
                                    <p>collection of bar favourited by customers.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 cms-boxes-outer">
                        <div class="cms-boxes-items cms-scalability">
                            <div class="boxes-align">
                                <div class="small-box">
                                    <h3>Trending this Week in liverpool</h3>
                                    <p>Trending this week in liverpool</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 cms-boxes-outer">
                        <div class="cms-boxes-items cms-built">
                            <div class="boxes-align">
                                <div class="large-box">
                                    <h3>Pics of the Week !!</h3>
                                    <p></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 cms-boxes-outer">
                        <div class="cms-boxes-items cms-documentation">
                            <div class="boxes-align">
                                <div class="large-box">
                                    <h3>Events</h3>
                                    <p>.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <h1>view all</h1>

        </section>
    </div>

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
</body>
</html>
