<?php

    // TODO: Implement function to convert mysql datetime to html datetime-local
    // TODO: Implement try catch for datetime-local input checking - Coded, needs testing

    session_start();

    $_SESSION["VenueUserID"] = 2;
    $_SESSION['loggedin'] = true;

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
    $eventID = $_GET['EventID'];
    $errorMessage = "";

    $eventToVenueUser = eventToVenueUser($eventID,$pdo);
    $eventToVenueUser = $eventToVenueUser['VenueUserID'];
    if($eventToVenueUser === false){
      $errorMessage = "Error getting VenueUserID";
    } elseif ($eventToVenueUser != $venueUserID) {
        header("location: venue-home.php");
        exit;
    }


    // Retrive existing values and populate fields
    $result = getEventInfo($eventID,$pdo);
    $venueID = $result['VenueID'];
    $name = $result['EventName'];
    $description = $result['EventDescription'];
    $startTime = $result['EventStartTime'];
    $endTime = $result['EventEndTime'];

    // Current tags for this event are pulled here
    $currentTagIDs = getEventTagID($eventID,$pdo);

    try {
        if (!empty($_POST) && isset($_POST['submit'])) {

             if (checkInputs($venueUserID,$eventID,$venueID,$errorMessage,$pdo)) {
                 $errorMessage = "Event Edited Successfully!";
                 // Refresh details!

                 $result = getEventInfo($eventID,$pdo);
                 $name = $result['EventName'];
                 $description = $result['EventDescription'];
                 $startTime = $result['EventStartTime'];
                 $endTime = $result['EventEndTime'];

                 $currentTagIDs = getEventTagID($venueID,$pdo);
             }
        }

    } catch (PDOException $e) {
        exit("PDO Error: ".$e->getMessage()."<br>");
    }


    function checkInputs($venueUserID,$EventID,$venueID,&$errorMessage,$pdo) {

        // Firstly check the user's password
        if (!(isset($_POST['password']) && !empty($_POST['password']))) {
            $errorMessage = "Please enter your password to add an event";
            return false;
        }

        //Check existing venues
        if (checkExistingEvent($name,$startTime,$endTime,$venueID,$pdo)) {
            // A event already exists with the same name and start times at the same venue!
            $errorMessage = "An event already exists at this venue with the same name and time!";
            return false;
        }

        if (!(isset($_POST['eventName']) && !empty(trim($_POST['eventName'])))) {
            $errorMessage = "Please enter a name for the event!";
            return false;
        } else {
            $name = trim($_POST['eventName']);
            if (!validate255($name)) { // This function can be reused for events. Name may need changing (config.php)
                $errorMessage = "The name cannot be more than 255 characters!";
                return false;
            }
        }

        try {
            // Check times given
            if (isset($_POST['startTime']) && !empty($_POST['startTime'])) {
                $phpStartDateTime = new DateTime($_POST['startTime']);
                if (new DateTime("now") > $phpStartDateTime) {
                    $errorMessage = "Event cannot be in the past!";
                    return false;
                }
            } else {
                $errorMessage = "You must give a start time!";
                return false;
            }
            $startTimestamp = strtotime($_POST['startTime']);
            $mysqlStartDateTime = date("Y-m-d H:i:s",$startTimestamp);

            if (isset($_POST['endTime']) && !empty($_POST['endTime'])) {
                $phpEndDateTime = new DateTime($_POST['endTime']);
                if (new DateTime("now") > $phpEndDateTime) {
                    $errorMessage = "Event cannot be in the past!";
                    return false;
                }
                if ($phpStartDateTime > $phpEndDateTime) {
                    $errorMessage = "end time cannot be before start time!";
                    return false;
                }
            } else {
                $errorMessage = "You must give an end time!";
                return false;
            }

            $endTimestamp = strtotime($_POST['endTime']);
            $mysqlEndDateTime = date("Y-m-d H:i:s",$endTimestamp);
        } catch (Exception $timeException) {
            $errorMessage = "Date and Time in the wrong format! Format (24 hour time) must be: dd-mm-yyyy hh:mm";
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

        // Tags
        unset($tags);
        $tags = checkTags($errorMessage);
        if ($tags === false) {
            return false;
        }

        // Check images, if valid then try to add everything to database

        if (!empty($_FILES['eventImage']['name'])) {
            if (!checkImage($errorMessage)) {
                return false;
            }
        }

        $pdo->beginTransaction();

        if (!updateEvent($eventID,$name,$description,$mysqlStartTime,$mysqlEndTime,$pdo)) {
            $errorMessage = "Error in editing event!";
            $pdo-rollBack();
            return false;
        }

        // If all tags are set to no tags then don't delete existing!
        if (!sizeof($tags) == 0) {
            if (!deleteTags($eventID,$pdo)) {
                $errorMessage = "Error in deleting existing tags!";
                $pdo-rollBack();
                return false;
            }
            foreach ($tags as $tag) {
                if (!insertTags($tag,$eventID,$pdo)) {
                    $errorMessage = "Error in inserting tags!";
                    $pdo->rollBack();
                    return false;
                }
            }
        }

        // Try uploading image
        if (!empty($_FILES['eventImage']['name'])) {
            if (!uploadEventImage($venueUserID,$venueID,$EventID,$pdo)) {
                $errorMessage = "Error in uploading image!";
                $pdo->rollBack();
                return false;
            }
        }

        // Everything completed successfully! return true
        $pdo->commit();
        return true;
    }

    function uploadEventImage($venueUserID,$venueID,$eventID,$pdo) {
        // Remove any existing file first
        $directory = "/home/sgstribe/private_upload/Venue/$venueUserID/$venueID/$eventID/event.jpg";
        if (file_exists($directory)) {
            chmod($directory,0755);
            unlink($directory);
        }
        if (move_uploaded_file($_FILES['eventImage']['tmp_name'],$directory)) {
            return true;
        } else {
            // Error in file upload!
            return false;
        }
    }


    function updateEvent($eventID,$name,$description,$startTime,$endTime,$pdo) {
        $updateEventStmt = $pdo->prepare("UPDATE Event SET EventName=:EventName, EventDescription=:EventDescription, EventStartTime=:EventStartTime EventEndTime=:EventEndTime WHERE EventID=:EventID");
        $updateEventStmt->bindValue(":EventName",$name);
        $updateEventStmt->bindValue(":EventDescription",$description);
        $updateEventStmt->bindValue(":EventStartTime",$startTime);
        $updateEventStmt->bindValue(":EventEndTime",$endTime);
        $updateEventStmt->bindValue(":EventID",$eventID);
        if (!$updateEventStmt->execute()) {
            // Error in update
            return false;
        } else {
            // Values updated correctly
            return true;
        }
    }

    function checkExistingEvent($name,$startTime,$endTime,$venueID,$pdo) {
        $checkExistingStmt = $pdo->prepare("SELECT EventID FROM Event WHERE EventName=:EventName AND (EventStartTime=:EventStartTime OR EventEndTime=:EventEndTime) AND VenueID=:VenueID");
        $checkExistingStmt->bindValue(":EventName",$name);
        $checkExistingStmt->bindValue(":EventStartTime",$startTime);
        $checkExistingStmt->bindValue(":EventEndTime",$endTime);
        $checkExistingStmt->bindValue(":VenueID",$venueID);
        $checkExistingStmt->execute();
        if ($checkExistingStmt->rowCount() > 0) {
            // A event exists with the same name and time and at the same venue!
            return true;
        } else {
            return false;
        }
    }

    // Reused from Venue-edit.php could be moved to config.php
    function checkTags(&$errorMessage) {
        unset($tags);
        $tags = [];
        if ((isset($_POST['tag1']) && $_POST['tag1'] != 'Optional')) {
            $tags[0] = $_POST['tag1'];
        }

        if (isset($_POST['tag2']) && $_POST['tag2'] != 'Optional') {
            if (in_array($_POST['tag2'],$tags)) {
                // Cannot have 2 of the same tag!
                $errorMessage = "You cannot have more than one of the same tag!";
                return false;
            } else {
                $tags[1] = $_POST['tag2'];
            }
        }

        if (isset($_POST['tag3']) && $_POST['tag3'] != 'Optional') {
            if (in_array($_POST['tag3'],$tags)) {
                // Cannot have 2 of the same tag!
                $errorMessage = "You cannot have more than one of the same tag!";
                return false;
            } else {
                $tags[2] = $_POST['tag3'];
            }
        }

        if (isset($_POST['tag4']) && $_POST['tag4'] != 'Optional') {
            if (in_array($_POST['tag4'],$tags)) {
                // Cannot have 2 of the same tag!
                $errorMessage = "You cannot have more than one of the same tag!";
                return false;
            } else {
                $tags[3] = $_POST['tag4'];
            }
        }

        if (isset($_POST['tag5']) && $_POST['tag5'] != 'Optional') {
            if (in_array($_POST['tag5'],$tags)) {
                // Cannot have 2 of the same tag!
                $errorMessage = "You cannot have more than one of the same tag!";
                return false;
            } else {
                $tags[4] = $_POST['tag5'];
            }
        }
        return $tags;
    }

    function getEventTagID($eventID,$pdo) {
        $EventTags = $pdo->prepare("SELECT TagID FROM EventTag WHERE EventID=:EventID");
        $EventTags->bindValue(":EventID",$eventID);
        $EventTags->execute();
        return $EventTags->fetchAll();
    }

    // When inserting new tags, the existing ones are deleted
    function deleteTags($eventID,$pdo) {
        $deleteTagsStmt = $pdo->prepare("DELETE FROM EventTag WHERE EventID=:EventID");
        $deleteTagsStmt->bindValue(":EventID",$eventID);
        if ($deleteTagsStmt->execute()) {
            // Tags deleted successfully!
            return true;
        } else {
            // Tags not deleted successfully!
            return false;
        }
    }

    // New tags added to the database
    function insertTags($tag,$eventID,$pdo) {
        $insertTagsStmt = $pdo->prepare("INSERT INTO EventTag (EventID,TagID) VALUES (:EventID,:TagID)");
        $insertTagsStmt->bindValue(":EventID",$eventID);
        $insertTagsStmt->bindValue(":TagID",$tag);
        if ($insertTagsStmt->execute()) {

            return true;
        } else {

            return false;
        }
    }

    function echoTags($pdo) {
        $tags = $pdo->query("SELECT * FROM Tag ORDER BY TagName");
        foreach ($tags as $row) {
            echo "<option value='".$row['TagID']."'>".$row['TagName']."</option>";
        }
    }

    // Returns an array of all event infomation
    function getEventInfo($eventID,$pdo) {
        $getVenueStmt = $pdo->prepare("SELECT VenueID, EventName, EventDescription, EventStartTime, EventEndTime FROM Event WHERE EventID=:EventID");
        $getVenueStmt->bindValue(":EventID",$eventID);
        $getVenueStmt->execute();
        return $getVenueStmt->fetch();
    }

?>

<!DOCTYPE html>
<head>
    <title>OutOut - Edit Event Details</title>
    <link rel="stylesheet" type="text/css" href="../css/events.css">
</head>
<body>
<form name='EventForm' method='post' enctype="multipart/form-data">
    <div>
        <input type='text' name='name' placeholder="Event Name"  value="<?php echo $name; ?>" required><br>
        <label for='description'>Event Description:</label>
        <textarea id='description' name ='description' form='EventForm' placeholder="Event Description, max 1000 characters" required><?php echo $description; ?></textarea><br>

        <p>Date and Time must be in the format: dd-mm-yyyy hh:mm (24 hour time)</p><br>

<!--    TODO: Revert input types to datetime-local -->

        <label for='endTime'>Event Start Time:</label>
        <input type='datetime-local' id="startTime" name='startTime' placeholder="Start time" value="<?php echo $startTime; ?>" required><br>
        <label for='endTime'>Event End Time:</label>
        <input type='datetime-local' id="endTime" name='endTime' placeholder="End time" value="<?php echo $endTime; ?>" required><br>
<!--    TODO: RESTRICT SIZE OF PICTURE THAT CAN BE UPLOADED -->
        Event Image: <br>
        <input type='file' id="eventImage" name='eventImage' class='input-file' accept=".jpg">
        <label for="eventImage">Upload Image</label>

        <!-- TAG INPUT -->
        <p>Current Tags: <?php getTags($currentTagIDs,$pdo); ?></p>
        <label for='tag1'>Add Tags for your event, these are optional but are used to recommend your event to users. Any changes made below will overwrite any existing Tags, If you want to keep the existing Tags then leave the tag fields below empty</label><br>
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
    </div>
    <div style= "display: flex">
        <input type='password' name='password' autocomplete="off" placeholder="Current Password" required><br>
        <input type='submit' id='submit' value='Update'>
<!--        TODO: FILL IN HREF ONCLICK OF CANCEL-->
        <input type="button" onclick="location.href='BACK TO DASHBOARD OR HOMEPAGE';" value="Cancel" />
    </div>
    <?php
        if ($errorMessage != "") {
            echo "<div class='error'>$errorMessage</div>";
        }
     ?>
</form>
</body>
