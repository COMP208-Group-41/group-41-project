<?php

     // NOTE: Review-edit will take a ReviewID to specify which review we are
     // referencing
     // TODO: Choose location to redirect user to if they are trying to edit
     // someone else's review

    session_start();

    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
        /* If the user is logged in but they are a venue user then they are
         * redirected to home page
         */
    } else if (isset($_SESSION["VenueUserID"])) {
        header("location: venue-home.php");
        exit;
    }

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    $userID = $_SESSION['UserID'];
    $errorMessage = "";

    if (isset($_GET['reviewID'])) {
        $reviewID = $_GET['reviewID'];
    } else {
        // No ReviewID specified, redirect to 404 page
        $_SESSION['message'] = "No ID specified!";
        header("location: 404.php");
        exit;
    }

    // Get existing Review Info
    $result = getReviewInfo($reviewID,$pdo);
    if ($result['UserID'] != $userID) {
        // User is not allowed to edit someone elses page!
        // TODO: THIS IS THE LINK THAT NEEDS DECIDING
        $_SESSION['message'] = "You are not allowed to edit someone else's review!";
        header("location: home.php");
        exit;
    }
    $reviewText = $result['ReviewText'];
    $reviewPrice = $result['ReviewPrice'];
    $reviewAtmosphere = $result['ReviewAtmosphere'];
    $reviewSafety = $result['ReviewSafety'];
    $reviewQueue = $result['ReviewQueue'];
    $eventID = $result['EventID'];
    $venueID = $result['VenueID'];

    try{
        if (isset($_POST['SubmitReview'])){
          if (checkInputs($reviewID,$errorMessage,$pdo)) {
              $_SESSION['message'] = "Review Updated successfully!";

              if ($eventID == 1) {
                  header("location: venue.php?venueID=$venueID");
                  exit;
              } else {
                  header("location: event.php?eventID=$eventID");
                  exit;
              }
          }
        }
    } catch (PDOException $e) {
        // Any PDO errors are shown here
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

    function checkInputs($reviewID,&$errorMessage,$pdo){
        $reviewDate = date("Y-m-d");

        // Check review text
        $reviewText= trim($_POST['Review']);
        if (!validateDescription($reviewText)) {
            $errorMessage = "The review cannot be longer than 1000 characters!";
            return false;
        }

        // All numeric ratings are validated below
        $reviewPrice = ($_POST['RatingPrice']);
        if (!validationReviewScore($reviewPrice)) {
            $errorMessage = "Error! Review price out of boundries";
            return false;
        }
        $reviewSafety = ($_POST['RatingSafety']);
        if (!validationReviewScore($reviewSafety)) {
            $errorMessage = "Error! Review safety out of boundries";
            return false;
        }
        $reviewQueue = ($_POST['RatingQueue']);
        if (!validationReviewScore($reviewQueue)) {
            $errorMessage = "Error! Review queue out of boundries";
            return false;
        }
        $reviewAtmosphere = ($_POST['RatingAtmosphere']);
        if (!validationReviewScore($reviewAtmosphere)) {
            $errorMessage = "Error! Review atmosphere out of boundries";
            return false;
        }

        // Al valid, transaction attempted
        $pdo->beginTransaction();
        if (!updateReview($reviewID,$reviewDate,$reviewText,$reviewPrice,$reviewSafety,$reviewAtmosphere,$reviewQueue,$pdo)) {
            $errorMessage = "Error in editing review in database!";
            $pdo->rollBack();
            return false;
        }
        $pdo->commit();
        // Everything completed successfully, return true!
        return true;
    }

    /* getReviewInfo will also get the userID and check if the user is allowed
     * to edit this review (if the review doesn't belong to them then they can't
     * edit it)
     */
    function getReviewInfo($reviewID,$pdo) {
        $getReviewStmt = $pdo->prepare("SELECT VenueID, EventID, UserID, ReviewText, ReviewPrice, ReviewAtmosphere, ReviewSafety, ReviewQueue FROM Review WHERE ReviewID=:ReviewID");
        $getReviewStmt->bindValue(":ReviewID",$reviewID);
        $getReviewStmt->execute();
        return $getReviewStmt->fetch();
    }

    // Update the existing review in the database
    function updateReview($reviewID,$reviewDate,$reviewText,$reviewPrice,$reviewSafety,$reviewAtmosphere,$reviewQueue,$pdo) {
        $updateReviewStmt = $pdo->prepare("UPDATE Review SET ReviewDate=:ReviewDate, ReviewText=:ReviewText, ReviewPrice=:ReviewPrice, ReviewSafety=:ReviewSafety, ReviewAtmosphere=:ReviewAtmosphere, ReviewQueue=:ReviewQueue WHERE ReviewID=:ReviewID");
        $updateReviewStmt->bindValue(":ReviewDate",$reviewDate);
        $updateReviewStmt->bindValue(":ReviewText",$reviewText);
        $updateReviewStmt->bindValue(":ReviewPrice",$reviewPrice);
        $updateReviewStmt->bindValue(":ReviewSafety",$reviewSafety);
        $updateReviewStmt->bindValue(":ReviewAtmosphere",$reviewAtmosphere);
        $updateReviewStmt->bindValue(":ReviewQueue",$reviewQueue);
        $updateReviewStmt->bindValue(":ReviewID",$reviewID);
        if (!$updateReviewStmt->execute()) {
            // Error in update
            return false;
        } else {
            // Values updated successfully
            return true;
        }
    }

?>

<!DOCTYPE html>
<head>
    <title>OutOut - Edit Review</title>
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" title="text/css" href="../css/navbar.css">
    <link rel="stylesheet" type="text/css" href="../css/review.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
  <body>
    <?php include "navbar.php" ?>
    <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='message-wrapper'><div class='success'>".$_SESSION['message']."</div></div>";
            unset($_SESSION['message']);
        }
    ?>
    <div class="container" style="width: 20vw">
        <h2 class="title">Write a review</h2>
        <form name='ReviewVenue' method='post'>
            <div style="display: flex; justify-content: center; flex-direction: column">
          <label for='Review'>Review:</label>
          <textarea name='Review' id='Review' placeholder="Write your review here..." rows="4" cols="50"><?php echo $reviewText; ?></textarea>
                <div class="seperator"></div>
          <label for='RatingPrice'>Price:</label>
          <select name="RatingPrice" id="RatingPrice" required>
              <option <?php if ($reviewPrice == 5) echo 'selected' ?> value="5">5</option>
              <option <?php if ($reviewPrice == 4) echo 'selected' ?> value="4">4</option>
              <option <?php if ($reviewPrice == 3) echo 'selected' ?> value="3">3</option>
              <option <?php if ($reviewPrice == 2) echo 'selected' ?> value="2">2</option>
              <option <?php if ($reviewPrice == 1) echo 'selected' ?> value="1">1</option>
          </select>
          <label for='RatingAtmosphere'>Atmosphere:</label>
          <select name="RatingAtmosphere" id="RatingAtmosphere" required>
              <option <?php if ($reviewAtmosphere == 5) echo 'selected' ?> value="5">5</option>
              <option <?php if ($reviewAtmosphere == 4) echo 'selected' ?> value="4">4</option>
              <option <?php if ($reviewAtmosphere == 3) echo 'selected' ?> value="3">3</option>
              <option <?php if ($reviewAtmosphere == 2) echo 'selected' ?> value="2">2</option>
              <option <?php if ($reviewAtmosphere == 1) echo 'selected' ?> value="1">1</option>
          </select>
          <label for='RatingSafety'>Safety:</label>
          <select name="RatingSafety" id="RatingSafety" required>
              <option <?php if ($reviewSafety == 5) echo 'selected' ?> value="5">5</option>
              <option <?php if ($reviewSafety == 4) echo 'selected' ?> value="4">4</option>
              <option <?php if ($reviewSafety == 3) echo 'selected' ?> value="3">3</option>
              <option <?php if ($reviewSafety == 2) echo 'selected' ?> value="2">2</option>
              <option <?php if ($reviewSafety == 1) echo 'selected' ?> value="1">1</option>
          </select>
          <label for='RatingQueue'>Queue Times:</label>
          <select name="RatingQueue" id="RatingQueue" required>
              <option <?php if ($reviewQueue == 5) echo 'selected' ?> value="5">5</option>
              <option <?php if ($reviewQueue == 4) echo 'selected' ?> value="4">4</option>
              <option <?php if ($reviewQueue == 3) echo 'selected' ?> value="3">3</option>
              <option <?php if ($reviewQueue == 2) echo 'selected' ?> value="2">2</option>
              <option <?php if ($reviewQueue == 1) echo 'selected' ?> value="1">1</option>
          </select>
            </div>
            <div class="seperator" style="margin-top: 20px"></div>
            <div style= "display: flex">
                <input type='submit' name='SubmitReview' value='Submit'>
            </div>
    </form>
</div>
    <?php
    if ($errorMessage != "") {
        echo "<div class='message-wrapper'><div class='error'>$errorMessage</div></div>";
    }
    ?>
  </body>
</html>
