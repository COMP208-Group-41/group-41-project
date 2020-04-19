<?php

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: venue-user-login.php");
    exit;
    /* If the user is logged in but they are not a venue user then they are
     * redirected to home page
     */
} else if (isset($_SESSION["UserID"])) {
    header("location: user-dashboard.php");
    exit;
} else if (!isset($_SESSION["VenueUserID"])) {
    header("location: venue-user-login.php");
    exit;
}

// Config file is imported
require_once "config.php";

$venueUserID = $_SESSION["VenueUserID"];
$errorMessage = "";
$result = getVenueUserInfo($venueUserID, $pdo);
$name = $result['VenueUserName'];
$email = $result['VenueUserEmail'];
$external = $result['VenueUserExternal'];
$venues = getVenues($venueUserID, $pdo);


?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Venue User Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/dashboard.css">
</head>
<body>
<?php include "navbar.php" ?>
<?php
if (isset($_SESSION['message'])) {
    echo "<div class='success'>" . $_SESSION['message'] . "</div>";
    unset($_SESSION['message']);
}
?>
<div class="wrapper">
    <div class="container">
        <h1 class="title"><?php echo "$name"; ?> - Dashboard</h1>
        <div class="seperator">
            <h2 class="title">Account details</h2>
        </div>
        <table align="center" border="1px" style="width:600px">
            <tr>
                <th>Name</th>
                <td><?php echo "$name"; ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo "$email"; ?></td>
            </tr>
            <tr>
                <th>External Site</th>
                <td><?php echo "$external"; ?></td>
            </tr>
        </table>
        <button onclick="location.href='venue-user-edit.php';" class="button" style="width: 100%; margin-bottom: 16px">Edit Account Details</button>
        <div class="seperator" style="margin-top: 4px">

        </div>
        <h2 class="title">Registered Venues</h2>
        <div class="venuelist">
            <?php
            foreach ($venues as $row) {
                echo '<div class="venue">';
                echo '<div class="venue-image"></div>';
                echo '<div class="venue-name">'.$row['VenueName']."</div>";
                echo '<div class="venue-buttons"><a href="venue.php?venueID='.$row['VenueID'].'" class="venue-button" style="border-right-color: #C4C4C4">View Venue</a>';
                echo '<a href="venue-edit.php?venueID='.$row['VenueID'].'" class="venue-button" style="width: 50%">Edit Venue</a></div></div>';
            }
            ?>
        </div>
        <button onclick="location.href='venue-creation.php';" class="button" style="width: 100%; margin-bottom: 16px">Add a new Venue</button>
    </div>
</div>
</div>

</body>
</html>