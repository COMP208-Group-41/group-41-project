<?php

    session_start();


    if (!isset($_SESSION["VenueUserID"])) {
        header("location: home.php");
        exit;
    }

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    $venueUserID = $_SESSION["VenueUserID"];
    $eventID = $_GET['eventID'];
    $errorMessage = "";

    $eventToVenueUser = eventToVenueUser($eventID,$pdo);
    $eventToVenueUser = $eventToVenueUser['VenueUserID'];
    if($eventToVenueUser === false){
      $errorMessage = "Error getting VenueUserID";
    } else if ($eventToVenueUser != $venueUserID) {
        $_SESSION['message'] = "You are not allowed to edit this event!";
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
                 $_SESSION['message'] = "Event Edited Successfully!";
                 // Refresh details!

                 $result = getEventInfo($eventID,$pdo);
                 $name = $result['EventName'];
                 $description = $result['EventDescription'];
                 $startTime = $result['EventStartTime'];
                 $endTime = $result['EventEndTime'];

                 $currentTagIDs = getEventTagID($eventID,$pdo);
             }
        }

    } catch (PDOException $e) {
        exit("PDO Error: ".$e->getMessage()."<br>");
    }


    function checkInputs($venueUserID,$eventID,$venueID,&$errorMessage,$pdo) {

        // Firstly check the user's password
        if (!(isset($_POST['password']) && !empty($_POST['password']))) {
            $errorMessage = "Please enter your password to add an event";
            return false;
        }

        if (!(isset($_POST['name']) && !empty(trim($_POST['name'])))) {
            $errorMessage = "Please enter a name for the event!";
            return false;
        } else {
            $name = trim($_POST['name']);
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

        if (!empty($_FILES['Image']['name'])) {
            if (!checkImage($errorMessage)) {
                return false;
            }
        }

        //Check existing venues
        if (checkExistingEvent($name,$mysqlStartDateTime,$mysqlEndDateTime,$eventID,$pdo)) {
            // A event already exists with the same name and start times at the same venue!
            $errorMessage = "An event already exists at this venue with the same name and time!";
            return false;
        }

        $pdo->beginTransaction();

        if (!updateEvent($eventID,$name,$description,$mysqlStartDateTime,$mysqlEndDateTime,$pdo)) {
            $errorMessage = "Error in editing event!";
            $pdo->rollBack();
            return false;
        }

        // If all tags are set to no tags then don't delete existing!
        if (!sizeof($tags) == 0) {
            if (!deleteTags($eventID,$pdo)) {
                $errorMessage = "Error in deleting existing tags!";
                $pdo->rollBack();
                return false;
            }
            foreach ($tags as $tag) {
                if (!insertEventTags($tag,$eventID,$pdo)) {
                    $errorMessage = "Error in inserting tags!";
                    $pdo->rollBack();
                    return false;
                }
            }
        }

        // Try uploading image
        if (!empty($_FILES['Image']['name'])) {
            if (!uploadEventImage($venueUserID,$venueID,$eventID,$pdo)) {
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
        if (move_uploaded_file($_FILES['Image']['tmp_name'],$directory)) {
            return true;
        } else {
            // Error in file upload!
            return false;
        }
    }


    function updateEvent($eventID,$name,$description,$startTime,$endTime,$pdo) {
        $updateEventStmt = $pdo->prepare("UPDATE Event SET EventName=:EventName, EventDescription=:EventDescription, EventStartTime=:EventStartTime, EventEndTime=:EventEndTime WHERE EventID=:EventID");
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

    function checkExistingEvent($name,$startTime,$endTime,$eventID,$pdo) {
        $checkExistingStmt = $pdo->prepare("SELECT EventID FROM Event WHERE EventName=:EventName AND (EventStartTime=:EventStartTime OR EventEndTime=:EventEndTime) AND EventID<>:EventID");
        $checkExistingStmt->bindValue(":EventName",$name);
        $checkExistingStmt->bindValue(":EventStartTime",$startTime);
        $checkExistingStmt->bindValue(":EventEndTime",$endTime);
        $checkExistingStmt->bindValue(":EventID",$eventID);
        $checkExistingStmt->execute();
        if ($checkExistingStmt->rowCount() > 0) {
            // A event exists with the same name and time and at the same venue!
            return true;
        } else {
            return false;
        }
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
    function insertEventTags($tag,$eventID,$pdo) {
        $insertEventTagsStmt = $pdo->prepare("INSERT INTO EventTag (EventID,TagID) VALUES (:EventID,:TagID)");
        $insertEventTagsStmt->bindValue(":EventID",$eventID);
        $insertEventTagsStmt->bindValue(":TagID",$tag);
        if ($insertEventTagsStmt->execute()) {

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
    <link rel="stylesheet" type="text/css" href="../css/events.css">
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
    <div class="container">
        <form id='EventForm' name='EventForm' method='post' enctype="multipart/form-data">
            <div class="edit-fields">
                <input type='text' name='name' placeholder="Event Name" value="<?php echo $name; ?>" required>
                <label for='description'>Event Description:</label>
                <textarea id='description' name='description' form='EventForm'
                          placeholder="Event Description, max 1000 characters"
                          required><?php echo $description; ?></textarea>

                <label for='endTime'>Event Start Time:</label>
                <input type='datetime-local' id="startTime" name='startTime' placeholder="Start time"
                       value="<?php echo $startTime; ?>" required>
                <label for='endTime'>Event End Time:</label>
                <input type='datetime-local' id="endTime" name='endTime' placeholder="End time"
                       value="<?php echo $endTime; ?>" required>

                <input type='file' id="Image" name='Image' accept=".jpg" class="input-file">
                <label for="Image">Upload Image</label>

                <!-- TAG INPUT -->
                <!-- Script here, if no tags present dont display any of the tag stuff -->
                <label style="text-align: center; margin-top: 16px;"><b>Current Tags:</b></label>
                <div style="display: flex; justify-content: center; ">
                    <div class="tag-container" style="text-align: center">
                        <?php getTags($currentTagIDs,$pdo); ?>
                    </div>
                </div>
                <label>Add some tags that best describe your event - this will overwrite old tags</label>
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
                <select name='tag5' id='tag5' onmousedown="if(this.options.length>5){this.size=4;}" onchange="this.size=0;" onblur="this.size=0;">
                    <option value='Optional'>No Tag</option>
                    <?php echoTags($pdo); ?>
                </select><br>
            </div>
            <div class="seperator">
                <label>Enter current password to allow changes:</label>
                <input type='password' name='password' required>
                <input type='submit' name='submit' value='Update' class="button" style="width: 100%">
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
