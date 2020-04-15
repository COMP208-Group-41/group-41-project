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

    // Current tags for this event are pulled here
    $currentTagIDs = getTagID($venueID,$pdo);

    try {
        if (!empty($_POST) && isset($_POST['submit'])) {
            /* User has submitted the creation form, check that the password is
             * correct, if so then continue with creation
             */
             if (checkInputs($venueUserID,$venueID,$errorMessage,$pdo)) {
                 $errorMessage = "Venue Edited Successfully!";
                 // Refresh details!

                 $result = getVenueInfo($venueID,$pdo);
                 $name = $result['VenueName'];
                 $description = $result['VenueDescription'];
                 $address = $result['VenueAddress'];
                 $times = $result['VenueTimes'];

                 $currentTagIDs = getTagID($venueID,$pdo);
             }
        }

    } catch (PDOException $e) {
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

    function checkInputs($venueUserID,$venueID,&$errorMessage,$pdo) {

        if (!(isset($_POST['password']) && !empty($_POST['password']))) {
            $errorMessage = "Please enter your password to edit the venue";
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
            $errorMessage = "Please enter information about your opening and closing times!";
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

        //Check existing venues
        if (checkExistingVenue($name,$address,$venueID,$pdo)) {
            // A venue already exists with the same name and address!
            $errorMessage = "A venue already exists with the same name and address!";
            return false;
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
        /* Unsetting the tags array so the values are definitely
         * up to date
         */
        unset($tags);
        $tags = checkTags($errorMessage);
        if ($tags === false) {
            return false;
        }

        // Check images, if valid then try to add everything to database
        if (!empty($_FILES['venueImage']['name'])) {
            if (!checkImage($venueUserID,$errorMessage)) {
                return false;
            }
        }


        $pdo->beginTransaction();

        if (!updateVenue($venueUserID,$venueID,$name,$description,$address,$times,$pdo,$errorMessage)) {
            $errorMessage = "Error in inserting new venue!";
            $pdo-rollBack();
            return false;
        }

        // If all tags are set to no tags then don't delete existing!
        if (!sizeof($tags) == 0) {
            if (!deleteTags($venueID,$pdo)) {
                $errorMessage = "Error in deleting existing tags!";
                $pdo-rollBack();
                return false;
            }
            foreach ($tags as $tag) {
                if (!insertTags($tag,$venueID,$pdo)) {
                    $errorMessage = "Error in inserting tags!";
                    $pdo->rollBack();
                    return false;
                }
            }
        }
        // Try uploading image
        if (!empty($_FILES['venueImage']['name'])) {
            if (!uploadImage($venueUserID,$venueID,$pdo)) {
                $errorMessage = "Error in uploading image!";
                $pdo->rollBack();
                return false;
            }
        }

        // Everything completed successfully! return true
        $pdo->commit();
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

    /* CheckTags returns an array of the user-selected tags if they are entered
     * Correctly. If they are not then false is returned
     */
    function checkTags(&$errorMessage) {
        unset($tags);
        $tags = [];
        if ((isset($_POST['tag1']) && $_POST['tag1'] != 'Optional')) {
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
        // Remove any existing file first
        $directory = "/home/sgstribe/private_upload/Venue/$venueUserID/$venueID/venue.jpg";
        if (file_exists($directory)) {
            chmod($directory,0755);
            unlink($directory);
        }
        if (move_uploaded_file($_FILES['venueImage']['tmp_name'],$directory)) {
            return true;
        } else {
            // Error in file upload!
            return false;
        }
    }

    /* Get the existing tag Names from the Tag table, this relies on the
     * getTagID function being called at the top of the code
     */
    function getTags($tagIDs,$pdo) {
        if (sizeof($tagIDs) > 0) {
            foreach ($tagIDs as $tagID) {
                $getTagNameStmt = $pdo->prepare("SELECT TagName FROM Tag WHERE TagID=:TagID");
                $getTagNameStmt->bindValue(":TagID",$tagID['TagID']);
                $getTagNameStmt->execute();
                $tag = $getTagNameStmt->fetch();
                echo $tag['TagName'].", ";
            }
        } else {
            echo "No Tags for this Venue";
        }

    }

    function getTagID($venueID,$pdo) {
        $getVenueTagsStmt = $pdo->prepare("SELECT TagID FROM VenueTag WHERE VenueID=:VenueID");
        $getVenueTagsStmt->bindValue(":VenueID",$venueID);
        $getVenueTagsStmt->execute();
        return $getVenueTagsStmt->fetchAll();
    }

    function echoTags($pdo) {
        $tags = $pdo->query("SELECT * FROM Tag ORDER BY TagName");
        foreach ($tags as $row) {
            echo "<option value='".$row['TagID']."'>".$row['TagName']."</option>";
        }
    }

    function checkExistingVenue($name,$address,$venueID,$pdo) {
        $checkExistingStmt = $pdo->prepare("SELECT VenueID FROM Venue WHERE VenueName=:VenueName AND VenueAddress=:VenueAddress AND VenueID<>:VenueID");
        $checkExistingStmt->bindValue(":VenueName",$name);
        $checkExistingStmt->bindValue(":VenueAddress",$address);
        $checkExistingStmt->bindValue(":VenueID",$venueID);
        $checkExistingStmt->execute();
        if ($checkExistingStmt->rowCount() > 0) {
            // A venue exists with the same name and address!
            return true;
        } else {
            return false;
        }
    }

    function updateVenue($venueUserID,$venueID,$name,$description,$address,$times,$pdo) {
        $updateVenueStmt = $pdo->prepare("UPDATE Venue SET VenueUserID=:VenueUserID, VenueName=:VenueName, VenueDescription=:VenueDescription, VenueAddress=:VenueAddress, VenueTimes=:VenueTimes WHERE VenueID=:VenueID");
        $updateVenueStmt->bindValue(":VenueUserID",$venueUserID);
        $updateVenueStmt->bindValue(":VenueName",$name);
        $updateVenueStmt->bindValue(":VenueDescription",$description);
        $updateVenueStmt->bindValue(":VenueAddress",$address);
        $updateVenueStmt->bindValue(":VenueTimes",$times);
        $updateVenueStmt->bindValue(":VenueID",$venueID);
        if (!$updateVenueStmt->execute()) {
            // Error in update
            return false;
        } else {
            // Values updated correctly
            return true;
        }
    }

    // When inserting new tags, the existing ones are deleted
    function deleteTags($venueID,$pdo) {
        $deleteTagsStmt = $pdo->prepare("DELETE FROM VenueTag WHERE VenueID=:VenueID");
        $deleteTagsStmt->bindValue(":VenueID",$venueID);
        if ($deleteTagsStmt->execute()) {
            // Tags deleted successfully!
            return true;
        } else {
            // Tags not deleted successfully!
            return false;
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
    <title><?php echo $name; ?></title>
    <link rel="stylesheet" type="text/css" href="../css/venue-edit-details.css">
</head>
<body>
<div class="wrapper">
    <img src="../Assets/outout.svg" alt="OutOut">
    <form id='CreateVenue' name='CreateVenue' method='post' style="margin-top: 10px" enctype="multipart/form-data">
        <div class="edit-fields">
            <label for='venueName'>Venue Name:</label>
            <input type='text' id='venueName' name='venueName' placeholder="Venue Name" value="<?php echo $name; ?>"><br>
            <label for='times'>Time Information:</label>
            <textarea id='times' name='times' form='CreateVenue' placeholder="Venue Opening and Closing Times"><?php echo $times; ?></textarea><br>
            <label for='venueLocation'>Location Information:</label>
            <textarea id='venueLocation' name='venueLocation' form='CreateVenue' placeholder="Venue Address and Location details, no more than 255 characters"><?php echo $address; ?></textarea><br>
            <label for='description'>Venue Description:</label>
            <textarea id='description' name ='description' form='CreateVenue' placeholder="Venue Description"><?php echo $description; ?></textarea><br>

            <h2>Additional Information</h2>

            <input type='file' id="venueImage" name='venueImage' class='input-file' accept=".jpg">
            <label for="venueImage">Add Venue Image (must be .jpg and cannot be bigger than 2MB)</label><br>
            <p>Current Tags: <?php getTags($currentTagIDs,$pdo); ?></p>
            <label for='tag1'>Add Tags for your venue, these are optional but are used to recommend your venue to users. Any changes made below will overwrite any existing Tags, If you want to keep the existing Tags then leave the tag fields below empty</label><br>
            <select name='tag1' id='tag1'>
                <option value='Optional'>No Tag</option>
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
