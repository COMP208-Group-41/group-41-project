<?php

    session_start();

    if (isset($_SESSION['VenueUserID'])) {
        $_SESSION['message'] = "Venue Users cannot use the recommended page";
        header("location: venue-user-dashboard.php");
        exit;
    }

    if (!isset($_SESSION['UserID'])) {
        $_SESSION['message'] = "You must be logged in to view recommended Events";
        header("location: login.php");
        exit;
    }

    $allEvents = getAllEvents($pdo);
    $userPrefs = getUserTags($userID,$pdo);
    $nonSortedArray = (array) null;
    foreach($allEvents as $row){
      $event = $emptyArray = (array) null;
      $eventTags = getEventTagID($row['EventID'],$pdo);
      $count = 0;
      foreach($userPrefs as $pref){
        if(in_array($pref, $eventTags)){
          $count++
        }
      }
      if ($count > 0){
        array_push($event,$count,$row['EventID']);
        array_push($nonSortedArray,$event);
      }
    }

?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Recommended Events</title>
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
</head>
<body>
  <?php include "navbar.php" ?>
  <div class="wrapper">






  </div>
</body>
</html>
