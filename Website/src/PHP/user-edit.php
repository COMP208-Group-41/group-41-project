<?php

    session_start();

    require_once "config.php";

?>

<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Edit Venue User Account</title>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
</head>
<body>
<?php include "navbar.php" ?>
<div class="wrapper">
    <div class="container">
        <h1 class="title">Account Settings</h1>
        <form name='EditVenueUserDetails' method='post' style="margin-top: 10px">
            <div class="edit-fields">
                <label>Email:</label>
                <input type='text' name='email' value="<?php echo $email; ?>">
                <label for='username'>Username:</label>
                <input type='text' name='username' value="<?php echo $username; ?>">
                <label for='newPassword'>New password:</label>
                <input type='password' name='newPassword'>
                <label for='confirmNewPassword'>Confirm new password:</label>
                <input type='password' name='confirmNewPassword'>
                <label for='dob'>Date of birth:</label>
                <input type='date' name='dob' value="<?php echo $dob; ?>">
                <div class="seperator">
                    <label for='password'>Enter current password to allow changes:</label>
                    <input type='password' name='password' required>
                </div>
                <!-- require password for any change! -->
                <input type='submit' name='submit' value='Save' class="button">
            </div>
        </form>
    </div>
</div>
<?php
    if ($errorMessage != "") {
         echo "<div class='error'>$errorMessage</div>";
    }
    if (isset($_SESSION['message'])) {
        echo "<div class='success'>".$_SESSION['message']."</div>";
        unset($_SESSION['message']);
    }
?>
</body>
</html>
