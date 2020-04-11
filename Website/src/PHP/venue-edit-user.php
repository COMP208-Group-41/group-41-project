<?php

?>


<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Edit Venue User Account</title>
    <link rel="stylesheet" type="text/css" href="../css/venue-edit-user.css">
</head>
<body>
<div class="wrapper">
    <img src="../../Assets/outout.svg" alt="OutOut">
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
