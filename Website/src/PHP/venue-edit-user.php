<?php
    // Session is started
    session_start();

    /* If the venue user is not logged in then redirect to venue login */
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: venue-login.php");
        exit;
        /* If the user is logged in but they are not a venue user then they are
         * redirected to home page
         */
    } else if (!isset($_SESSION["VenueUserID"])) {
        header("location: home.php");
        exit;
    }

    // The config file is imported here for any database connections required later
    require_once "config.php";

?>

<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Edit Venue User Account</title>
    <link rel="stylesheet" type="text/css" href="../css/venue-edit-user.css">
</head>
<body>
<div class="wrapper">
    <img src="../Assets/outout.svg" alt="OutOut">
    <form name='EditVenueUserDetails' method='post' style="margin-top: 10px">
        <div class="edit-fields">
            <input type='text' name='email' placeholder="Email">
            <input type='password' name='password' placeholder="Current Password">
            <input type='password' name='newPassword' placeholder="New Password">
            <input type='password' name='confirmNewPassword' placeholder="Confirm New Password">
            <input type='text' name='accountName' placeholder="New Account Name">
            <input type='text' name='externalLink' placeholder="Venue Website Link">
            <input type='submit' value='Save'>
            <input type='submit' value='Delete Account'>
        </div>
    </form>
</div>
</body>
</html>
