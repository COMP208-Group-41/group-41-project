<?php

  session_start();

    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
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
    $result = getVenueUserInfo($venueUserID,$pdo);
    $name = $result['VenueUserName'];
    $email = $result['VenueUserEmail'];
    $external = $result['VenueUserExternal'];




?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Venue User Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/venue-user-dashboard.css">
</head>
<body>
<h1>Account Details</h1>
<table align="center" border="1px" style="width:600px; line-height:40px;">
  <tr>
    <th>Name</th>
    <td><?php $name ?><td>
  </tr>
  <tr>
    <th>Email</th>
    <td><?php $name ?><td>
  </tr>
  <tr>
    <th>External Site</th>
    <td><?php $name ?><td>
  </tr>
</table>
<button onclick="location.href='venue-user-edit.php';" class="edit-account">Edit Account</button>
<h2>Venues</h2>
<button class="create-edit-venue">Create/Edit Venue</button>
<table align="center" border="1px" style="width:600px; line-height:40px;">
    <t>
      <th>Venue</th>
      <th>Upcoming Events</th>
      <td>
          <div class="dropdown">
              <button onclick="dropdown()" class="editbtn">Edit</button>
              <div id="venueOptions" class="dropdown-content">
                  <a href="#venue-page">View Venue</a>
                  <a href="#edit-venue">Edit/Delete Venue</a>
              </div>
          </div>
      </td>
</table>
<script>function dropdown() {
    document.getElementById("venueOptions").classList.toggle("show");
}
// Close the dropdown menu if the user clicks outside of it
window.onclick = function (event) {
    if (!event.target.matches('.editbtn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        var i;
        for (i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}
</script>
</body>
</html>
