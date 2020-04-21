<?php

    session_start();

    if (isset($_SESSION['VenueUserID'])) {
        $_SESSION['message'] = "Venue Users cannot use the recommended page";
        header("location: venue-user-dashboard.php");
        exit;
    }

    if (!isset($_SESSION['UserID'])) {
        $_SESSION['message'] = "You must be logged in to view recommended Venues";
        header("location: login.php");
        exit;
    }

    $allVenues = 

?>
<!DOCTYPE html>
<html lang='en-GB'>

<head>
    <title>OutOut - Recommended Venues</title>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
</head>
<body>
  <?php include "navbar.php" ?>
  <div class="wrapper">






  </div>
</body>
</html>
