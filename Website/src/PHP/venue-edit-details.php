<?php

/* Need to edit this to not INSERT but to UPDATE VALUES!!! */

    session_start();

    $_SESSION["loggedin"] = true;
    $_SESSION["VenueUserID"] = 2;

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

    // Handle the GET variable for venueID to display the correct venue
    $venueID = $_GET['venueID'];
    // Now check that the user accessing this venue is allowed to
    if (!checkVenueUserAllowed($venueID,$venueUserID,$pdo)) {
        // User is not allowed to edit!
        header("location: venue-home.php");
    }

    // First get all existing values and populate fields
    $result = getVenueInfo($venueID,$pdo);
    $name = $result['VenueName'];
    $description = $result['VenueDescription'];
    $address = $result['VenueAddress'];
    $times = $result['VenueTimes'];

    try {
        if (!empty($_POST) && isset($_POST['submit'])) {
            /* User has submitted the creation form, check that the password is
             * correct, if so then continue with creation
             */
             if (checkInputs($venueUserID,$venueID,$errorMessage,$pdo)) {
                 $errorMessage = "Venue Edited Successfully!";
             }
        }

    } catch (PDOException $e) {
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

    function checkInputs($venueUserID,$venueID,&$errorMessage,$pdo) {

        if (!(isset($_POST['password']) && !empty($_POST['password']))) {
            $errorMessage = "Please enter your password to add a venue";
            return false;
        }

        $password = $_POST['password'];
        if (!verifyVenuePassword($venueUserID,$password,$pdo)) {
            $errorMessage = "Password Incorrect!";
            return false;
        }

        if (!(isset($_POST['venueName']) && !empty(trim($_POST['venueName'])))) {
            $errorMessage = "Please enter a name for the venue!";
            return false;
        } else {
            $name = trim($_POST['venueName']);
            if (!validateVenueName($name)) {
                $errorMessage = "The name cannot be more than 255 characters!";
                return false;
            }
        }

        // Check times text
        if (!(isset($_POST['times']) && !empty(trim($_POST['times'])))) {
            $errorMessage = $_POST['times'];
            return false;
        } else {
            $times = trim($_POST['times']);
            if (!validateTimes($times)) {
                $errorMessage = "The information about times cannot be more than 500 characters!";
                return false;
            }
        }

        // Check address

        if (!(isset($_POST['venueLocation']) && !empty(trim($_POST['venueLocation'])))) {
            $errorMessage = "Please enter the address of the venue";
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
        $tags = checkTags($errorMessage);
        if ($tags === false) {
            return false;
        }

        // Check images, if valid then try to add everything to database
        if (!checkImage($venueUserID,$errorMessage)) {
            return false;
        }

        $pdo->beginTransaction();

        if (!updateVenue($venueUserID,$name,$description,$address,$times,$pdo,$errorMessage)) {
            $errorMessage = "Error in inserting new venue!";
            $pdo-rollBack();
            return false;
        } else {

            foreach ($tags as $tag) {
                if (!insertTags($tag,$venueID,$pdo)) {
                    $errorMessage = "Error in inserting tags!";
                    $pdo->rollBack();
                    return false;
                }
            }

            // Try uploading image
            $venueID = getVenueID($venueUserID,$name,$address,$pdo);
            if (!uploadImage($venueUserID,$venueID,$pdo)) {
                $errorMessage = "Error in uploading image!";
                return false;
            } else {
                // Try inserting tags

            }
        }
        // Everything completed successfully! return true
        return true;
    }

    /* If the description is longer than 1000 characters then it is not valid */
    function validateDescription($description) {
        if (strlen($description) <= 1000) {
            return true;
        } else {
            return false;
        }
    }

    function validateTimes($times) {
        if (strlen($times) <= 500) {
            return true;
        } else {
            return false;
        }
    }

    function checkTags(&$errorMessage) {
        if (!(isset($_POST['tag1']) && $_POST['tag1'] != 'None')) {
            // First tag not selected
            $errorMessage = "You must select the first Tag!";
            return false;
        } else {
            $tags[0] = $_POST['tag1'];
        }

        if (isset($_POST['tag2']) && $_POST['tag2'] != 'Optional') {
            if (in_array($_POST['tag2'],$tags)) {
                // Cannot have 2 of the same tag!
                $errorMessage = "You cannot have more than one of each tag!";
                return false;
            } else {
                $tags[1] = $_POST['tag2'];
            }
        }

        if (isset($_POST['tag3']) && $_POST['tag3'] != 'Optional') {
            if (in_array($_POST['tag3'],$tags)) {
                // Cannot have 2 of the same tag!
                $errorMessage = "You cannot have more than one of each tag!";
                return false;
            } else {
                $tags[2] = $_POST['tag3'];
            }
        }

        if (isset($_POST['tag4']) && $_POST['tag4'] != 'Optional') {
            if (in_array($_POST['tag4'],$tags)) {
                // Cannot have 2 of the same tag!
                $errorMessage = "You cannot have more than one of each tag!";
                return false;
            } else {
                $tags[3] = $_POST['tag4'];
            }
        }

        if (isset($_POST['tag5']) && $_POST['tag5'] != 'Optional') {
            if (in_array($_POST['tag5'],$tags)) {
                // Cannot have 2 of the same tag!
                $errorMessage = "You cannot have more than one of each tag!";
                return false;
            } else {
                $tags[4] = $_POST['tag5'];
            }
        }

        return $tags;
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

    function uploadImage($venueUserID,$venueID,$pdo) {
        $directory = "/home/sgstribe/private_upload/Venue/$venueUserID/$venueID/venue.jpg";
        if (move_uploaded_file($_FILES['venueImage']['tmp_name'],$directory)) {
            return true;
        } else {
            // Error in file upload!
            return false;
        }
    }

    function echoTags($pdo) {
        $tags = $pdo->query("SELECT * FROM Tag ORDER BY TagName");
        foreach ($tags as $row) {
            echo "<option value='".$row['TagID']."'>".$row['TagName']."</option>";
        }
    }


    function updateVenue($venueUserID,$name,$description,$address,$times,$pdo) {
        $createVenueStmt = $pdo->prepare("INSERT INTO Venue (VenueUserID,VenueName,VenueDescription,VenueAddress,VenueTimes) VALUES (:VenueUserID,:VenueName,:VenueDescription,:VenueAddress,:VenueTimes)");
        $createVenueStmt->bindValue(":VenueUserID",$venueUserID);
        $createVenueStmt->bindValue(":VenueName",$name);
        $createVenueStmt->bindValue(":VenueDescription",$description);
        $createVenueStmt->bindValue(":VenueAddress",$address);
        $createVenueStmt->bindValue(":VenueTimes",$times);
        if (!$createVenueStmt->execute()) {
            // Error in insertion
            return false;
        } else {
            // Values inserted correctly
            return true;
        }
    }

    function insertTags($tag,$venueID,$pdo) {

        $insertTagsStmt = $pdo->prepare("INSERT INTO VenueTag (VenueID,TagID) VALUES (:VenueID,:TagID)");
        $insertTagsStmt->bindValue(":VenueID",$venueID);
        $insertTagsStmt->bindValue(":TagID",$tag);
        if ($insertTagsStmt->execute()) {

            return true;
        } else {

            return false;
        }
    }

    function checkVenueUserAllowed($venueID,$venueUserID,$pdo) {
        $venueUserIDStmt = $pdo->prepare("SELECT VenueUserID FROM Venue WHERE VenueID=:VenueID");
        $venueUserIDStmt->bindValue(":VenueID",$venueID);
        $venueUserIDStmt->execute();
        $row = $venueUserIDStmt->fetch();
        if ($row['VenueUserID'] != $venueUserID) {
            // User is not allowed to edit this!
            return false;
        } else {
            return true;
        }
    }

    function getVenueInfo($venueID,$pdo) {
        $getVenueStmt = $pdo->prepare("SELECT VenueName,VenueDescription,VenueAddress,VenueTimes FROM Venue WHERE VenueID=:VenueID");
        $getVenueStmt->bindValue(":VenueID",$venueID);
        $getVenueStmt->execute();
        return $getVenueStmt->fetch();
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
    <form id='CreateVenue' name='CreateVenue' method='post' style="margin-top: 10px" enctype="multipart/form-data">
        <div class="edit-fields">

            <input type='text' name='venueName' placeholder="Venue Name" value="<?php echo $name; ?>"><br>

            <textarea id='times' name='times' form='CreateVenue' placeholder="Venue Opening and Closing Times"><?php echo $times; ?></textarea><br>

            <input type='text' name='venueLocation' placeholder="Venue Address" value="<?php echo $address; ?>"><br>

            <textarea id='description' name ='description' form='CreateVenue' placeholder="Venue Description"><?php echo $description; ?></textarea><br>

            <input type='file' id="venueImage" name='venueImage' class='input-file' accept=".jpg">
            <label for="venueImage">Add Venue Image</label><br>
            <label for='tag1'>Add Tags for your venue, the first is required, the rest are optional</label><br>
            <select name='tag1' id='tag1'>
                <option value='None'>Select a Tag</option>
                <?php echoTags($pdo); ?>
            </select>
            <select name='tag2' id='tag2'>
                <option value='Optional'>No Tag</option>
                <?php echoTags($pdo); ?>
            </select>
            <select name='tag3' id='tag3'>
                <option value='Optional'>No Tag</option>
                <?php echoTags($pdo); ?>
            </select>
            <select name='tag4' id='tag4'>
                <option value='Optional'>No Tag</option>
                <?php echoTags($pdo); ?>
            </select>
            <select name='tag5' id='tag5'>
                <option value='Optional'>No Tag</option>
                <?php echoTags($pdo); ?>
            </select><br>
            <input type='password' name='password' placeholder="Current Password"><br>

            <input type='submit' name='submit' value='Add Venue'>
        </div>
    </form>
</div>
<?php
    if ($errorMessage != "") {
        echo "<div class='error'>$errorMessage</div>";
    }
?>
</body>
</html>
