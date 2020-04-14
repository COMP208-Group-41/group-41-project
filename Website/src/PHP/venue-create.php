<?php

    session_start();

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

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    $venueUserID = $_SESSION["VenueUserID"];
    $errorMessage = "";

    try {
        if (!empty($_POST) && isset($_POST['submit'])) {
            /* User has submitted the creation form, check that the password is
             * correct, if so then continue with creation
             */

        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

    function checkInputs($venueUserID,&$errorMessage,$pdo) {

        if (!(isset($_POST['password']) && !empty($_POST['password']))) {
            $errorMessage = "Please enter your password to add a venue";
            return false;
        }

        $password = $_POST['password'];
        if (!verifyVenuePassword($venueUserID,$password,$pdo)) {
            $errorMessage = "Password Incorrect!";
            return false;
        }

        if (!(isset($_POST['venueName']) ** !empty(trim($_POST['venueName'])))) {
            $errorMessage = "Please enter a name for the venue!";
            return false;
        } else {
            $name = trim($_POST['venueName']);
            // Password is entered correctly, check other fields
            if (!validateVenueName($name)) {
                $errorMessage = "The name cannot be more than 255 characters!";
                return false;
            }
        }




        // Check address

        if (!(isset($_POST['venueLocation']) && !empty(trim($_POST['venueLocation'])))) {
            $errorMessage = "Please enter the address of the venue!";
            return false;
        } else {
            $address = trim($_POST['venueLocation']);
            if (!validateVenueName($address)) {
                $errorMessage = "The address cannot be more than 255 characters!";
                return false;
            }
        }

        // Check Description
        if (!(isset($_POST['description']) && !empty(trim($_POST['description'])))) {
            $errorMessage = "Please enter a description for the venue!";
            return false;
        } else {
            $description = trim($_POST['description']);
            if (!validateDescription($description)) {
                $errorMessage = "The Description cannot be more than 1000 characters!";
                return false;
            }
        }

        // Make the image optional
        /* Will need to upload image after Venue is created so we have
         * the VenueID for folder creation
         */



        $pdo->beginTransaction();
        // Check times
        if (!checkTimes($pdo)) {
            $pdo->rollBack();
            $errorMessage = "Ensure that you have entered times correctly!";
            return false;
        }
    }

    /* If the description is longer than 1000 characters then it is not valid */
    function validateDescription($description) {
        if (strlen($description) <= 1000) {
            return true;
        } else {
            return false;
        }
    }

    function checkTimes($pdo) {
        if (isset($_POST['mondayCheck'])) {
            // Monday checkbox is checked, check times
            if (isset($_POST['timeStartMonday']) && !empty($_POST['timeStartMonday']) && isset($_POST['timeEndMonday']) && !empty($_POST['timeEndMonday'])) {
                /* Don't bother checking times, venue could close early hours of
                 * the next day so just specify closing times for the day that
                 * it opens
                 */

                 // Insert Monday times into table
            } else {
                // Times have not been entered properly, show error
                return false;
            }
        }

        if (isset($_POST['tuesdayCheck'])) {
            if (isset($_POST['timeStartTuesday']) && !empty($_POST['timeStartTuesday']) && isset($_POST['timeEndTuesday']) && !empty($_POST['timeEndTuesday'])) {
                /* Don't bother checking times, venue could close early hours of
                 * the next day so just specify closing times for the day that
                 * it opens
                 */

                 // Insert Tuesday times into table
            } else {
                // Times have not been entered properly, show error
                return false;
            }
        }

        if (isset($_POST['wednesdayCheck'])) {
            if (isset($_POST['timeStartWednesday']) && !empty($_POST['timeStartWednesday']) && isset($_POST['timeEndWednesday']) && !empty($_POST['timeEndWednesday'])) {
                /* Don't bother checking times, venue could close early hours of
                 * the next day so just specify closing times for the day that
                 * it opens
                 */

                 // Insert Monday times into table
            } else {
                // Times have not been entered properly, show error
                return false;
            }
        }

        if (isset($_POST['thursdayCheck'])) {
            if (isset($_POST['timeStartThursday']) && !empty($_POST['timeStartThursday']) && isset($_POST['timeEndThursday']) && !empty($_POST['timeEndThursday'])) {
                /* Don't bother checking times, venue could close early hours of
                 * the next day so just specify closing times for the day that
                 * it opens
                 */

                 // Insert Monday times into table
            } else {
                // Times have not been entered properly, show error
                return false;
            }
        }
        if (isset($_POST['fridayCheck'])) {
            if (isset($_POST['timeStartFriday']) && !empty($_POST['timeStartFriday']) && isset($_POST['timeEndFriday']) && !empty($_POST['timeEndFriday'])) {
                /* Don't bother checking times, venue could close early hours of
                 * the next day so just specify closing times for the day that
                 * it opens
                 */

                 // Insert Monday times into table
            } else {
                // Times have not been entered properly, show error
                return false;
            }
        }
        if (isset($_POST['saturdayCheck'])) {
            if (isset($_POST['timeStartSaturday']) && !empty($_POST['timeStartSaturday']) && isset($_POST['timeEndSaturday']) && !empty($_POST['timeEndSaturday'])) {
                /* Don't bother checking times, venue could close early hours of
                 * the next day so just specify closing times for the day that
                 * it opens
                 */

                 // Insert Monday times into table
            } else {
                // Times have not been entered properly, show error
                return false;
            }
        }
        if (isset($_POST['sundayCheck'])) {
            if (isset($_POST['timeStartSunday']) && !empty($_POST['timeStartSunday']) && isset($_POST['timeEndSunday']) && !empty($_POST['timeEndSunday'])) {
                /* Don't bother checking times, venue could close early hours of
                 * the next day so just specify closing times for the day that
                 * it opens
                 */

                 // Insert Monday times into table
            } else {
                // Times have not been entered properly, show error
                return false;
            }
        }
    }

    function checkImage($venueUserID,&$errorMessage) {
        if ($_FILES['venueImage']['size'] == 0) {
            $errorMessage = "No file selected or the selected file is too large!";
            return false;
        }

        if ($_FILES['venueImage']['error'] != 0) {
            $errorMessage = "Error in file upload";
            return false;
        }

        if ($_FILES['venueImage']['type'] != "image/jpeg") {
            $errorMessage = "File must be a jpeg!";
            return false;
        }

        return true;
    }

    function uploadImage($venueUserID,$venueName,$pdo) {
        $getVenueIDStmt = $pdo->prepare("SELECT VenueID FROM Venue WHERE VenueUserID=:VenueUserID AND VenueName=:VenueName");
        $getVenueIDStmt->bindValue(":VenueUserID",$venueUserID);
        $getVenueIDStmt->bindValue(":VenueName",$venueName);
        $getVenueIDStmt->execute();
        $row = $getVenueIDStmt->fetch();
        $venueID = $row['VenueID'];
        if (createVenueFolder($venueUserID,$venueID)) {
            // Folder created successfully, upload image
            $directory = "/home/sgstribe/private_upload/$venueUserID/$venueID/";
            if (move_uploaded_file($_FILES['venueImage']['tmp_name'],"/home/sgstribe/private_upload/venue.jpg")) {
                return true;
            } else {
                // Error in file upload!
                return false;
            }
        } else {
            // Folder not created successfully, error!
            return false;
        }
    }

    function createVenueFolder($venueUserID,$venueID) {
        $path = "/home/sgstribe/private_upload/$venueUserID/$venueID";
        if (mkdir($path,0755)) {
            // Folder created successfully
            return true;
        } else {
            // Error in folder creation!
            return false;
        }
    }
?>

<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Add A Venue</title>
    <link rel="stylesheet" type="text/css" href="../css/venue-edit-details.css">
</head>
<body>
<div class="wrapper">
    <img src="../Assets/outout.svg" alt="OutOut">
    <form name='CreateVenue' method='post' style="margin-top: 10px">
        <div class="edit-fields">

            <input type='text' name='venueName' placeholder="Venue Name"><br>

            <input type='checkbox' id='mondayCheck' name='mondayCheck' value='monday'>
            <label for='mondayCheck'>Monday: </label>
            <input type='time' name="timeStartMonday" id="timeStartMonday">
            <input type='time' name="timeEndMonday" id="timeEndMonday"><br>

            <input type='checkbox' id='tuesdayCheck' name='tuesdayCheck' value='tuesday'>
            <label for='tuesdayCheck'>Tuesday: </label>
            <input type='time' name="timeStartTuesday" id="timeStartTuesday">
            <input type='time' name="timeEndTuesday" id="timeEndTuesday"><br>

            <input type='checkbox' id='wednesdayCheck' name='wednesdayCheck' value='wednesday'>
            <label for='wednesdayCheck'>Wednesday: </label>
            <input type='time' name="timeStartWednesday" id="timeStartWednesday">
            <input type='time' name="timeEndWednesday" id="timeEndWednesday"><br>

            <input type='checkbox' id='thursdayCheck' name='thursdayCheck' value='thursday'>
            <label for='thursdayCheck'>Thursday: </label>
            <input type='time' name='timeStartThursday' id='timeStartThursday'>
            <input type='time' name='timeEndThursday' id='timeEndThursday'><br>

            <input type='checkbox' id='fridayCheck' name='fridayCheck' value='friday'>
            <label for='fridayCheck'>Friday: </label>
            <input type='time' name='timeStartFriday' id='timeStartFriday'>
            <input type='time' name='timeEndFriday' id='timeEndFriday'><br>

            <input type='checkbox' id='saturdayCheck' name='saturdayCheck' value='saturday'>
            <label for='saturdayCheck'>Saturday: </label>
            <input type='time' name='timeStartSaturday' id='timeStartSaturday'>
            <input type='time' name='timeEndSaturday' id='timeEndSaturday'><br>

            <input type='checkbox' id='sundayCheck' name='sundayCheck' value='sunday'>
            <label for='sundayCheck'>Sunday: </label>
            <input type='time' name='timeStartSunday' id='timeStartSunday'>
            <input type='time' name='timeEndSunday' id='timeEndSunday'><br>

            <input type='text' name='venueLocation' placeholder="Venue Address"><br>
            <textarea id='description' name ='description' form='CreateVenue' placeholder="Venue Description"></textarea><br>

            <input type='file' id="venueImage" name='venueImage' class='input-file' accept=".jpg">
            <label for="venueImage">Add Venue Image</label><br>
            <select name='tag1' id='tag1'>
                <!-- Include php code here to populate tag drop down -->
            </select>
            <select name='tag2' id='tag2'>
                <!-- Include php code here to populate tag drop down -->
            </select>
            <select name='tag3' id='tag3'>
                <!-- Include php code here to populate tag drop down -->
            </select>
            <select name='tag4' id='tag4'>
                <!-- Include php code here to populate tag drop down -->
            </select>
            <select name='tag5' id='tag5'>
                <!-- Include php code here to populate tag drop down -->
            </select><br>
            <input type='password' name='password' placeholder="Current Password"><br>

            <input type='submit' name='submit' value='Add Venue'>
        </div>
    </form>
</div>
</body>
</html>
