<?php

    /* As with venue-creation, won't have tag entry in this page, or image
     * upload
     */

     /* This page will not work on safari or firefox, add checks */

    session_start();

    // Testing purposes
    $_SESSION['venueUserID'] = 2;
    $_SESSION['loggedin'] = true;

    // if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    //     header("location: venue-login.php");
    //     exit;
    //     /* If the user is logged in but they are not a venue user then they are
    //      * redirected to home page
    //      */
    // } else if (!isset($_SESSION["VenueUserID"])) {
    //     header("location: home.php");
    //     exit;
    // }

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    $venueUserID = $_SESSION['VenueUserID'];
    $errorMessage = "";
    // $unsupportedBrowser = "";

    // $user_agent = $_SERVER['HTTP_USER_AGENT'];

    /* If the user is using firefox or safari then a message needs to be shown
     * informing the user that the event creation form may not work correctly
     */
    // if (preg_match('/Firefox/i',$user_agent) || preg_match('/Safari/i',$user_agent)) {
    //     $unsupportedBrowser = "The browser you are using is not fully supported for this page! You may have issues with the date and time entry, if so we suggest using chrome, edge or Opera";
    // } else {
    //     $unsupportedBrowser = "";
    // }

    try {
        $venues = getVenues($venueUserID,$pdo);
        if ($venues === false) {
            /* The user has no Venues to add an event for, show error message,
             * ideally on the page they are redirected to (their home page)
             */
             $_SESSION['message'] = "You do not have any Venues to add an event to!";
             header("location: venue-user-dashboard.php");
             exit;
        }

        if (checkInputs($venueUserID,$errorMessage,$pdo)) {
            /* If everything is valid and the event has been added to the
             * db then EventCreated session variable is set to true (to
             * show message on the next page) and the user is redirected
             * to the edit event page to fill in additional details
             */
            $_SESSION['EventCreated'] = true;
            $eventID = $_SESSION['eventID'];
            header("location: events-edit.php?eventID=$eventID");
            exit;
        }


    } catch (PDOException $e) {
        // Any PDO errors are shown here
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

    function checkInputs($venueUserID,&$errorMessage,$pdo) {

        // Firstly check the user's password
        if (!(isset($_POST['password']) && !empty($_POST['password']))) {
            $errorMessage = "Please enter your password to add an event";
            return false;
        }

        $password = $_POST['password'];
        if (!verifyVenuePassword($venueUserID,$password,$pdo)) {
            /* If the password is incorrect then they are not allowed to create
             * a new event, an error message is shown
             */
            $errorMessage = "Password Incorrect!";
            return false;
        }

        // Check which venue has been selected
        if (!(isset($_POST['venue'])) && $_POST['venue'] != 'None') {
            // No Venue selected!
            $errorMessage = "Please select a venue!";
            return false;
        }
        $venueID = $_POST['venue'];

        // Check name input
        if (!(isset($_POST['eventName']) && !empty(trim($_POST['eventName'])))) {
            $errorMessage = "Please enter a name for the event!";
            return false;
        }

        $name = trim($_POST['eventName']);
        // Use same validation as venue name as they have the same constraints!
        if (!validateVenueName($name)) {
            $errorMessage = "The name cannot be more than 255 characters!";
            return false;
        }

        // Check description
        if (!(isset($_POST['description']) && !empty(trim($_POST['description'])))) {
            // Description empty!
            $errorMessage = "Please enter a description for the event!";
            return false;
        }
        $description = trim($_POST['description']);
        if (!validateDescription($description)) {
            $errorMessage = "The description cannot be longer than 1000 characters!";
            return false;
        }

        try {
            // Check times given
            if (isset($_POST['startTime']) && !empty($_POST['startTime'])) {
                $phpStartDateTime = new DateTime($_POST['startTime']);
                if (new DateTime("now") > $startTime) {
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
                if (new DateTime("now") > $endTime) {
                    $errorMessage = "Event cannot be in the past!";
                    return false;
                }
                if ($startTime > $endTime) {
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

        // Everything is valid, try inserting user into database
        $pdo->beginTransaction();
        if (!createEvent($venueID,$name,$description,$mysqlStartDateTime,$mysqlEndDateTime,$pdo)) {
            $errorMessage = "Error in inserting event into database!";
            $pdo->rollBack();
            return false;
        }
        $_SESSION['eventID'] = getEventID($venueID,$name,$description,$pdo);
        // Create Event Folder
        if (!createEventFolder($venueUserID,$venueID,$_SESSION['eventID'])) {
            $errorMessage = "Error in creating event folder!";
            $pdo->rollBack();
            return false;
        }
        $pdo->commit();
        // Everything completed successfully, return true!
        return true;
    }

    function createEvent($venueID,$name,$description,$startTime,$endTime,$pdo) {
        $createEventStmt = $pdo->prepare("INSERT INTO Event (VenueID,EventName,EventDescription,EventStartTime,EventEndTime) VALUES (:VenueID,:EventName,:EventDescription,:EventStartTime,:EventEndTime)");
        $createEventStmt->bindValue(":VenueID",$venueID);
        $createEventStmt->bindValue(":EventName",$name);
        $createEventStmt->bindValue(":EventDescription",$description);
        $createEventStmt->bindValue(":startTime",$startTime);
        $createEventStmt->bindValue(":endTime",$endTime);
        if ($createEventStmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    function createEventFolder($venueUserID,$venueID,$eventID) {
        $path = "/home/sgstribe/private_upload/Venue/$venueUserID/$venueID/$eventID";
        if (mkdir($path,0755)) {
            // Folder created successfully
            return true;
        } else {
            // Error in folder creation!
            return false;
        }
    }

    function getEventID($venueID,$eventName,$description,$pdo) {
        $getEventIDStmt = $pdo->prepare("SELECT EventID FROM Event WHERE VenueID=:VenueID AND EventName=:EventName AND EventDescription=:EventDescription");
        $getEventIDStmt->bindValue(":VenueID",$venueID);
        $getEventIDStmt->bindValue(":EventName",$eventName);
        $getEventIDStmt->bindValue(":EventDescription",$description);
        $getEventIDStmt->execute();
        $row = $getEventIDStmt->fetch();
        return $row['EventID'];
    }

    function getVenues($venueUserID,$pdo) {
        $getVenuesStmt = $pdo->prepare("SELECT VenueID,VenueName FROM Venue WHERE VenueUserID=:VenueUserID");
        $getVenuesStmt->bindValue(":VenueUserID",$venueUserID);
        $getVenuesStmt->execute();
        $results = $getVenuesStmt->fetchAll();
        if (sizeof($results) == 0) {
            // Venue User has no venues, show error message!
            return false;
        } else {
            return $results;
        }

    }

    function echoVenues($venues) {
        foreach ($venues as $row) {
            echo "<option value=".$row['VenueID'].">".$row['VenueName']."</option>";
        }
    }
?>

<!DOCTYPE html>
<html lang='en-GB'>
<head>
<title>OutOut - Event Creation</title>
    <link rel="stylesheet" type="text/css" href="../css/events.css">
</head>
<body>
<form id='EventCreation' name='EventCreation' method='post'>
    <div>
        <label for='venue'>Select a Venue to create an event for:</label>
        <select name='venue' id='venue'>
            <option value='None'>Select Venue</option>
            <?php echoVenues($venues); ?>
        </select><br>
        <input type='text' name='eventName' placeholder="Event Name" required><br>

        <textarea id='description' name ='description' form='EventCreation' placeholder="Event Description, max 1000 characters" required></textarea><br>
        <label for='startTime'>Event Start Time:</label>
        <input type='datetime-local' id="startTime" name='startTime' placeholder="Start time" required><br>
        <label for='endTime'>Event End Time:</label>
        <input type='datetime-local' id="endTime" name='endTime' placeholder="End time" required><br>

        <input type='password' name='password' autocomplete="off" placeholder="Current Password" required><br>

    </div>
    <div style= "display: flex">
        <input type='submit' name='submit' value='Create'>
        <input type="button" onclick="location.href='BACK TO DASHBOARD OR HOMEPAGE';" value="Cancel" />
    </div>
</form>
<?php
    if ($errorMessage != "") {
        echo "<div class='error'>$errorMessage</div>";
    }
    // if ($unsupportedBrowser != "") {
    //     echo "<div class='browser-error'>$unsupportedBrowser</div>";
    // }
 ?>
</body>
</html>
