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
        header("location: venue-user-login.php");
        exit;
        /* If the user is logged in but they are not a venue user then they are
         * redirected to home page
         */
    } else if (!isset($_SESSION["VenueUserID"])) {
        header("location: home.php");
        exit;
    }

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    // The config file is imported here for any database connections required later
    require_once "config.php";

    $venueUserID = $_SESSION["VenueUserID"];
    $name = $email = $external = $newName = $newPassword = $password = "";
    $errorMessage = "";

    $result = getVenueUserInfo($venueUserID,$pdo);
    $name = $result['VenueUserName'];
    $email = $result['VenueUserEmail'];
    $external = $result['VenueUserExternal'];

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
                    if (performChecks($venueUserID,$email,$name,$external,$pdo,$errorMessage)) {
                        // Changes done successfully, show confirmation message
                        $errorMessage = "Changes saved successfully!";
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

    /* perform checks for every field that can be edited, if changes are being
     * made then checks are performed and if updates are successful then the
     * transactions are comitted, otherwise they are rolled back and the
     * appropriate error message is shown
     */
    function performChecks($venueUserID,$email,$name,$external,$pdo,&$errorMessage) {
        $pdo->beginTransaction();
        if (!emailCheck($venueUserID,$email,$pdo,$errorMessage)) {
            // email check did not execute correctly, return false
            $pdo->rollBack();
            return false;
        }

        if (!newPasswordCheck($venueUserID,$pdo,$errorMessage)) {
            $pdo->rollBack();
            return false;
        }

        if (!nameCheck($venueUserID,$name,$pdo,$errorMessage)) {
            $pdo->rollBack();
            return false;
        }

        if (!externalCheck($venueUserID,$external,$pdo,$errorMessage)) {
            $pdo->rollBack();
            return false;
        }

        // If all checks are complete then try to commit Transactions
        if ($pdo->commit()) {
            /* All changes committed successfully, reset error message and
             * return true
             */
            $errorMessage = "";
            return true;
        } else {
            // Error in commits!
            $errorMessage = "Error in committing changes!";
            return false;
        }
    }

    /* Check email, if the email stays the same then no error
     * should be shown, but if it is different then need to
     * ensure it is not the same as another account's email
     */
    function emailCheck($venueUserID,$email,$pdo,&$errorMessage) {
        if (isset($_POST['email']) && !empty($_POST['email']) && (trim($_POST['email']) != $email)) {
            $newEmail = trim($_POST['email']);
            if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                // Valid email
                if (!checkVenueEmailExists($newEmail,$pdo)) {
                    // Email does not exist for another account
                    if (!changeEmail($newEmail,$venueUserID,$pdo)) {
                        // Update unsuccessful!
                        $errorMessage = "Error trying to update your email!";
                        return false;
                    } else {
                        // Update successful
                        return true;
                    }
                } else {
                    // Email already exists for another account!
                    $errorMessage = "Email is already linked to another account!";
                    return false;
                }
            } else {
                // Invalid email
                $errorMessage = "That email is not valid!";
                return false;
            }

        } else {
            return true;
        }
    }
    /* The email is updated in the database, if successful then true is
     * returned, otherwise false is returned
     */
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
    function newPasswordCheck($venueUserID,$pdo,&$errorMessage) {
        if (isset($_POST['newPassword']) && !empty($_POST['newPassword'])) {
            $newPassword = $_POST['newPassword'];
            $confirmNewPassword = $_POST['confirmNewPassword'];
            if ($newPassword != $confirmNewPassword) {
                // New passwords don't match!
                $errorMessage = "New passwords do not match!";
                return false;
            }
            /* If the new password given matches the existing password then
             * return false
             */
            if (verifyVenuePassword($venueUserID,$newPassword,$pdo)) {
                // Password is the same! return false
                $errorMessage = "Please use a new password!";
                return false;
            }

            // the passwords match, now validate password
            if (!validatePassword($newPassword)) {
                // Password not valid!
                $errorMessage = "New password is not valid!";
                return false;
            }

            // new password is valid, try to update password
            $hashedPassword = passwordHasher($newPassword);
            if (changePassword($hashedPassword,$venueUserID,$pdo)) {
                // If password updated successfully then return true
                return true;
            } else {
                $errorMessage = "Error trying to update your password!";
                return false;
            }

        } else {
            // newPassword field is empty, no change to be made, return true
            return true;
        }

    }

    /* The password is updated in the database, if successful then true is
     * returned, otherwise false is returned
     */
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

    /* Check if the name has been changed in the input field, if it has then
     * validate it and then update in the database
     */
    function nameCheck($venueUserID,$name,$pdo,&$errorMessage) {
        if (isset($_POST['companyName']) && !empty($_POST['companyName']) && trim($_POST['companyName']) != $name) {
            $newName = trim($_POST['companyName']);

            // If the new name given is not valid then return false
            if (!validate255($newName)) {
                $errorMessage = "Name of company cannot be more than 255 characters!";
                return false;
            }

            // Try to update name
            if (changeName($newName,$venueUserID,$pdo)) {
                // Update successful
                return true;
            } else {
                $errorMessage = "Error trying to update company name!";
                return false;
            }

        } else {
            return true;
        }
    }

    /* The name is updated in the database, if successful then true is
     * returned, otherwise false is returned
     */
    function changeName($newName,$venueUserID,$pdo) {
        $changeNameStmt = $pdo->prepare("UPDATE VenueUser SET VenueUserName=:VenueUserName WHERE VenueUserID=:VenueUserID");
        $changeNameStmt->bindValue(":VenueUserName",$newName);
        $changeNameStmt->bindValue(":VenueUserID",$venueUserID);
        /* Try to update record, if it updates correctly then return true,
         * otherwise return false
         */
        if ($changeNameStmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /* Check if the external link has been changed, if so then validate and
     * update in database, if all successful then return true, otherwise return
     * false and show error message
     */
    function externalCheck($venueUserID,$external,$pdo,&$errorMessage) {
        if (isset($_POST['external']) && !empty(trim($_POST['external'])) && trim($_POST['external']) != $external) {
            $newExternal = trim($_POST['external']);
            if (!filter_var($newExternal, FILTER_VALIDATE_URL)) {
                // The URL given is not valid!
                $errorMessage = "The URL given is not valid!";
                return false;
            }

            if (changeExternal($newExternal,$venueUserID,$pdo)) {
                return true;
            } else {
                $errorMessage = "Error in trying to update external URL!";
                return false;
            }
        } else {
            return true;
        }
    }

    /* The external link is updated in the database, if successful then true is
     * returned, otherwise false is returned
     */
    function changeExternal($newExternal,$venueUserID,$pdo) {
        $changeEmxternalStmt = $pdo->prepare("UPDATE VenueUser SET VenueUserExternal=:VenueUserExternal WHERE VenueUserID=:VenueUserID");
        $changeEmxternalStmt->bindValue(":VenueUserExternal",$newExternal);
        $changeEmxternalStmt->bindValue(":VenueUserID",$venueUserID);
        /* Try to update record, if it updates correctly then return true,
         * otherwise return false
         */
        if ($changeEmxternalStmt->execute()) {
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
                <label>New password:</label>
                <input type='password' name='newPassword'>
                <label>Confirm new password:</label>
                <input type='password' name='confirmNewPassword'>
                <label>Company name:</label>
                <input type='text' name='companyName' value="<?php echo $name; ?>">
                <label>Website link:</label>
                <input type='text' name='external' value="<?php echo $external; ?>">
                <div class="seperator">
                    <label>Enter current password to allow changes:</label>
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
