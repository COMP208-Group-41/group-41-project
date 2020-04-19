<?php

    session_start();

    require_once "config.php";

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    $userID = $_SESSION['UserID'];
    $email = $username = $dob = $newPassword = $password = "";
    $errorMessage = "";

    $result = getUserInfo($userID,$pdo);
    $username = $result['UserName'];
    $email = $result['UserEmail'];
    $dob = $result['UserDOB'];


    try {
        if (!empty($_POST) && isset($_POST['submit'])) {
            if (isset($_POST['password']) && !empty($_POST['password'])) {
                // First check if the original password is correct
                $password = $_POST['password'];
                if (verifyVenuePassword($venueUserID,$password,$pdo)) {
                    // If the password given is correct then check other fields
                    if (performChecks($venueUserID,$email,$name,$external,$pdo,$errorMessage)) {
                        // Changes done successfully, show confirmation message
                        $_SESSION['message'] = "Changes made successfully!";
                        // Refresh details
                        $result = getVenueUserInfo($venueUserID,$pdo);
                        $name = $result['VenueUserName'];
                        $email = $result['VenueUserEmail'];
                        $external = $result['VenueUserExternal'];
                    }
                } else {
                    // Password was not correct, show error message
                    $errorMessage = "Password incorrect!";
                }
            } else {
                /* The password field is empty, show error message and don't save
                 * any changes!
                 */
                 $errorMessage = "You must enter your password to make any changes!";
            }
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

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
    <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='success'>".$_SESSION['message']."</div>";
            unset($_SESSION['message']);
        }
    ?>
    <div class="container">
        <h1 class="title">Account Settings</h1>
        <form name='EditVenueUserDetails' method='post' style="margin-top: 10px">
            <div class="edit-fields">
                <label for='email'>Email:</label>
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
?>
</body>
</html>
