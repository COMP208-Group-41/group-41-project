<?php
/* Config file for database connection, all other pages use require_once at the
 * start of the file and reference this config file */
$db_hostname = "studdb.csc.liv.ac.uk";
$db_database = "sgstribe";
$db_username = "sgstribe";
$db_password = "scriptingdb";
$db_charset = "utf8mb4";

$dsn = "mysql:host=$db_hostname;dbname=$db_database;charset=$db_charset";
$opt = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
);

try {
    /* A connection to the database is attempted, if unsuccessful then the
     * PDO Exception is caught and an error page is shown (possibly try to give
     * a more user-friendly error page) */
    $pdo = new PDO($dsn,$db_username, $db_password,$opt);
} catch (PDOException $e) {
    exit("PDO Error: ".$e->getMessage()."<br>");
}

/* The function passwordHasher hashes the password given by the user
 * It is in it's own function so this can be easily edited later if needed
 */
function passwordHasher($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/* The function validatePassword returns true if the password provided by
 * the user is valid according to validation rules: must be at least 8
 * characters, must contain at least 1 lower case letter and at least one
 * number
 */
function validatePassword($password) {
    if ((strlen($password) >= 8) && (preg_match("/[a-z]/",$password)) && (preg_match("/[0-9]/",$password))) {
        return true;
    } else {
        return false;
    }
}

/* The function checkVenueEmailExists returns true if the email provided already
 * exists in the VenueUser database table
 */
function checkVenueEmailExists($email,$pdo) {
    // Register form has been filled out and submitted, check if email already exists in db
    $checkExistingStmt = $pdo->prepare("SELECT VenueUserEmail FROM VenueUser WHERE VenueUserEmail=:VenueUserEmail");
    $checkExistingStmt->bindValue(':VenueUserEmail',$email);
    $checkExistingStmt->execute();
    if ($checkExistingStmt->rowCount() > 0) {
        // Email exists, return true
        return true;
    } else {
        return false;
    }
}

/* validate255 returns true if the name given does not exceed 255
 * characters and returns false if it does
 */
function validate255($name) {
    if (strlen($name) <= 255) {
        return true;
    } else {
        return false;
    }
}

    /* The verifyVenuePassword function returns true if the venue user's
     * password is correct using the password_verify function
     */
    function verifyVenuePassword($VenueUserID,$password,$pdo) {
        $checkPasswordStmt = $pdo->prepare("SELECT VenueUserPass FROM VenueUser WHERE VenueUserID=:VenueUserID");
        $checkPasswordStmt->bindValue(':VenueUserID',$VenueUserID);
        $checkPasswordStmt->execute();
        $row = $checkPasswordStmt->fetch();
        /* If the password is verified then return true */
        if (password_verify($password,$row['VenueUserPass'])) {
            return true;
        } else {
            return false;
        }
    }

    /* If the description is longer than 1000 bytes then it is not valid */
    function validateDescription($description) {
        if (strlen($description) <= 1000) {
            return true;
        } else {
            return false;
        }
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

    function getEvents($venueID,$pdo) {
        $getVenuesStmt = $pdo->prepare("SELECT EventID,EventName FROM Event WHERE VenueID=:VenueID");
        $getVenuesStmt->bindValue(":VenueID",$venueID);
        $getVenuesStmt->execute();
        $results = $getVenuesStmt->fetchAll();
        if (sizeof($results) == 0) {
            // Venue has no events, show error message!
            return false;
        } else {
            return $results;
        }
    }

    function eventToVenueUser($eventID,$pdo) {
        $getVenuesStmt = $pdo->prepare("SELECT VenueID FROM Event WHERE EventID=:EventID");
        $getVenuesStmt->bindValue(":EventID",$eventID);
        $getVenuesStmt->execute();
        $result = $getVenuesStmt->fetch();
        $getVenuesUserStmt = $pdo->prepare("SELECT VenueUserID FROM Venue WHERE VenueID=:VenueID");
        $getVenuesUserStmt->bindValue(":VenueID",$result['VenueID']);
        $getVenuesUserStmt->execute();
        $result = $getVenuesUserStmt->fetch();
        if (sizeof($result) == 0) {
            // Error
            return false;
        } else {
            return $result;
        }
    }

    function echoVenues($venues) {
        foreach ($venues as $row) {
            echo "<option value=".$row['VenueID'].">".$row['VenueName']."</option>";
        }
    }

    function echoEvents($events) {
        foreach ($events as $row) {
            echo "<option value=".$row['EventID'].">".$row['EventName']."</option>";
        }
    }

    function checkImage(&$errorMessage) {
        if ($_FILES['Image']['size'] == 0) {
            $errorMessage = "No file selected or the selected file is too large!";
            return false;
        }

        if ($_FILES['Image']['error'] != 0) {
            $errorMessage = "Error in file upload";
            return false;
        }

        if ($_FILES['Image']['type'] != "image/jpeg") {
            $errorMessage = "File must be a jpeg!";
            return false;
        }

        return true;
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
                echo $tag['TagName']."<br>";
            }
        } else {
            echo "No Tags";
        }
    }

    // Checks review scores are within suitable boundries
    function validationReviewScore($reviewScore){
        if ($reviewScore < 1 || $reviewScore > 5){
          return false;
        } else {
          return true;
        }
    }

    function getVenueUserInfo($venueUserID, $pdo) {
        $infoStmt = $pdo->prepare("SELECT VenueUserEmail,VenueUserName,VenueUserExternal FROM VenueUser WHERE VenueUserID=:VenueUserID");
        $infoStmt->bindValue(":VenueUserID",$venueUserID);
        $infoStmt->execute();
        return $infoStmt->fetch();
    }

    function getUserInfo($UserID, $pdo) {
        $infoStmt = $pdo->prepare("SELECT UserName,UserEmail,UserDOB FROM User WHERE UserID=:UserID");
        $infoStmt->bindValue(":UserID",$UserID);
        $infoStmt->execute();
        return $infoStmt->fetch();
    }

    /* The verifyPassword function returns true if the user's password is correct
     * using the password_verify function
     */
    function verifyPassword($UserID,$password,$pdo) {
        $checkPasswordStmt = $pdo->prepare("SELECT UserPass FROM User WHERE UserID=:UserID");
        $checkPasswordStmt->bindValue(':UserID',$UserID);
        $checkPasswordStmt->execute();
        $row = $checkPasswordStmt->fetch();
        /* If the password is verified then return true */
        if (password_verify($password,$row['UserPass'])) {
            return true;
        } else {
            return false;
        }
    }

    function getUserTags($userID,$pdo){
      $infoStmt = $pdo->prepare("SELECT TagID FROM UserPreferences WHERE UserID=:UserID");
      $infoStmt->bindValue(":UserID",$userID);
      $infoStmt->execute();
      return $infoStmt->fetchAll();
    }

    function getInterested($userID,$pdo){
      $infoStmt = $pdo->prepare("SELECT EventID FROM InterestedIn WHERE UserID=:UserID");
      $infoStmt->bindValue(":UserID",$userID);
      $infoStmt->execute();
      return $infoStmt->fetchAll();
    }

    /* The function findUser checks if the account exists in the database
     * with the email and password, and returns the UserID if the user
     * exists, or 0 if they do not (no UserID can be 0)
     */
    function findUser($email,$pdo) {
        /* Try to find the user in the database using provided
         * username and password
         */
        $loginstmt = $pdo->prepare("SELECT UserID FROM User WHERE UserEmail=:UserEmail");
        $loginstmt->bindValue(":UserEmail",$email);
        $loginstmt->execute();
        if ($loginstmt->rowCount() == 1) {
            $row = $loginstmt->fetch();
            return $row['UserID'];
        } else {
            return 0;
        }
    }

    /* The function checkEmailExists returns true if the email provided already
     * exists in the User database table
     */
    function checkEmailExists($email,$pdo) {
        // Register form has been filled out and submitted, check if email already exists in db
        $checkExistingStmt = $pdo->prepare("SELECT UserEmail FROM User WHERE UserEmail=:UserEmail");
        $checkExistingStmt->bindValue(':UserEmail',$email);
        $checkExistingStmt->execute();
        if ($checkExistingStmt->rowCount() > 0) {
            // Email exists, return true
            return true;
        } else {
            return false;
        }
    }

    /* The function checkValidAge returns false if the date of birth given by the
     * user means they are under 18 or the date is in the future
     */
    function checkValidAge($dob) {
        // First check the date isn't in the future
        try {
            $dobTimestamp = strtotime($dob);
            $mysqlDob = date("Y-m-d",$dobTimestamp);
            /* If the dob entered was invalid then the strtotime and date
             * conversions above will throw an exception, otherwise they are
             * valid
             */
            $bday = new DateTime($dob);
            $bday->add(new DateInterval("P18Y"));

            if ($bday > new DateTime("now")) {
                // user is under 18
                return false;
            } else {
                // user is over 18
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /* The function checkUsernameExists returns true if the username provided already
     * exists in the User database table
     */
    function checkUsernameExists($username,$pdo) {
        // Register form has been filled out and submitted, check if username already exists in db
        $checkExistingStmt = $pdo->prepare("SELECT UserName FROM User WHERE UserName=:UserName");
        $checkExistingStmt->bindValue(':UserName',$username);
        $checkExistingStmt->execute();
        if ($checkExistingStmt->rowCount() > 0) {
            // Username exists, return true
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

    function getVenueInfo($venueID,$pdo) {
        $getVenueStmt = $pdo->prepare("SELECT VenueUserID,VenueName,VenueDescription,VenueAddress,VenueTimes FROM Venue WHERE VenueID=:VenueID");
        $getVenueStmt->bindValue(":VenueID",$venueID);
        $getVenueStmt->execute();
        return $getVenueStmt->fetch();
    }

    // Returns an array of all event infomation
    function getEventInfo($eventID,$pdo) {
        $getVenueStmt = $pdo->prepare("SELECT VenueID, EventName, EventDescription, DATE_FORMAT(EventStartTime,'%Y-%m-%dT%H:%i') AS EventStartTime, DATE_FORMAT(EventEndTime,'%Y-%m-%dT%H:%i') AS EventEndTime FROM Event WHERE EventID=:EventID");
        $getVenueStmt->bindValue(":EventID",$eventID);
        $getVenueStmt->execute();
        return $getVenueStmt->fetch();
    }

    function getEventTagID($eventID,$pdo) {
        $EventTags = $pdo->prepare("SELECT TagID FROM EventTag WHERE EventID=:EventID");
        $EventTags->bindValue(":EventID",$eventID);
        $EventTags->execute();
        return $EventTags->fetchAll();
    }

    function checkReviewWritten($userID,$eventID,$venueID,$pdo) {
        $getReviewIDStmt = $pdo->prepare("SELECT ReviewID FROM Review WHERE UserID=:UserID AND EventID=:EventID AND VenueID=:VenueID");
        $getReviewIDStmt->bindValue(":UserID",$userID);
        $getReviewIDStmt->bindValue(":EventID",$eventID);
        $getReviewIDStmt->bindValue(":VenueID",$venueID);
        $getReviewIDStmt->execute();
        if ($getReviewIDStmt->rowCount() == 0) {
            return false;
        } else {
            $result = $getReviewIDStmt->fetch();
            return $result['ReviewID'];
        }
    }

    function getVenueReviews($venueID,$pdo){
      $getReviewStmt = $pdo->prepare("SELECT UserID, ReviewDate, ReviewText, ReviewAtmosphere,ReviewPrice, ReviewSafety, ReviewQueue  FROM Review WHERE VenueID=:VenueID ORDER BY ReviewDate");
      $getReviewStmt->bindValue(":VenueID",$venueID);
      $getReviewStmt->execute();
      return $getReviewStmt->fetchAll();
    }

?>
