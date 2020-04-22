<?php


    session_start();

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    $search = $_GET['search'];
    $allVenues = getAllVenues($pdo);
    // EXPRESSION TO FILTER NEEDED HERE

?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Matching Venues</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/all-venues.css">
</head>
<body>
    <?php include "navbar.php" ?>
    <?php
    if (isset($_SESSION['message'])) {
        echo "<div class='message-wrapper'><div class='success'>" . $_SESSION['message'] . "</div></div>";
        unset($_SESSION['message']);
    }
    ?>
    <div class="wrapper">
        <div class="container">
            <div class="section">
                <h1 class='title'>Matching Venues</h1>
                <?php
                
                ?>
            </div>
        </div>
    </div>

</body>
</html>
