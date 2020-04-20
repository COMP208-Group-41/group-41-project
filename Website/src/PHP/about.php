<?php

    session_start();

?>

<!DOCTYPE html>
<html>
<head>
    <title>OutOut - About Us</title>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
</head>
<body>
    <?php include "navbar.php" ?>
    <div class="wrapper">
        <?php
            if (isset($_SESSION['message'])) {
                echo "<div class='success'>".$_SESSION['message']."</div>";
                unset($_SESSION['message']);
            }
        ?>
        <div class="container">
            <div class="outout-wrapper" style="padding-bottom: 10px">
                <img src="../Assets/outout.svg" alt="OutOut">
            </div>
            <div style="padding-bottom: 8px; text-align: center">
                <b style="color: #e9e9e9; font-size: 24px">About Us</b>
            </div>
            <p>OutOut is a website to view and recommend venues and events for
            nightlife around Liverpool for people looking for a good night out.<br>
            This website was made for a second year group project for Computer
            Science at the University of Liverpool. <br> This was made by Samuel Tribe,
            Will Dunnion, Daniel Ambrose, Orestes Vasilikos, Desmond Anianu and
            Harrison Porter. <br> The images used for this site are being used purely
            for educational purposes and no money is being made through this service.
            <br>All coding is our own and we would ask to please ask permission before
            using any of this site for your own purpose. Email S.Tribe@student.liverpool.ac.uk.
            <br>We hope you enjoy the site.
            </p>
        </div>
    </div>
</body>
</html>
