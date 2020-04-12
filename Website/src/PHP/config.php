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

/* The function findVenueUser checks if the account exists in the database
 * with the email and password, and returns the VenueUserID if the venue user
 * exists, or 0 if they do not (no UserID can be 0)
 */
function findVenueUser($email,$pdo) {
    /* Try to find the venue user in the database using provided
     * email and password
     */
    $loginstmt = $pdo->prepare("SELECT VenueUserID FROM VenueUser WHERE VenueUserEmail=:VenueUserEmail");
    $loginstmt->bindValue(':VenueUserEmail',$_POST['email']);
    $loginstmt->execute();
    if ($loginstmt->rowCount() == 1) {
        $row = $loginstmt->fetch();
        return $row['VenueUserID'];
    } else {
        return 0;
    }
}

/* The verifyVenuePassword function returns true if the venue user's password is correct
 * using the password_verify function
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
