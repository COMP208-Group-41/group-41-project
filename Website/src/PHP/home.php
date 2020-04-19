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
    <link rel="stylesheet" href="../css/home.css">
</head>
<body>
<?php include "navbar.php" ?>
<div class="container">
    <div class="form">
        <form name='search form' method='post'>
            <input type='text' name='venue' placeholder="venue">
            <input type='date' name='date' placeholder="Password">
        </form>
    </div>
    <br>

    <div style="text-align:center">
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(1)"></span>
    </div>

    <div class="info_banner">
        <div infomessage1>
        </div>
    </div>


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
<script>
    var slideIndex = 1;
    showSlides(slideIndex);

    // Next/previous controls
    function plusSlides(n) {
        showSlides(slideIndex += n);
    }

    // Thumbnail image controls
    function currentSlide(n) {
        showSlides(slideIndex = n);
    }

    function showSlides(n) {
        var i;
        var slides = document.getElementsByClassName("mySlides");
        var dots = document.getElementsByClassName("dot");
        if (n > slides.length) {
            slideIndex = 1
        }
        if (n < 1) {
            slideIndex = slides.length
        }
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slides[slideIndex - 1].style.display = "block";
        dots[slideIndex - 1].className += " active";
    }
</script>
</body>
</html>
