<?php

    session_start();

    if (isset($_SESSION['VenueUserID'])) {
        $_SESSION['message'] = "Venue Users cannot use the recommended page";
        header("location: venue-user-dashboard.php");
        exit;
    }

    if (!isset($_SESSION['UserID'])) {
        $_SESSION['message'] = "You must be logged in to view recommended Venues";
        header("location: login.php");
        exit;
    }

    $userID = $_SESSION['UserID'];

    $allVenues = getAllVenues($pdo);
    $userPrefs = getUserTags($userID,$pdo);
    $nonSortedArray = (array) null;
    foreach($allVenues as $row){
      $venue = $emptyArray = (array) null;
      $venueTags = getVenueTagID($row['VenueID'],$pdo);
      $count = 0;
      foreach($userPrefs as $pref){
        if(in_array($pref, $venueTags)){
          $count++
        }
      }
      if ($count > 0){
        array_push($venue,$count,$row['VenueID']);
        array_push($nonSortedArray,$venue);
      }
    }

?>
<!DOCTYPE html>
<html lang='en-GB'>

<head>
    <title>OutOut - Recommended Venues</title>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
</head>
<body>
  <?php include "navbar.php" ?>
  <div class="wrapper">






  </div>
</body>
</html>
