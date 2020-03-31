<?php
    session_start();

    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }

    require_once "config.php";

?>
<!DOCTYPE html>
<html lang='en-GB'>
    <head>
        <title>OutOut - Home</title>
    </head>
    <body>
        <h1>OutOut - Home</h1>
        <a href="logout.php" class="btn btn-danger">Logout of Account</a>
    </body>
</html>
