<?php

    session_start();

    if (!isset($_SESSION["VenueUserID"])) {
        header("location: home.php");
        exit;
    }

    if (!isset($_GET['venueID'])) {
        $_SESSION['message'] = "No venueID specified!";
        header("location: 404.php");
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

    if (!checkVenueExists($venueID,$pdo)) {
        $_SESSION['message'] = "This venue does not exist!";
        header("location: 404.php");
        exit;
    }

    // Now check that the user accessing this venue is allowed to
    if (!checkVenueUserAllowed($venueID,$venueUserID,$pdo)) {
        // User is not allowed to edit!
        $_SESSION['message'] = "You are not allowed to edit this venue!";
        header("location: venue-user-dashboard.php");
        exit;
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
                 $_SESSION['message'] = "Venue Edited Successfully!";
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
            if (!validate255($address)) {
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
        if (!empty($_FILES['Image']['name'])) {
            if (!checkImage($errorMessage)) {
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
        if (!empty($_FILES['Image']['name'])) {
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

    // Delete Venue
    if (isset($_POST['delete'])) {
        $success = deleteVenue($venueID, $pdo);
        if ($success){
          header("location: venue-user-dashboard.php" );
          exit;
        } else {

        }
    }

    function deleteVenue($venueID, $pdo){
        $pdo->beginTransaction();
        $deleteVenueStmt = $pdo->prepare("DELETE FROM Reviews WHERE VenueID=:VenueID");
        $deleteVenueStmt->bindValue(':VenueID',$venueID);
        $success = $deleteVenueStmt->execute();
        if (!$success){
          $errorMessage = "Error in deleteing venue reviews!";
          $pdo->rollBack();
          return false;
        }
        $deleteVenueStmt = $pdo->prepare("DELETE FROM VenueTag WHERE VenueID=:VenueID");
        $deleteVenueStmt->bindValue(':VenueID',$venueID);
        $success = $deleteVenueStmt->execute();
        if (!$success){
          $errorMessage = "Error in deleteing venue tags!";
          $pdo->rollBack();
          return false;
        }
        $events = getEvents($venueID, $pdo);
        if ($events !== false){
          foreach($events as $row){
            $deleteEventStmt = $pdo->prepare("DELETE FROM EventTag WHERE EventID=:EventID");
            $deleteEventStmt->bindValue(':EventID',$row['EventID']);
            $success = $deleteEventStmt->execute();
            if (!$success){
              $errorMessage = "Error in deleteing event tags! Event=".$row['EventName']."";
              $pdo->rollBack();
              return false;
            }
            $deleteEventStmt = $pdo->prepare("DELETE FROM InterestedIn WHERE EventID=:EventID");
            $deleteEventStmt->bindValue(':EventID',$row['EventID']);
            $success = $deleteEventStmt->execute();
            if (!$success){
              $errorMessage = "Error in deleteing event InterestedIn! Event=".$row['EventName']."";
              $pdo->rollBack();
              return false;
            }
            $deleteEventStmt = $pdo->prepare("DELETE FROM Event WHERE EventID=:EventID");
            $deleteEventStmt->bindValue(':EventID',$row['EventID']);
            $success = $deleteEventStmt->execute();
            if (!$success){
              $errorMessage = "Error in deleteing event! Event=".$row['EventName']."";
              $pdo->rollBack();
              return false;
            }
          }
        }
        $deleteVenueStmt = $pdo->prepare("DELETE FROM Venue WHERE VenueID=:VenueID");
        $deleteVenueStmt->bindValue(':VenueID',$venueID);
        $success = $deleteVenueStmt->execute();
        if (!$success){
          $errorMessage = "Error in deleteing venue!";
          $pdo->rollBack();
          return false;
        }
        $pdo->commit();
        return true;
    }

    function validateTimes($times) {
        if (strlen($times) <= 500) {
            return true;
        } else {
            return false;
        }
    }


    function uploadImage($venueUserID,$venueID,$pdo) {
        // Remove any existing file first
        $directory = "/home/sgstribe/public_html/Images/Venue/$venueUserID/$venueID/venue.jpg";
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

    function getTagID($venueID,$pdo) {
        $getVenueTagsStmt = $pdo->prepare("SELECT TagID FROM VenueTag WHERE VenueID=:VenueID");
        $getVenueTagsStmt->bindValue(":VenueID",$venueID);
        $getVenueTagsStmt->execute();
        return $getVenueTagsStmt->fetchAll();
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

    $image = checkVenueImageOnServer($venueUserID,$venueID);

?>

<!DOCTYPE html>
<html lang='en-GB'>
<head>
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
        <h1 class="title"><?php echo $name; ?></h1>
        <?php
            if ($image) {
                echo '<div class="seperator"></div>';
                echo '<img src="https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/'.$venueUserID.'/'.$venueID.'/venue.jpg" alt="Venue Image">';
            }
        ?>

        <form id='CreateVenue' name='CreateVenue' method='post' style="margin-top: 10px" enctype="multipart/form-data">
            <div class="edit-fields">
                <label for='venueName'>Venue Name:</label>
                <input type='text' id='venueName' name='venueName' placeholder="Venue Name"
                       value="<?php echo $name; ?>">
                <label for='times'>Time Information:</label>
                <textarea id='times' name='times'
                          form='CreateVenue'
                          placeholder="Venue Opening and Closing Times"><?php echo $times; ?></textarea>
                <label for='venueLocation'>Location Information:</label>
                <textarea id='venueLocation' name='venueLocation' form='CreateVenue'
                          placeholder="Venue Address and Location details, no more than 255 characters"><?php echo $address; ?></textarea>
                <label for='description'>Venue Description:</label>
                <textarea id='description' name='description' form='CreateVenue'
                          placeholder="Venue Description"><?php echo $description; ?></textarea>
                <div class="seperator">
                    <h2 class="title">Additional Information</h2>
                </div>
                <input type='file' id="Image" name='Image' class='input-file' accept=".jpg">
                <label for="Image">Add Venue Image (must be .jpg and cannot be bigger than 2MB)</label>


                <!-- Script here, if no tags present dont display any of the tag stuff -->
                <label style="text-align: center; margin-top: 16px;"><b>Current Tags:</b></label>
                <div style="display: flex; justify-content: center; ">
                    <div class="tag-container" style="text-align: center">
                        <?php getTags($currentTagIDs,$pdo); ?>
                    </div>
                </div>

                <label for='tag1'>Add some tags that best describe your venue - this will overwrite old tags</label>
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
                <select name='tag5' id='tag5' onmousedown="if(this.options.length>5){this.size=4;}" onchange="this.size=0;" onblur="this.size=0;" style="margin-bottom: 16px">
                    <option value='Optional'>No Tag</option>
                    <?php echoTags($pdo); ?>
                </select>
                <div class="seperator">
                    <label>Enter current password to allow changes:</label>
                    <input type='password' name='password' required>
                </div>
                <input type='submit' name='submit' value='Save changes' class="button" style="width: 100%"><br>
                <div class="seperator" style="margin-top: 4px"></div>
            </div>
        </form>
        <form id='DeleteVenue' name='DeleteVenue' method='post' style="margin-top: 10px" enctype="multipart/form-data">
          <div class="edit-fields">
            <input type='submit' name='delete' value='Delete Venue' class="button" style="width: 100%">
          </div>
        </form>
    </div>
<?php
    if ($errorMessage != "") {
        echo "<div class='message-wrapper'><div class='error'>$errorMessage</div></div>";
    }
?>
</div>
</body>
</html>
