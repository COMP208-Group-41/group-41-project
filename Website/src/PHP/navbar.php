<?php

    if (isset($_SESSION['UserID'])) {
        $dashboardLink = "user-dashboard.php";
        $accountLink = "user-edit.php";
    }
    if (isset($_SESSION['VenueUserID'])) {
        $dashboardLink = "venue-user-dashboard.php";
        $accountLink = "venue-user-edit.php";
    }
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
      $dashboardLink = "login.php";
      $accountLink = "login.php";
    }
    $eventLink = "all-events.php";
    $venueLink = "all-venues.php";
    $aboutLink = "about.php";

?>

<head>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
</head>
<body>
<div class="banner">
    <a href="#" data-drawer-trigger aria-controls="drawer-name"  class="banner-image" aria-expanded="false"> <img src="../Assets/menu-icon.svg" alt="Menu" width="25" class="menu-image"></a>
    <img src="../Assets/outout.svg" alt="OutOut" width="100" class="hvr-grow" onclick="location.href='home.php';">
    <img src="../Assets/profile.svg" alt="Profile" width="40"  class="hvr-grow" onclick="location.href='<?php echo $dashboardLink;?>';">
</div>
<section class="drawer drawer--left" id="drawer-name" data-drawer-target>
    <div class="drawer__overlay" data-drawer-close tabindex="-1"></div>
    <div class="drawer__wrapper">
        <div class="drawer__header">
            <img src="../Assets/outout.svg" alt="OutOut" width="100" class="hvr-grow" style="margin-top: 8px" onclick="location.href='home.php';">
            <a href="javascript:void(0)" class="drawer__close" data-drawer-close aria-label="Close Drawer">&times;</a>
        </div>
        <div class="drawer__content">
            <a href="<?php echo $dashboardLink; ?>">My Account</a>
            <a href="<?php echo $accountLink; ?>">Edit Account</a>
            <a href="<?php echo $venueLink; ?>">All Venues</a>
            <a href="<?php echo $eventLink; ?>">Upcoming Events</a>
            <div class="seperator"></div>
            <?php
            if (!isset($_SESSION['UserID']) && !isset($_SESSION['VenueUserID'])) {
                echo '<a href="login.php">Login</a>';
                echo '<a href="register.php">Register</a>';
                echo '<div class="seperator"></div>';
                echo '<a href="venue-user-login.php">Venue Login</a>';
                echo '<a href="venue-user-register.php">Register Venue</a><div class="seperator"></div>';
            } else {
                echo '<a href="logout.php">Log Out</a><div class="eperator"></div>';
            }
            ?>
            <a href="<?php echo $aboutLink; ?>">About Us</a>
        </div>
    </div>
</section>
<script src="navbar.js"></script>
</body>

