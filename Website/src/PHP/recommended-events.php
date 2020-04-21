<?php

    session_start();

    if (isset($_SESSION['VenueUserID'])) {
        $_SESSION['message'] = "Venue Users cannot use the recommended page";
        header("location: venue-user-dashboard.php");
        exit;
    }

    if (!isset($_SESSION['UserID'])) {
        $_SESSION['message'] = "You must be logged in to view recommended Venues and Events";
        header("location: venue-user-dashboard.php");
        exit;
    }

?>
<!DOCTYPE html>
<html lang='en-GB'>


</html>
