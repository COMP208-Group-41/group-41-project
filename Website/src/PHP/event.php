<?php

    session_start();

    require_once "config.php";

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    if (isset($_SESSION['UserID'])) {
        $userID = $_SESSION['UserID'];
    } else if (isset($_SESSION['VenueUserID'])) {
        $venueUserID = $_SESSION['VenueUserID'];
    }

    if (isset($_GET['EventID'])) {
        $eventID = $_GET['EventID'];
    } else {
        $_SESSION['message'] = "No Event ID specified!";
        header("location: 404.php");
        exit;
    }

    


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
