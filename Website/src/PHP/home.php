<?php
    /* At the moment this is just a blank home page which only contains a
     * header and a logout link which executes logout.php
     * This entire page needs to be completed to match the design document
     * specifications
     */

    // Start the session
    session_start();
    /* if the user is not logged in (determined using session variables) then
     * they are redirected to the login page */
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }
    // The config file is imported here for any database connections required later
    require_once "config.php";

?>
