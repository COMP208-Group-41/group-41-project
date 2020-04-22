<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
    /* If the user is logged in but they are not a venue user then they are
     * redirected to home page
     */
} else if (isset($_SESSION["VenueUserID"])) {
    header("location: venue-user-dashboard.php");
    exit;
} else if (!isset($_SESSION["UserID"])) {
    header("location: login.php");
    exit;
}

// Config file is imported
require_once "config.php";

$userID = $_SESSION["UserID"];
$errorMessage = "";
$result = getUserInfo($userID, $pdo);
$name = $result['UserName'];
$email = $result['UserEmail'];
$userDOB = $result['UserDOB'];
$userPrefs = getUserTags($userID, $pdo);
$interestedIn = getInterested($userID, $pdo);

function eventToVenueID($eventID, $pdo)
{
    $getVenuesStmt = $pdo->prepare("SELECT VenueID FROM Event WHERE EventID=:EventID");
    $getVenuesStmt->bindValue(":EventID", $eventID);
    $getVenuesStmt->execute();
    $result = $getVenuesStmt->fetch();
}

function eventIDtoName($eventID, $pdo)
{
    $getNameStmt = $pdo->prepare("SELECT EventName FROM Event WHERE EventID=:EventID");
    $getNameStmt->bindValue(':EventID', $eventID);
    $getNameStmt->execute();
    $result = $getNameStmt->fetch();
    return $result['EventName'];
}


?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - User Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
<?php include "navbar.php" ?>
<?php
if (isset($_SESSION['message'])) {
    echo "<div class='message-wrapper'><div class='success'>" . $_SESSION['message'] . "</div></div>";
    unset($_SESSION['message']);
}
?>
<div class='container'>
    <div class="section">
        <h1 class="title">Account Details</h1>
        <div class="table">
            <div class="table-row">
                <div class="table-item" style="width: 30%">
                    User Name
                </div>
                <div class="table-item" style="width: 70%">
                    <?php echo "$name"; ?>
                </div>
            </div>
            <div class="table-row">
                <div class="table-item" style="width: 30%">
                    Email
                </div>
                <div class="table-item" style="width: 70%">
                    <?php echo "$email"; ?>
                </div>
            </div>
            <div class="table-row">
                <div class="table-item" style="width: 30%">
                    Date of Birth
                </div>
                <div class="table-item" style="width: 70%">
                    <?php echo "$userDOB"; ?>
                </div>
            </div>
        </div>
        <button onclick="location.href='user-edit.php';" class="button" style="margin-top: 20px;width: 100%">
            Edit Account Details
        </button>
    </div>
    <div class="flex-wrap">
        <div class="section">
            <h1 class="title">Favourite Tags</h1>
            <div style="display: flex; justify-content: center">
            <div class="tag-container" style="text-align: center">
                <?php getTags($userPrefs, $pdo); ?>
            </div>
            </div>
        </div>
        <div class="section">
            <h1 class="title">Followed Events</h1>
                    <?php
                    if (count($interestedIn) > 0) {
                        echo '<div class="table">';
                        foreach ($interestedIn as $row) {
                            echo '<div class="table-row">';
                            echo '<div class="table-item">' . eventIDtoName($row['EventID'], $pdo) . '</div>';
                            echo '<div class="table-buttons"><a href="event.php?eventID=' . $row['EventID'] . '" class="table-button">View Event</a>';
                            echo "</div></div>";
                        }
                        echo '</div></div>';
                    } else {
                        echo "<h3 class='title'>No upcoming events found</h3>";
                    }
                    ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
