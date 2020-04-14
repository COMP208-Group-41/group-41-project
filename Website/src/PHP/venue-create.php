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


        // Need to do tags


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

    function getTags() {
        require "config.php";
        $getTagStmt = $pdo->query("SELECT * FROM Tag");
        foreach ($moduleStmt as $row) {
            echo "<option value='",$row['TagID'],"'>",$row['TagName'],"</option>";
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

            <textarea id='times' name='times' form='CreateVenue' placeholder="Venue Opening and Closing Times"></textarea><br>

            <input type='text' name='venueLocation' placeholder="Venue Address"><br>
            <textarea id='description' name ='description' form='CreateVenue' placeholder="Venue Description"></textarea><br>

            <input type='file' id="venueImage" name='venueImage' class='input-file' accept=".jpg">
            <label for="venueImage">Add Venue Image</label><br>
            <select name='tag1' id='tag1'>
                <?php getTags(); ?>
            </select>
            <select name='tag2' id='tag2'>
                <?php getTags(); ?>
            </select>
            <select name='tag3' id='tag3'>
                <?php getTags(); ?>
            </select>
            <select name='tag4' id='tag4'>
                <?php getTags(); ?>
            </select>
            <select name='tag5' id='tag5'>
                <?php getTags(); ?>
            </select><br>
            <input type='password' name='password' placeholder="Current Password"><br>

            <input type='submit' name='submit' value='Add Venue'>
        </div>
    </form>
</div>
</body>
</html>
