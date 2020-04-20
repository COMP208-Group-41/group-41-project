<?php

  session_start();

  error_reporting( E_ALL );
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);

  require_once "config.php";

  $venueID = $_GET['venueID'];
  $result = getVenueInfo($venueID,$pdo);
  $owner = $result['VenueUserID'];
  $name = $result['VenueName'];
  $description = $result['VenueDescription'];
  $address = $result['VenueAddress'];
  $times = $result['VenueTimes'];

  if (isset($_SESSION["UserID"])){
    $userID = $_SESSION["UserID"];
  } elseif (isset($_SESSION["VenueUserID"]) && $owner == $_SESSION["VenueUserID"]){
    $venueUserID = $_SESSION["VenueUserID"];
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
            <h1 class="title">Venue name here</h1>
            <div class="seperator"></div>

            <label>Image:</label>
            <img src="../Assets/venue-image.jpg" alt="Venue Image">

            <label>Location:</label>
            <label>Location here</label>

            <label>Opening times:</label>
            <textarea readonly placeholder="Opening times here"></textarea>

            <label>Venue description:</label>
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
