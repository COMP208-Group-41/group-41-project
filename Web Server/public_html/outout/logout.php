<?php
    /* This php file ends the session and clears all session data, redirecting
     * the user back to the login page
     */
    // The session is started
    session_start();
    // All session variables are cleared
    $_SESSION = array();
    // The session is detroyed
    session_destroy();
    // The user is redirected to the login page
    header("location: login.php");
    exit;
?>
