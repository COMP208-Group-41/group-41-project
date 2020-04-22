<?php

    session_start();

    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    if (isset($_SESSION['VenueUserID'])) {
        $_SESSION['message'] = "Venue Users cannot use the recommended page";
        header("location: venue-user-dashboard.php");
        exit;
    }

    require_once "config.php";

    if (!isset($_SESSION['UserID'])) {
        $_SESSION['message'] = "You must be logged in to view recommended Venues";
        header("location: login.php");
        exit;
    }

    $userID = $_SESSION['UserID'];

    $allVenues = getAllVenues($pdo);
    print_r($allVenues);
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

    // Back up function in case above sort doesn't work (Found on web)
    /*
    usort($myArray, function($a, $b) {
      $retval = $a['order'] <=> $b['order'];
      if ($retval == 0) {
          $retval = $a['suborder'] <=> $b['suborder'];
          if ($retval == 0) {
            $retval = $a['details']['subsuborder'] <=> $b['details']['subsuborder'];
          }
      }
      return $retval;
    });
    */

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
              echo print_r($sortedArray);
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
