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

<div class="banner">
    <img src="../Assets/menu-icon.svg" alt="Menu" width="25" onclick="openNav()" class="menu-image">
    <img src="../Assets/outout.svg" alt="OutOut" width="115" onclick="location.href='home.php';">
    <img src="../Assets/profile.svg" alt="Profile" width="40" onclick="location.href='<?php echo $dashboardLink;?>';">
</div>
<div id="mySidenav" class="sidenav">
    <div class="sidenav-content">
        <div style="display: flex" >
            <img src="../Assets/outout.svg" width="115">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        </div>

        <a href="<?php echo $dashboardLink; ?>">My Account</a>
        <a href="<?php echo $accountLink; ?>">Edit Account</a>
        <a href="<?php echo $venueLink; ?>">All Venues</a>
        <a href="<?php echo $eventLink; ?>">Upcoming Events</a>
        <br>
        <?php
            if (!isset($_SESSION['UserID']) && !isset($_SESSION['VenueUserID'])) {
                echo '<a href="login.php">Login</a>';
                echo '<a href="register.php">Register</a>';
                echo '<br>';
                echo '<a href="venue-user-login.php">Venue Login</a>';
                echo '<a href="venue-user-register.php">Register Venue</a><br>';
            } else {
                echo '<a href="logout.php">Log Out</a><br>';
            }
        ?>
        <a href="<?php echo $aboutLink; ?>">About Us</a>
    </div>
</div>
<script>
    function openNav() {
        document.getElementById("mySidenav").style.width = "250px";
    }
    function closeNav() {
        document.getElementById("mySidenav").style.width = "0";
    }
</script>
