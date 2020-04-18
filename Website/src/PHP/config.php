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


?>
