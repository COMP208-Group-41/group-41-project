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

    if (!isset($_SESSION['UserID'])) {
        $_SESSION['message'] = "You must be logged in to view recommended Events";
        header("location: login.php");
        exit;
    }

    require_once "config.php";

    $allEvents = getAllEvents($pdo);
    $userPrefs = getUserTags($userID,$pdo);
    $SortedArray = (array) null;
    foreach($allEvents as $row){
      $event = $emptyArray = (array) null;
      $eventTags = getEventTagID($row['EventID'],$pdo);
      $count = 0;
      foreach($userPrefs as $pref){
        if(in_array($pref, $eventTags)){
          $count++;
        }
      }
      if ($count > 0){
        $event['Count'] = $count;
        $event['EventID'] = $row['EventID'];
        array_push($SortedArray,$event);
      }
    }
    sortArray($SortedArray);

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
