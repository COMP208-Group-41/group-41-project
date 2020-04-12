<?php

    /* Ensure that the php code does not pull the existing info from the
     * database before submitting new values if the user has clicked the submit
     * button!!!
     */

    /* As the user can change some fields and not others, I am going to use
     * beginTransaction and if anything fails then all transactions will be
     * rolled Back and no changes will be made
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
    $name = $email = $external = $newName = $newPassword = $password = "";
    $passwordError = $emailError = $nameError = $linkError = "";

    /* The user has clicked the Save button, form submitted, check password is
     * correct, then save changes
     */
    try {
        if (!empty($_POST) && isset($_POST['submit'])) {
            if (isset($_POST['password']) && !empty($_POST['password'])) {
                // First check if the original password is correct
                $password = $_POST['password'];
                if (verifyVenuePassword($venueUserID,$password,$pdo)) {
                    // If the password given is correct then check other fields
                    performChecks($venueUserID,$email,$pdo,$emailError,$passwordError);
                } else {
                    // Password was not correct, show error message
                    $passwordError = "Password incorrect!";
                }
            } else {
                /* The password field is empty, show error message and don't save
                 * any changes!
                 */
                 $passwordError = "You must enter your password to make any changes!";
            }
        } else {
            $result = getVenueUserInfo($venueUserID,$pdo);
            $name = $result['VenueUserName'];
            $email = $result['VenueUserEmail'];
            $external = $result['VenueUserExternal'];
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

    function getVenueUserInfo($venueUserID, $pdo) {
        $infoStmt = $pdo->prepare("SELECT VenueUserEmail,VenueUserName,VenueUserExternal FROM VenueUser WHERE VenueUserID=:VenueUserID");
        $infoStmt->bindValue(":VenueUserID",$venueUserID);
        $infoStmt->execute();
        return $infoStmt->fetch();
    }

    function performChecks($venueUserID,$email,$pdo,&$emailError,&$passwordError) {
        if (!emailCheck($venueUserID,$email,$pdo,$emailError)) {
            // email check did not execute correctly, return false
            $pdo->rollBack();
            return false;
        }

        if (!newPasswordCheck($venueUserID,$pdo,$passwordError)) {
            $pdo->rollBack();
            return false;
        }

    }

    function emailCheck($venueUserID,$email,$pdo,&$emailError) {
        /* Check email, if the email stays the same then no error
         * should be shown, but if it is different then need to
         * ensure it is not the same as another account's email
         */
        if (isset($_POST['email']) && !empty($_POST['email']) && (trim($_POST['email']) != $email)) {
            $newEmail = trim($_POST['email']);
            if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                // Valid email
                if (!checkVenueEmailExists($newEmail,$pdo)) {
                    // Email does not exist for another account
                    if (!changeEmail($newEmail,$venueUserID,$pdo)) {
                        // Update unsuccessful!
                        $emailError = "Error trying to update your email!";
                        return false;
                    } else {
                        // Update successful
                        return true;
                    }
                } else {
                    // Email already exists for another account!
                    $emailError = "Email is already linked to another account!";
                    return false;
                }
            } else {
                // Invalid email
                $emailError = "That email is not valid!";
                return false;
            }

        } else {
            return true;
        }
    }

    function changeEmail($newEmail,$venueUserID,$pdo) {
        $changeEmailStmt = $pdo->prepare("UPDATE VenueUser SET VenueUserEmail=:VenueUserEmail WHERE VenueUserID=:VenueUserID");
        $changeEmailStmt->bindValue(":VenueUserEmail",$newEmail);
        $changeEmailStmt->bindValue(":VenueUserID",$venueUserID);
        /* Try to update record, if it updates correctly then return true,
         * otherwise return false
         */
        if ($changeEmailStmt->execute()) {
            return true;
        } else {
            return false;
        }
    }


    /* Check if the new passwords are the same as the existing password, if so
     * then return false, otherwise perform all validation checks and update
     * password if all is valid, returning true if update occurs successfully
     */
    function newPasswordCheck($venueUserID,$pdo,&$passwordError) {
        if (isset($_POST['newPassword']) && !empty($_POST['newPassword'])) {
            $newPassword = $_POST['newPassword'];
            $confirmNewPassword = $_POST['confirmNewPassword'];
            if ($newPassword != $confirmNewPassword) {
                // New passwords don't match!
                $passwordError = "New passwords do not match!";
                return false;
            }
            /* If the new password given matches the existing password then
             * return false
             */
            if (verifyVenuePassword($venueUserID,$newPassword,$pdo)) {
                // Password is the same! return false
                $passwordError = "Please use a new password!";
                return false;
            }

            // the passwords match, now validate password
            if (!validatePassword($newPassword)) {
                // Password not valid!
                $passwordError = "New password is not valid!";
                return false;
            }

            // new password is valid, try to update password
            $hashedPassword = passwordHasher($newPassword);
            if (changePassword($hashedPassword,$venueUserID,$pdo)) {
                // If password updated successfully then return true
                return true;
            } else {
                $passwordError = "Error trying to update your password!";
                return false;
            }

        } else {
            // newPassword field is empty, no change to be made, return true
            return true;
        }

    }

    function changePassword($hashedPassword,$venueUserID,$pdo) {
        $changePasswordStmt = $pdo->prepare("UPDATE VenueUser SET VenueUserPass=:VenueUserPass WHERE VenueUserID=:VenueUserID");
        $changePasswordStmt->bindValue(":VenueUserPass",$hashedPassword);
        $changePasswordStmt->bindValue(":VenueUserID",$venueUserID);
        /* Try to update record, if it updates correctly then return true,
         * otherwise return false
         */
        if ($changePasswordStmt->execute()) {
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
