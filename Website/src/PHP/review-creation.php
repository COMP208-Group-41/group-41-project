<?php


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

    // Gets which event or venue the review is for
    if (isset($_GET['eventID'])){
        $eventID = $_GET['eventID'];
        $getVenueID = eventIDToVenueID($eventID, $pdo);
        $venueID = $getVenueID['VenueID'];
    } elseif (isset($_GET['venueID'])){
        $venueID = $_GET['venueID'];
        $eventID = 1;
    } else {
        // If both unset ERROR as no venue or event exists under that name
        $_SESSION['message'] = "No ID specified for review!";
        header("location: 404.php");
        exit;
    }

    if (!checkVenueEventExists($eventID,$venueID,$pdo)) {
        // Venue or Event does not exist! redirect to home and show error
        $_SESSION['message'] = "That Venue/Event does not exist!";
        header("location: home.php");
        exit;
    }

    // Need to check if specified venue or event exists


    /* Check if the user has already written a review for this Venue/Event, if
     * so then they are not allowed to write another one, redirect to the review
     * edit page
     */
     checkExistingReview($userID,$venueID,$eventID,$pdo);

    try{
        if (isset($_POST['SubmitReview'])){
          if (checkInputs($userID,$eventID,$venueID,$errorMessage,$pdo)) {
              // TODO: After the review is created successfully, redirect to
              // appropriate page (ideally showing success message or similar)
              $_SESSION['message'] = "Review Created Successfully!";
              header("location: home.php");
              exit;
          }
        }
    } catch (PDOException $e) {
        // Any PDO errors are shown here
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

    function checkInputs($userID,$eventID,$venueID,&$errorMessage,$pdo){
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
        if (!createReview($venueID,$eventID,$userID,$reviewDate,$reviewText,$reviewPrice,$reviewSafety,$reviewAtmosphere,$reviewQueue,$pdo)) {
            $errorMessage = "Error in inserting review into database!";
            $pdo->rollBack();
            return false;
        }
        $pdo->commit();
        // Everything completed successfully, return true!
        return true;
    }

    // Check if the user has already written a review for this venue/event
    function checkExistingReview($userID,$venueID,$eventID,$pdo) {
        $checkExistingReviewStmt = $pdo->prepare("SELECT ReviewID FROM Review WHERE UserID=:UserID AND VenueID=:VenueID AND EventID=:EventID");
        $checkExistingReviewStmt->bindValue(":UserID",$userID);
        $checkExistingReviewStmt->bindValue(":VenueID",$venueID);
        $checkExistingReviewStmt->bindValue(":EventID",$eventID);
        $checkExistingReviewStmt->execute();
        if ($checkExistingReviewStmt->rowCount() != 0) {
            // User already has a review for this venue/event!
            $result = $checkExistingReviewStmt->fetch();
            $reviewID = $result['ReviewID'];
            header("location: review-edit.php?ReviewID=$reviewID");
            exit;
        }
    }

    function eventIDToVenueID($eventID, $pdo){
        $getVenueStmt = $pdo->prepare("SELECT VenueID FROM Event WHERE EventID=:EventID");
        $getVenueStmt->bindValue(":EventID",$eventID);
        $getVenueStmt->execute();
        return $getVenueStmt->fetch();
    }

    function createReview($venueID,$eventID,$userID,$reviewDate,$reviewText,$reviewPrice,$reviewSafety,$reviewAtmosphere,$reviewQueue,$pdo){
        $createReviewStmt = $pdo->prepare("INSERT INTO Review (VenueID,EventID,UserID,ReviewDate,ReviewText,ReviewPrice,ReviewAtmosphere,ReviewSafety,ReviewQueue) VALUES (:VenueID,:EventID,:UserID,:ReviewDate,:ReviewText,:ReviewPrice,:ReviewAtmosphere,:ReviewSafety,:ReviewQueue)");
        $createReviewStmt->bindValue(":EventID",$eventID);
        $createReviewStmt->bindValue(":VenueID",$venueID);
        $createReviewStmt->bindValue(":UserID",$userID);
        $createReviewStmt->bindValue(":ReviewDate",$reviewDate);
        $createReviewStmt->bindValue(":ReviewText",$reviewText);
        $createReviewStmt->bindValue(":ReviewPrice",$reviewPrice);
        $createReviewStmt->bindValue(":ReviewSafety",$reviewSafety);
        $createReviewStmt->bindValue(":ReviewAtmosphere",$reviewAtmosphere);
        $createReviewStmt->bindValue(":ReviewQueue",$reviewQueue);
        if ($createReviewStmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    function checkVenueEventExists($eventID,$venueID,$pdo) {
        if ($eventID == 1) {
            // Just check venue exists
            $checkVenueExists = $pdo->prepare("SELECT VenueID FROM Venue WHERE VenueID=:VenueID");
            $checkVenueExists->bindValue(":VenueID",$venueID);
            $checkVenueExists->execute();
            if ($checkVenueExists->rowcount() == 0) {
                // Venue does not exist!
                return false;
            } else {
                // Venue does exist
                return true;
            }
        } else {
            // Check Event Exists
            $checkEventExists = $pdo("SELECT EventID FROM Event WHERE EventID=:EventID");
            $checkEventExists->bindValue(":EventID",$eventID);
            $checkEventExists->execute();
            if ($checkEventExists->rowcount() == 0) {
                // Venue does not exist!
                return false;
            } else {
                // Venue does exist
                return true;
            }
        }
    }
?>

<!DOCTYPE html>
<html>
  <head>
    <title>OutOut - Submit Review</title>
    <link rel="stylesheet" type="text/css" href="../css/reviews.css">
  </head>
  <body>
    <?php include "navbar.php" ?>
    <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='success'>".$_SESSION['message']."</div>";
            unset($_SESSION['message']);
        }
    ?>
    <form name='ReviewVenue' method='post'>
      <div>
          <label for='Review'>Review:</label>
          <textarea name='Review' id='Review' placeholder="Write your review here..." rows="4" cols="50"></textarea><br>
          <label for='RatingPrice'>Price:</label>
          <select name="RatingPrice" id="RatingPrice" required>
              <option value="5">5</option>
              <option value="4">4</option>
              <option value="3">3</option>
              <option value="2">2</option>
              <option value="1">1</option>
          </select><br>
          <label for='RatingAtmosphere'>Atmosphere:</label>
          <select name="RatingAtmosphere" id="RatingAtmosphere" required>
              <option value="5">5</option>
              <option value="4">4</option>
              <option value="3">3</option>
              <option value="2">2</option>
              <option value="1">1</option>
          </select><br>
          <label for='RatingSafety'>Safety:</label>
          <select name="RatingSafety" id="RatingSafety" required>
              <option value="5">5</option>
              <option value="4">4</option>
              <option value="3">3</option>
              <option value="2">2</option>
              <option value="1">1</option>
          </select><br>
          <label for='RatingQueue'>Queue Times:</label>
          <select name="RatingQueue" id="RatingQueue" required>
              <option value="5">5</option>
              <option value="4">4</option>
              <option value="3">3</option>
              <option value="2">2</option>
              <option value="1">1</option>
          </select><br>
      </div>

      <div style= "display: flex">
          <input type='submit' name='SubmitReview' value='Submit'>
      </div>
      <?php
          if ($errorMessage != "") {
              echo "<div class='error-wrapper'><div class='error'>$errorMessage</div></div>";
          }
       ?>
    </form>
  </body>
</html>
