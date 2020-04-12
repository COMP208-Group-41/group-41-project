<?php

    /* Ensure that the php code does not pull the existing info from the
     * database before submitting new values if the user has clicked the submit
     * button!!!
     */

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

    $venueUserID = $_SESSION["VenueUserID"];
    $name = $email = $external = "";
    $passwordError = $emailError = $nameError = $linkError = "";

    /* The user has clicked the Save button, submit  */
    if (!empty($_POST) && isset($_POST['submit'])) {
        if (!empty($_POST['password']) && !empty($_POST['newPassword']) && !empty($_POST['confirmNewPassword'])) {
            // The user is changing thier password, do all checks for password
            // First check if the original password is correct
            $password = $_POST['password'];
            if (verifyVenuePassword($venueUserID,$password,$pdo)) {
                /* If the password given is correct then check if both of the
                 * new passwords are valid and match
                 */

            } else {
                // Password was not correct, show error message
                $passwordError = "Password incorrect!";
            }
        }
    } else {
        $result = getVenueUserInfo($venueUserID,$pdo);
        $name = $result['VenueUserName'];
        $email = $result['VenueUserEmail'];
        $external = $result['VenueUserExternal'];
    }

    function getVenueUserInfo($venueUserID, $pdo) {
        $infoStmt = $pdo->prepare("SELECT VenueUserEmail,VenueUserName,VenueUserExternal FROM VenueUser WHERE VenueUserID=:VenueUserID");
        $infoStmt->bindValue(":VenueUserID",$venueUserID);
        $infoStmt->execute();
        return $infoStmt->fetch();
    }

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
            <input type='text' name='email' placeholder="Email" value="<?php echo $email; ?>"><br>
            <input type='password' name='newPassword' placeholder="New Password"><br>
            <input type='password' name='confirmNewPassword' placeholder="Confirm New Password"><br>
            <input type='text' name='companyName' placeholder="Change Company Name" value="<?php echo $name; ?>"><br>
            <input type='text' name='externalLink' placeholder="Venue Website Link" value="<?php echo $external; ?>"><br>
            <input type='password' name='password' placeholder="Current Password"><br>
            <!-- require password for any change! -->
            <input type='submit' value='Save'><br>
        </div>
    </form>
</div>
</body>
</html>
