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

/* validateVenueName returns true if the name given does not exceed 255
 * characters and returns false if it does
 */
function validateVenueName($name) {
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


?>
