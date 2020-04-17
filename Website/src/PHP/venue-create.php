<?php

    /* Splitting up venue creation so all insertion into the Venue table is
     * done here and then once the venue has been created and inserted into the
     * Venue table, the user is redirected to the edit page to add additional
     * information to the venue such as tags and an image
     */

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

    // Error messages shown for testing
    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    // Config file is imported
    require_once "config.php";

    // Local venueUserID variable is set
    $venueUserID = $_SESSION["VenueUserID"];
    $errorMessage = "";

    try {
        if (!empty($_POST) && isset($_POST['submit'])) {
            /* User has submitted the creation form, check that the password is
             * correct, if so then continue with creation
             */
             if (checkInputs($venueUserID,$errorMessage,$pdo)) {
                 /* If everything is valid and the venue has been added to the
                  * db then VenueCreated session variable is set to true (to
                  * show message on the next page) and the user is redirected
                  * to the edit venue page to fill in additional details
                  */
                 $_SESSION['VenueCreated'] = true;
                 $venueID = $_SESSION['venueID'];
                 header("location: venue-edit-details.php?venueID=$venueID");
                 exit;
             }
        }

    } catch (PDOException $e) {
        // Any PDO errors are shown here
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

    /* All user-inputted values are checked in this function (mostly calling
     * other functions that specialise in one input)
     */
    function checkInputs($venueUserID,&$errorMessage,$pdo) {
        // Check password
        if (!(isset($_POST['password']) && !empty($_POST['password']))) {
            $errorMessage = "Please enter your password to add a venue";
            return false;
        }

        $password = $_POST['password'];
        if (!verifyVenuePassword($venueUserID,$password,$pdo)) {
            /* If the password is incorrect then they are not allowed to create
             * a new venue, an error message is shown
             */
            $errorMessage = "Password Incorrect!";
            return false;
        }
        // venue Name checking
        if (!(isset($_POST['venueName']) && !empty(trim($_POST['venueName'])))) {
            $errorMessage = "Please enter a name for the venue!";
            return false;
        } else {
            $name = trim($_POST['venueName']);
            if (!validate255($name)) {
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
            // Using the same validation as the venue name for length limit
            if (!validate255($address)) {
                $errorMessage = "The address cannot be more than 255 characters!";
                return false;
            }
        }

        //Check existing venues
        if (checkExistingVenue($name,$address,$pdo)) {
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

        $pdo->beginTransaction();

        if (!createVenue($venueUserID,$name,$description,$address,$times,$pdo,$errorMessage)) {
            $errorMessage = "Error in inserting new venue!";
            $pdo->rollBack();
            return false;
        }
        // Get venueID and assign to session variable for generating php get link
        $_SESSION['venueID'] = getVenueID($venueUserID,$name,$address,$pdo);
        // Create the Venue folder for the Venue User
        if (!createVenueFolder($venueUserID,$_SESSION['venueID'])) {
            $errorMessage = "Error in creating your folder on the web server!";
            $pdo->rollBack();
            return false;
        }
        $pdo->commit();
        // Everything completed successfully! return true
        return true;
    }

    /* If the time info is longer than 300 bytes then it is not valid */
    function validateTimes($times) {
        if (strlen($times) <= 300) {
            return true;
        } else {
            return false;
        }
    }

    function createVenue($venueUserID,$name,$description,$address,$times,$pdo) {
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

    /* If a venue has identical name and address to what the user entered then
     * true is returned, otherwise false
     */
    function checkExistingVenue($name,$address,$pdo) {
        $checkExistingStmt = $pdo->prepare("SELECT VenueID FROM Venue WHERE VenueName=:VenueName AND VenueAddress=:VenueAddress");
        $checkExistingStmt->bindValue(":VenueName",$name);
        $checkExistingStmt->bindValue(":VenueAddress",$address);
        $checkExistingStmt->execute();
        if ($checkExistingStmt->rowCount() > 0) {
            // A venue exists with the same name and address!
            return true;
        } else {
            return false;
        }
    }

    function createVenueFolder($venueUserID,$venueID) {
        $path = "/home/sgstribe/private_upload/Venue/$venueUserID/$venueID";
        if (mkdir($path,0755)) {
            // Folder created successfully
            return true;
        } else {
            // Error in folder creation!
            return false;
        }
    }

    function getVenueID($venueUserID,$name,$address,$pdo) {
        $getVenueIDStmt = $pdo->prepare("SELECT VenueID FROM Venue WHERE VenueUserID=:VenueUserID AND VenueName=:VenueName AND VenueAddress=:VenueAddress");
        $getVenueIDStmt->bindValue(":VenueUserID",$venueUserID);
        $getVenueIDStmt->bindValue(":VenueName",$name);
        $getVenueIDStmt->bindValue(":VenueAddress",$address);
        $getVenueIDStmt->execute();
        $row = $getVenueIDStmt->fetch();
        return $row['VenueID'];
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
<div class="banner">
    <img src="../Assets/menu-icon.svg" alt="Menu" width="25" onclick="openNav()" class="menu-image">
    <img src="../Assets/outout.svg" alt="OutOut" width="100">
    <img src="../Assets/profile.svg" alt="Profile" width="40">
</div>
<div id="mySidenav" class="sidenav">
    <div class="sidebar-content">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="#">Dashboard</a>
        <a href="#">Venues</a>
        <a href="#">Account</a>
        <a href="#">Contact</a>
    </div>
</div>
<script>
    function openNav() {
        document.getElementById("mySidenav").style.width = "200px";
    }

    function closeNav() {
        document.getElementById("mySidenav").style.width = "0";
    }
</script>
<div class="wrapper">
    <div class="container">
        <form id='CreateVenue' name='CreateVenue' method='post' style="margin-top: 10px" enctype="multipart/form-data">
            <div class="edit-fields">
                <h1 class="title">Add a venue</h1>
                <label>Venue name:</label>
                <input type='text' name='venueName' required>
                <label>Open and closing times:</label>
                <textarea id='times' name='times'
                          form='CreateVenue'
                          placeholder="Open every Friday, Saturday 21:00-04:00!"
                          required></textarea>
                <label>Address of venue:</label>
                <textarea form='CreateVenue'
                          id='venueLocation'
                          name='venueLocation'
                          placeholder="Maximum 255 characters"
                          required></textarea>
                <label>Describe your venue:</label>
                <textarea id='description'
                          name='description'
                          form='CreateVenue'
                          placeholder="Venue Description"
                          required></textarea>
                <div class="seperator">
                    <label>Enter current password to allow changes:</label>
                    <input type='password' name='password' required>
                </div>
                <input type='submit' name='submit' value='Add Venue' class="button" required style="width: 100%">
            </div>
        </form>
    </div>
</div>
<?php
if ($errorMessage != "") {
    echo "<div class='error'>$errorMessage</div>";
}
?>
</body>
</html>
