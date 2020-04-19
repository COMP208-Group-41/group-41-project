<?php
    // Links are for devel branch, need to adapt for master
    if (isset($_SESSION['UserID'])) {
        $dashboardLink = "https://student.csc.liv.ac.uk/~sgstribe/test/php/dashboards/user-dash.php";
        $accountLink = "https://student.csc.liv.ac.uk/~sgstribe/test/php/dashboards/user-edit.php";
    }
    if (isset($_SESSION['VenueUserID'])) {
        $dashboardLink = "https://student.csc.liv.ac.uk/~sgstribe/test/php/dashboards/venue-user-dashboard.php";
        $accountLink = "https://student.csc.liv.ac.uk/~sgstribe/test/php/dashboards/venue-user-edit.php"
    }

?>

<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Edit Venue User Account</title>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
</head>
<body>
<div class="banner" >
    <img src="../Assets/menu-icon.svg" alt="Menu" width="25" onclick="openNav()" class="menu-image">
    <img src="../Assets/outout.svg" alt="OutOut" width="100">
    <img src="../Assets/profile.svg" alt="Profile" width="40" >
</div>
<div id="mySidenav" class="sidenav">
    <div class="sidebar-content">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="<?php echo $dashboardLink; ?>">Dashboard</a>
        <a href="<?php echo ; ?>">Venues</a>
        <a href="<?php echo $accountLink; ?>">Account</a>
        <a href="<?php echo $dashboardLink; ?>">Contact</a>
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
</body>
