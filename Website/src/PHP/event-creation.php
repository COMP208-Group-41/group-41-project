<?php

    /* As with venue-creation, won't have tag entry in this page, or image
     * upload
     */


    session_start();

    // Testing purposes
    $_SESSION['venueUserID'] = 3;
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

    $venueUserID = $_SESSION['VenueUserID'];
    $errorMessage = "";

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




    } catch (PDOException $e) {
        // Any PDO errors are shown here
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

    function checkInputs($venueUserID,&$errorMessage,$pdo) {

        // Firstly check the user's password
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
<form name='EventCreation' method='post'>
    <div>
        <label for='venue'>Select a Venue to create an event for:</label>
        <select name='venue' id='venue'>
            <option value='None'>Select Venue</option>
            <?php echoVenues($venues); ?>
        </select><br>
        <input type='text' name='name' placeholder="Event Name" required><br>
        <input type='text' name='description' placeholder="Event Description" required> <br>
        <input type='text' id="startTime" name='startTime' placeholder="Start time" required>
        <input type='text' id="endTime" name='endTime' placeholder="End time" required><br>

        <input type='password' name='password' autocomplete="off" placeholder="Current Password"><br>

        <script>
            var dtt = document.getElementById('startTime');
            dtt.onfocus = function (event) {
                this.type = 'datetime-local';
                this.focus();
            };
            dtt.onblur = function (event) {
                this.type = 'text';
                this.blur();
            };
            var ett = document.getElementById('endTime');
            ett.onfocus = function (event) {
                this.type = 'datetime-local';
                this.focus();
            };
            ett.onblur = function (event) {
                this.type = 'text';
                this.blur();
            };
        </script>
    </div>
    <div style= "display: flex">
        <input type='submit' value='Create'>
        <input type="button" onclick="location.href='BACK TO DASHBOARD OR HOMEPAGE';" value="Cancel" />
    </div>
</form>
<?php
    if ($errorMessage != "") {
        echo "<div class='error'>$errorMessage</div>";
    }
 ?>
</body>
</html>
