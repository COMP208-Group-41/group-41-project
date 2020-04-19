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
    $userPrefs = getUserTags($userID,$pdo);
    $interestedIn = getInterested($userID,$pdo);





    try {
        if (!empty($_POST) && isset($_POST['submit'])) {
            if (performChecks($userID,$errorMessage,$pdo)) {
                // Changes done successfully, show confirmation message
                $_SESSION['message'] = "Changes made successfully!";
                // Refresh details
                $result = getUserInfo($userID,$pdo);
                $username = $result['UserName'];
                $email = $result['UserEmail'];
                $dob = $result['UserDOB'];
                $userPrefs = getUserTags($userID,$pdo);
                $interestedIn = getInterested($userID,$pdo);
            }
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        exit("PDO Error: ".$e->getMessage()."<br>");
    }


    performChecks($userID,&$errorMessage,$pdo) {
        if (!(isset($_POST['password']) && !empty($_POST['password']))) {
            $password = $_POST['password'];
            if (!verifyPassword($userID,$password,$pdo)) {
                $errorMessage = "Password Incorrect!";
                return false;
            }
        } else {
            $errorMessage = "Please enter your password to edit your details";
            return false;
        }

        if (!(isset($_POST['username']) && !empty(trim($_POST['username'])))) {
            $errorMessage = "Please enter a username!";
            return false;
        } else {
            $username = $_POST['username'];
            if (!validate255($username)) {
                $errorMessage = "The username cannot be more than 255 characters!";
                return false;
            }
        }

        if (!isset($_POST['email']) && !empty(trim($_POST['email']))) {
            $errorMessage = "Please enter an email!";
            return false;
        } else {
            $email = trim($_POST['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = "The email address is not valid!";
                return false;
            }
        }

        
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
                <label for='username'>Username:</label>
                <input type='text' name='username' value="<?php echo $username; ?>">
                <label for='email'>Email:</label>
                <input type='text' name='email' value="<?php echo $email; ?>">
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
         echo "<div class='error-wrapper'><div class='error'>$errorMessage</div></div>";
    }
?>
</body>
</html>
