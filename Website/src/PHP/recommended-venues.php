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
    $sortedArray = (array) null;
    foreach($allVenues as $row){
      $venue = $emptyArray = (array) null;
      $venueTags = getVenueTagID($row['VenueID'],$pdo);
      $count = 0;
      foreach($userPrefs as $pref){
        if(in_array($pref, $venueTags)){
          $count++;
        }
      }
      if ($count > 0){
        $event['Count'] = $count;
        $event['VenueID'] = $row['VenueID'];
        array_push($sortedArray,$event);
      }
    }
    sortArray($sortedArray);

    function sortArray (&$array) {
      $temp=array();
      $ret=array();
      reset($array);
      foreach ($array as $index=> $value) {
          $temp[$index]=$value["Count"];
      }
      asort($temp);
      foreach ($temp as $index => $value) {
          $ret[$index]=$array[$index];
      }
      $array=$ret;
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
    <div class="container">
        <?php
          if (sizeof($sortedArray) != 0) {
              foreach($sortedArray as $row) {
                  echo '<div class="seperator" style="margin-top: 4px">';
                  $currentTagIDs = getVenueTagID($row['VenueID'],$pdo);
                  echo "<table>";
                  echo "<tr>";
                  echo "<td>".$row['VenueName']."</td>";
                  echo '<td><div class="venue-buttons"><a href="venue.php?venueID='.$row['VenueID'].'" class="venue-button" style="margin-left: -1px">View Venue</a>';
                  echo '<a href="upcoming-events.php?venueID='.$row['VenueID'].'" class="venue-button" style="margin-right: -1px">View Upcoming Events</a></div></td>';
                  echo '<td><div class="tag-container" style="text-align: center">'.getTagsNoEcho($currentTagIDs,$pdo).'</div></td>';
                  echo "</tr>";
                  echo "</table>";
              }
          } else {
            echo "<table>";
            echo "</tr><tr>";
            echo "<td>No Upcoming events for this Venue listed</td>";
            echo "</tr>";
            echo "</table>";
          }
        ?>
    </div>
  </div>
</body>
</html>
