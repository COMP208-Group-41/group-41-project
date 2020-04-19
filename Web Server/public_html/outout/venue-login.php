<?php
    // Starting session
    session_start();
    // If the venue user is already logged in then they are redirected to the homepage
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
        header("location: venue-user-edit.php");
        exit;
    }

    /* If the venue user has just registered then they will be redirected to this
     * page, show them a message saying that their account has been created
     * successfully
     */

     error_reporting( E_ALL );
     ini_set('display_errors', 1);
     ini_set('display_startup_errors', 1);

     $registeredMsg = '';
     $loginError = '';

    // Config file for connecting to the database is grabbed here
    require_once "config.php";

    try {
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = $_POST['email'];
            $password = $_POST['password'];
            /* The function findVenueUser is called and the result is assigned
             * to a variable to check if it was a valid user
             */
            $VenueUserID = findVenueUser($email,$pdo);
            if ($VenueUserID != 0) {
                if (verifyVenuePassword($VenueUserID,$password,$pdo)) {
                    /* The venue user is now logged in, the session variable
                     * logged in is set to true, and the session variable
                     * VenueUserID is assigned the value of the venue user's ID, the
                     * user is then redirected to the home page
                     */
                    $_SESSION["loggedin"] = true;
                    $_SESSION['VenueUserID'] = $VenueUserID;
                    header("location: venue-user-edit.php");
                    exit;
                } else {
                    // Password doesn't match!
                    $loginError = 'Email or Password incorrect!';
                }
            } else {
                /* If the user's details are not in the system or their account
                 * is not verified then their login attempt is unsuccessful
                 * and the message is shown to them
                 */
                $loginError = 'Email or Password incorrect!';
            }
        }

    } catch (PDOException $e) {
        /* If a PDO Execption is thrown then it is caught here and an
         * error page is shown, this needs to be made more user friendly
         * for the final page
         */
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

    /* The function findVenueUser checks if the account exists in the database
     * with the email and password, and returns the VenueUserID if the venue user
     * exists, or 0 if they do not (no UserID can be 0)
     */
    function findVenueUser($email,$pdo) {
        /* Try to find the venue user in the database using provided
         * email and password
         */
        $loginstmt = $pdo->prepare("SELECT VenueUserID FROM VenueUser WHERE VenueUserEmail=:VenueUserEmail");
        $loginstmt->bindValue(':VenueUserEmail',$_POST['email']);
        $loginstmt->execute();
        if ($loginstmt->rowCount() == 1) {
            $row = $loginstmt->fetch();
            return $row['VenueUserID'];
        } else {
            return 0;
        }
    }
?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <link rel="stylesheet" type="text/css" href="../css/login-register.css">
    <title>OutOut - Venue User Login</title>
</head>
<body>
<div class="wrapper">
    <div class="outout-wrapper" style="padding-bottom: 10px">
        <img src="../Assets/outout.svg" alt="OutOut">
    </div>
    <div class="form">
        <div style="padding-bottom: 8px; text-align: center">
            <b style="color: #e9e9e9; font-size: 24px">Venue Login</b>
        </div>
        <form name='LoginForm' method='post'>
            <div class="login-field">
                <input type='text' name='email' placeholder="Email">
                <input type='password' name='password' placeholder="Password">
            </div>
            <div style="display: flex">
                <input type='submit' value='Login' class="login-button">
                <a class="register-button" href="venue-register.php">Register</a>
            </div>
        </form>
    </div>
</div>
<?php
    // If the details are incorrect then error message is shown
    if ($loginError != '') {
        echo "<div class='error'>$loginError</div>";
    }
    if (isset($_SESSION['verified']) && $_SESSION['verified']) {
        echo "<div class='success'>Account created successfully, please log in</div>";
        unset($_SESSION['verified']);
    }
?>
</body>
</html>
