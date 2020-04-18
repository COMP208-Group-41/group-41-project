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
    if (isset($_GET['EventID'])){
        $eventID = $_GET['EventID'];
        $getVenueID = eventIDToVenueID($eventID, $pdo);
        $venueID = $getVenueID['VenueID'];
    } elseif (isset($_GET['VenueID'])){
        $venueID = $_GET['VenueID'];
        $eventID = 1;
    } else {
        // If both unset ERROR as no venue or event exists under that name
        header("location: 404.php");
        exit;
    }

    try{
        if (isset($_POST['SubmitReview'])){
          checkInputs($userID,$eventID,$venueID,$errorMessage,$pdo);
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
?>

<!DOCTYPE html>
  <head>
    <title>OutOut - Submit Review</title>
    <link rel="stylesheet" type="text/css" href="../css/reviews.css">
  </head>
  <body>
    <form name='ReviewVenue' method='post'>
      <div>
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
    </form>
  </body>
</html>
