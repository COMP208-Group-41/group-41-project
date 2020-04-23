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

if (isset($_POST['venue']) && $_POST['venue'] != 'None') {
    $venueID = $_POST['venue'];
}

if (isset($venueID)){
  $venueNamestmt =  getVenueInfo($venueID,$pdo);
  $venueName = $venueNamestmt['VenueName'];
  $events = getEvents($venueID,$pdo);
}




?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title><?php echo "$name"; ?> - Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/dashboard.css">
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
        <h1 class="title"><?php echo "$name"; ?></h1>
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
          <h2 class="title">Registered Venues</h2>
          <div class="table">
            <?php
            foreach ($venues as $row) {
                echo '<div class="table-row">';
                echo '<div class="table-item">'.$row['VenueName']."</div>";
                echo '<div class="table-buttons"><a href="venue.php?venueID='.$row['VenueID'].'" class="table-button" style="margin-right: -1px">View Venue</a>';
                echo '<a href="venue-edit.php?venueID='.$row['VenueID'].'" class="table-button" style="width: 33%">Edit Venue</a></div></div>';
            }
            ?>
          </div>
          <button onclick="location.href='venue-creation.php';" class="button" style="width: 100%; margin-bottom: 16px">Add New Venue</button>
        </div>
        <div class="seperator"></div>
          <h2 class="title">Registered Events</h2>
          <div class="list">
              <form name='venueSelect' method='post'>
                  <select name='venue' id='venue' onChange='document.venueSelect.submit()' style="margin-bottom: 16px">
                      <option value='None'>Select Venue</option>
                      <?php echoVenues($venues); ?>
                  </select>
              </form>
            <?php
            if ($events !== false){
              echo '<div class="table-row">';
              echo '<div class="table-item" style="width: 35%">Venue</div>';
              echo '<div class="table-item" style="width: 35%">Event Name</div>';
              echo '<div class="table-item" style="width: 30%;">Action</div></div>';
              foreach ($events as $row) {
                echo '<div class="table-row">';
                echo '<div class="table-item" style="width: 35%">'.$venueName."</div>";
                echo '<div class="table-item" style="width: 35%">'.$row['EventName']."</div>";
                echo '<div class="table-buttons column" style="width: 30%"><a href="event.php?eventID='.$row['EventID'].'" class="table-button">View Event</a>';
                echo '<a href="event-edit.php?eventID='.$row['EventID'].'" class="table-button">Edit Event</a></div></div>';
              }
            }
            ?>
            <br>
            <button onclick="location.href='event-creation.php';" class="button" style="width: 100%; margin-bottom: 16px">Add New Event</button>
        </div>
    </div>
</div>
</div>
</body>
</html>
