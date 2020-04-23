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

    if (isset($_POST['search'])) {
      header("location: search.php?search=".$_POST['searchText']."");
      exit;
    }
?>

<head>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
</head>
<body>
<div class="banner">
    <a href="#" data-drawer-trigger aria-controls="drawer-name"  class="banner-image" aria-expanded="false"> <img src="../Assets/menu-icon.svg" alt="Menu" width="25" class="menu-image"></a>
    <img src="../Assets/outout.svg" alt="OutOut" width="100" class="hvr-grow outout" onclick="location.href='home.php';">
    <div class="search-form">
        <form name='search form' method='post' class="form">
            <div class="search-wrapper">
                <input type='text' name='searchText' placeholder="Search..." class="searchbar">
                <input type='submit' name='search' value='Search' class='button search-button'>
            </div>

        </form>
    </div>
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
            <div class="nav-seperator"></div>
            <?php
            if (isset($_SESSION['UserID'])) {
                echo '<a href="recommended-venues.php" style="line-height: 28px">Recommended Venues</a>';
                echo '<div class="nav-seperator"></div>';
                echo '<a href="recommended-events.php" style="line-height: 28px">Recommended Events</a>';
                echo '<div class="nav-seperator"></div>';
            }
            ?>
            <a href="<?php echo $venueLink; ?>">All Venues</a>
            <a href="<?php echo $eventLink; ?>">Upcoming Events</a>
            <div class="nav-seperator"></div>
            <?php
            if (!isset($_SESSION['UserID']) && !isset($_SESSION['VenueUserID'])) {
                echo '<a href="login.php">Login</a>';
                echo '<a href="register.php">Register</a>';
                echo '<div class="nav-seperator"></div>';
                echo '<a href="venue-user-login.php">Venue Login</a>';
                echo '<a href="venue-user-register.php">Register Venue User</a><div class="nav-seperator"></div>';
            } else {
                echo '<a href="logout.php">Log Out</a><div class="nav-seperator"></div>';
            }
            ?>
            <a href="<?php echo $aboutLink; ?>">About Us</a>
        </div>
    </div>
</section>
<script src="navbar.js"></script>
</body>
