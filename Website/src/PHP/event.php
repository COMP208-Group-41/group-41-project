<?php

    session_start();

    if (isset($_SESSION['UserID'])) {
        $userID = $_SESSION['UserID'];
    } else if (isset($_SESSION['VenueUserID'])) {
        $venueUserID = $_SESSION['VenueUserID'];
    }

    require_once "config.php";



?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
</head>
<body>
<div class="wrapper">
    <div class="container">
        <div style="display: flex; flex-direction: column">
            <h1 class="title">Event name here</h1>
            <label>Image:</label>
            <img src="../Assets/event-image.jpg">
            <div class="seperator"></div>
            <label>Location:</label>
            <label>Location here</label>

            <label>Event time:</label>
            <label>Time here:</label>
            <label>Event description:</label>
            <textarea readonly placeholder="Description of event here"></textarea>

            <label style="text-align: center; margin-top: 16px;"><b>Venue Tags:</b></label>
            <div style="display: flex; justify-content: center; ">
                <div class="tag-container" style="text-align: center">
                    <?php getTags($currentTagIDs,$pdo); ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
