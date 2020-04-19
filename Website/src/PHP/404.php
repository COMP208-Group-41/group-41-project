<?php
    session_start();

    require_once "config.php";
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Error 404 - Page Not Found!</title>
        <link rel="stylesheet" type="text/css" href="../css/404.css">
        <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    </head>
    <body>
        <?php include "navbar.php" ?>
        <?php
            if (isset($_SESSION['message'])) {
                echo "<div class='success'>".$_SESSION['message']."</div>";
                unset($_SESSION['message']);
            }
        ?>
        <h1 class='title'>Error 404 - Page Not Found!</h1>
        <p>The page you tried to access was not found!</p>
    </body>
</html>
