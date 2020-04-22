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


    function performChecks($userID, &$errorMessage,$pdo) {
        if (isset($_POST['password']) && !empty($_POST['password'])) {
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
            if (!validateUserName($username)) {
                $errorMessage = "The username must be more than 6 characters and less than 20!";
                return false;
            }
        }

        if (!(isset($_POST['email']) && !empty(trim($_POST['email'])))) {
            $errorMessage = "Please enter an email!";
            return false;
        } else {
            $email = trim($_POST['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = "The email address is not valid!";
                return false;
            }
        }

        if (checkEmailExistsOmit($email,$userID,$pdo)) {
            $errorMessage = "That Email is associated with another account!";
            return false;
        }

        if (checkUsernameExistsOmit($username,$userID,$pdo)) {
            $errorMessage = "That Username is taken!";
            return false;
        }

        if (!(isset($_POST['dob']) && !empty(trim($_POST['dob'])))) {
            $errorMessage = "Please enter your Date of Birth, in the format dd-mm-yyyy";
            return false;
        }

        if (!checkValidAge($_POST['dob'])) {
            $errorMessage = "Either your age is under 18 or the format of the date of birth was wrong, please match the format dd-mm-yyyy!";
            return false;
        }
        $dob = $_POST['dob'];

        // Try inserting Tags
        unset($tags);
        $tags = checkTags($errorMessage);
        if ($tags === false) {
            return false;
        }

        $pdo->beginTransaction();

        if (isset($_POST['newPassword']) && !empty($_POST['newPassword'])) {
            $newPassword = $_POST['newPassword'];
            $confirmNewPassword = $_POST['confirmNewPassword'];
            if ($newPassword != $confirmNewPassword) {
                $errorMessage = "New passwords do not match!";
                return false;
            }
            if (!validatePassword($newPassword)) {
                $errorMessage = "New password must contain at least 1 number, 1 lowercase letter and be at least 8 characters long!";
                return false;
            }
            $hashedPassword = passwordHasher($newPassword);
            if (!updateUserPass($userID,$hashedPassword,$pdo)) {
                $errorMessage = "Error in updating password!";
                $pdo->rollBack();
                return false;
            }
        }

        if (!updateUser($userID,$username,$email,$dob,$pdo)) {
            $errorMessage = "Error in updating your details!";
            $pdo-rollBack();
            return false;
        }


        if (!sizeof($tags) == 0) {
            if (!deleteUserPreferences($userID,$pdo)) {
                $errorMessage = "Error in deleting existing preferences!";
                $pdo->rollBack();
                return false;
            }
            foreach ($tags as $tag) {
                if (!insertUserPreferences($tag,$userID,$pdo)) {
                    $errorMessage = "Error in inserting new User Preferences!";
                    $pdo->rollBack();
                    return false;
                }
            }
        }


        // Everything completed successfully, commit and return true
        $pdo->commit();
        return true;
    }

    function insertUserPreferences($tag,$userID,$pdo) {
        $insertUserPreferencesStmt = $pdo->prepare("INSERT INTO UserPreferences (UserID,TagID) VALUES (:UserID,:TagID)");
        $insertUserPreferencesStmt->bindValue(":UserID",$userID);
        $insertUserPreferencesStmt->bindValue(":TagID",$tag);
        if ($insertUserPreferencesStmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    function deleteUserPreferences($userID,$pdo) {
        $deleteTagsStmt = $pdo->prepare("DELETE FROM UserPreferences WHERE UserID=:UserID");
        $deleteTagsStmt->bindValue(":UserID",$userID);
        if ($deleteTagsStmt->execute()) {
            // Tags deleted successfully!
            return true;
        } else {
            // Tags not deleted successfully!
            return false;
        }
    }

    function updateUser($userID,$username,$email,$dob,$pdo) {
        $updateUserStmt = $pdo->prepare("UPDATE User SET UserName=:UserName, UserEmail=:UserEmail, UserDOB=:UserDOB WHERE UserID=:UserID");
        $updateUserStmt->bindValue(":UserName",$username);
        $updateUserStmt->bindValue(":UserEmail",$email);
        $updateUserStmt->bindValue(":UserDOB",$dob);
        $updateUserStmt->bindValue(":UserID",$userID);
        if ($updateUserStmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    function updateUserPass($userID,$hashedPassword,$pdo) {
        $updateUserPassStmt = $pdo->prepare("UPDATE User SET UserPass=:UserPass WHERE UserID=:UserID");
        $updateUserPassStmt->bindValue(":UserPass",$hashedPassword);
        $updateUserPassStmt->bindValue(":UserID",$userID);
        if ($updateUserPassStmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    function checkEmailExistsOmit($email,$userID,$pdo) {
        $checkExistingStmt = $pdo->prepare("SELECT UserEmail FROM User WHERE UserEmail=:UserEmail AND UserID<>:UserID");
        $checkExistingStmt->bindValue(':UserEmail',$email);
        $checkExistingStmt->bindValue(':UserID',$userID);
        $checkExistingStmt->execute();
        if ($checkExistingStmt->rowCount() > 0) {
            // Email exists, return true
            return true;
        } else {
            return false;
        }
    }

    function checkUsernameExistsOmit($username,$userID,$pdo) {
        $checkExistingStmt = $pdo->prepare("SELECT UserName FROM User WHERE UserName=:UserName AND UserID<>:UserID");
        $checkExistingStmt->bindValue(':UserName',$username);
        $checkExistingStmt->bindValue(':UserID',$userID);
        $checkExistingStmt->execute();
        if ($checkExistingStmt->rowCount() > 0) {
            // Username exists, return true
            return true;
        } else {
            return false;
        }
    }
?>

<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Edit Venue User Account</title>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include "navbar.php" ?>
<div class="wrapper">
    <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='message-wrapper'><div class='success'>".$_SESSION['message']."</div></div>";
            unset($_SESSION['message']);
        }
    ?>
    <div class="container" style="width: 40vw">
        <h1 class="title">Account Settings</h1>
        <form name='EditVenueUserDetails' method='post' style="margin-top: 10px">
            <div class="edit-fields">
                <label for='username'>Username:</label>
                <input type='text' name='username' value="<?php echo $username; ?>" required>
                <label for='email'>Email:</label>
                <input type='text' name='email' value="<?php echo $email; ?>" required>
                <label for='newPassword'>New password:</label>
                <input type='password' name='newPassword'>
                <label for='confirmNewPassword'>Confirm new password:</label>
                <input type='password' name='confirmNewPassword'>
                <label for='dob'>Date of birth:</label>
                <input type='date' name='dob' value="<?php echo $dob; ?>" required>

                <!-- Script here, if no tags present dont display any of the tag stuff -->
                <label style="text-align: center; margin-top: 16px;"><b>Current Tags:</b></label>
                <div style="display: flex; justify-content: center; ">
                    <div class="tag-container" style="text-align: center">
                        <?php getTags($userPrefs,$pdo); ?>
                    </div>
                </div>

                <label for='tag1'>Add some tags that you look for in a night out, these are used for recommending you places! - this will overwrite old tags</label>
                <select name='tag1' id='tag1' onmousedown="if(this.options.length>5){this.size=4;}" onchange="this.size=0;" onblur="this.size=0;">
                    <option value='Optional'>No Tag</option>
                    <?php echoTags($pdo); ?>
                </select>
                <select name='tag2' id='tag2' onmousedown="if(this.options.length>5){this.size=4;}" onchange="this.size=0;" onblur="this.size=0;">
                    <option value='Optional'>No Tag</option>
                    <?php echoTags($pdo); ?>
                </select>
                <select name='tag3' id='tag3' onmousedown="if(this.options.length>5){this.size=4;}" onchange="this.size=0;" onblur="this.size=0;">
                    <option value='Optional'>No Tag</option>
                    <?php echoTags($pdo); ?>
                </select>
                <select name='tag4' id='tag4' onmousedown="if(this.options.length>5){this.size=4;}" onchange="this.size=0;" onblur="this.size=0;">
                    <option value='Optional'>No Tag</option>

                    <?php echoTags($pdo); ?>
                </select>
                <select name='tag5' id='tag5' onmousedown="if(this.options.length>5){this.size=4;}" onchange="this.size=0;" onblur="this.size=0;" style="margin-bottom: 16px">
                    <option value='Optional'>No Tag</option>
                    <?php echoTags($pdo); ?>
                </select>

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
         echo "<div class='message-wrapper'><div class='error'>$errorMessage</div></div>";
    }
?>
</body>
</html>
