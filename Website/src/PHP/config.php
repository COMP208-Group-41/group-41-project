<?php
/* Config file for database connection, all other pages use require_once at the
 * start of the file and reference this config file
 * Test for CI/CD workflow */
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
?>
