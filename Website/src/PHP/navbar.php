<?php
    // Links are for devel branch, need to adapt for master

    $path = "https://student.csc.liv.ac.uk/~sgstribe/test/php/dashboards/";

    if (isset($_SESSION['UserID'])) {
        $dashboardLink = $path."user-dash.php";
        $accountLink = $path."user-edit.php";
    }
    if (isset($_SESSION['VenueUserID'])) {
        $dashboardLink = $path."venue-user-dashboard.php";
        $accountLink = $path."venue-user-edit.php";
    }
    $eventLink = $path."events.php";
    $venueLink = $path."venues.php";
    if (!isset($_SESSION['UserID']) && !isset($_SESSION['VenueUserID'])) {

    }

?>

<div class="banner" >
    <img src="../Assets/menu-icon.svg" alt="Menu" width="25" onclick="openNav()" class="menu-image">
    <img src="../Assets/outout.svg" alt="OutOut" width="100">
    <img src="../Assets/profile.svg" alt="Profile" width="40" >
</div>
<div id="mySidenav" class="sidenav">
    <div class="sidebar-content">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="<?php echo $dashboardLink; ?>">Dashboard</a>
        <a href="<?php echo $venueLink; ?>">Venues</a>
        <a href="<?php echo $eventLink; ?>">Events</a>
        <a href="<?php echo $accountLink; ?>">Account</a>
        <?php
            if (!isset($_SESSION['UserID']) && !isset($_SESSION['VenueUserID'])) {
                echo '<a href="'$path.'login.php">Log In</a>';
            } else {
                echo '<a href="'.$path.'logout.php">Log Out</a>';
            }
        ?>
    </div>
</div>
<script>
    function openNav() {
        document.getElementById("mySidenav").style.width = "200px";
    }
    function closeNav() {
        document.getElementById("mySidenav").style.width = "0";
    }
</script>
