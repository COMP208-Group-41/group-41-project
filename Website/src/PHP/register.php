<?php
    /* Notes on current register progress:
     *
     * Have implemented validation for email and password, and organised code
     * into functions for easy reading
     * As email verification seems to not be possible, I will change the
     * default value of IsVerified in the database to be 1 so all accounts
     * are verified
     * Samuel tribe, 01/04/2020
     */

    // Session is started
    session_start();

    /* If the user is already logged in (determined using Session variables)
     * then they are redirected to the homepage where they are already logged
     * in
     */
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        header("location: home.php");
        exit;
    }
    // Optional error reporting below commented out
    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    // config file imported here
    require_once "config.php";

    /* All variables needed for registration delcared here as empty string,
     * Error messages are also declared here */
    $email = $password = $confirmPassword = $dob = '';
    $errorMessage = '';

    try {
        /* If email, password, confirm password and dob are provided using the submit
         * form then start processing inputs (validation, assigning values to
         * variables etc.) */
        if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirmPassword']) && isset($_POST['DOB'] && isset($_POST['username']))) {
            // Trim email to remove whitespaces at start or end
            $email = trim($_POST['email']);
            $username = trim($_POST['username']);
            if ($_POST['username'] = "" || !isset($_POST['username'])){
              $errorMessage = 'The username entered is invalid';
            }
            else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // The email address provided is invalid!
                $errorMessage = 'The email address entered is not valid!';
            } else {
                if (checkEmailExists($email,$pdo)) {
                    // Account already exists with email address entered!
                    $errorMessage = 'An Account already exists with that email!';
                } elseif (checkUsernameExists($username, $pdo)) {
                  // Account already exists with username address entered!
                    $errorMessage = 'An Account already exists with that username!';
                } elseif (!validate255($username)) {
                  // Account username too long
                    $errorMessage = 'Username too long!';
                } else {
                    // Account does not exist with email, continue with registration
                    $dob = $_POST['DOB'];

                    if (!checkValidAge($dob)) {
                        /* The date of birth given by the user is invalid
                         * (either they are under 18 or the date given is in
                         * the future)
                         */
                        $errorMessage = 'Either your age is under 18 or the format of the date of birth was wrong, please match the format dd-mm-yyyy!';
                    } else {
                        // The user is over 18, continue with registration
                        // Check passwords match
                        $password = $_POST['password'];
                        $confirmPassword = $_POST['confirmPassword'];
                        if ($password != $confirmPassword) {
                            // Passwords do not match!
                            $errorMessage = 'Passwords do not match!';
                        } else {
                            // Validate password
                            if (!validatePassword($password)) {
                                // The password is not valid
                                $errorMessage = 'password must be at least 8 characters long and contain a lower case letter and a number!';
                            } else {
                                // The password is hashed
                                $hashedPassword = passwordHasher($password);
                                if (createUser($username,$email,$hashedPassword,$dob,$pdo)) {
                                    /* The verification email would be sent here but
                                     * as we do not have a working mail server this
                                     * will not work at the moment
                                     */
                                    // sendVerificationEmail($email,$hash);
                                    $_SESSION['message'] = "Your account has been created successfully!";
                                    header('location: login.php');
                                    exit;
                                } else {
                                    $errorMessage = 'Error creating new account, please try again later!';
                                }
                            }
                        }
                    }
                }
            }
        }
    } catch (PDOException $e) {
        /* If a PDO Exception is thrown then rollback any changes to database
         * and display error, this will need to be made more user friendly later
         * on */
        $pdo->rollback();
        exit("PDO Error: ".$e->getMessage()."<br>");
    }


    /* The function checkUsernameExists returns true if the username provided already
     * exists in the User database table
     */
    function checkUsernameExists($username,$pdo) {
        // Register form has been filled out and submitted, check if username already exists in db
        $checkExistingStmt = $pdo->prepare("SELECT UserName FROM User WHERE UserName=:UserName");
        $checkExistingStmt->bindValue(':UserName',$username);
        $checkExistingStmt->execute();
        if ($checkExistingStmt->rowCount() > 0) {
            // Username exists, return true
            return true;
        } else {
            return false;
        }
    }


    /* The function checkEmailExists returns true if the email provided already
     * exists in the User database table
     */
    function checkEmailExists($email,$pdo) {
        // Register form has been filled out and submitted, check if email already exists in db
        $checkExistingStmt = $pdo->prepare("SELECT UserEmail FROM User WHERE UserEmail=:UserEmail");
        $checkExistingStmt->bindValue(':UserEmail',$email);
        $checkExistingStmt->execute();
        if ($checkExistingStmt->rowCount() > 0) {
            // Email exists, return true
            return true;
        } else {
            return false;
        }
    }

    /* The function checkValidAge returns false if the date of birth given by the
     * user means they are under 18 or the date is in the future
     */
    function checkValidAge($dob) {


        // First check the date isn't in the future
        try {
            $dobTimestamp = strtotime($dob);
            $mysqlDob = date("Y-m-d",$dobTimestamp);
            /* If the dob entered was invalid then the strtotime and date
             * conversions above will throw an exception, otherwise they are
             * valid
             */
            $bday = new DateTime($dob);
            $bday->add(new DateInterval("P18Y"));

            if ($bday > new DateTime("now")) {
                // user is under 18
                return false;
            } else {
                // user is over 18
                return true;
            }
        } catch (Exception $e) {
            return false;
        }

    }

    /* The function createUser returns true if the account has been created
     * successfully
     */
    function createUser($username,$email,$password,$dob,$pdo) {
        // Verification hash is generated here using md5 and random numbers

        // Create user in db
        $pdo->beginTransaction();
        $registerStmt = $pdo->prepare("INSERT INTO User (UserName,UserEmail,UserPass,UserDOB) VALUES (:UserName,:UserEmail,:UserPass,:UserDOB)");
        $registerStmt->bindValue(':UserName',$username);
        $registerStmt->bindValue(':UserEmail',$email);
        $registerStmt->bindValue(':UserPass',$password);
        $registerStmt->bindValue(':UserDOB',$dob);

        if ($registerStmt->execute()) {
            // if statement executes successfully, redirect to login page
            $pdo->commit();
            return true;
        } else {
            // Error in creating account in db!
            $pdo->rollBack();
            return false;
        }
    }

    /* The function sendVerificationEmail sends a verification to
     * the email provided by the user, it contains a link to
     * verify their account
     */
    // function sendVerificationEmail($email,$hash) {
    //     $to = $email;
    //     $subject = 'OutOut | Verify your account';
    //     $message = '
    //
    //     Thank you for registering for OutOut!
    //     Please click the following link to verify your account:
    //     https://student.csc.liv.ac.uk/~sgstribe/test/verify.php?email='.$email.'&hash='.$hash.'
    //
    //     You will be able to log in using the email: '.$email.' and the password you used in registration
    //
    //     ';
    //
    //     $headers = 'From:noreply@LiveproolOutOut.com' . "\r\n";
    //     mail($to,$subject,$message,$headers);
    // }
?>

<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <link rel="stylesheet" type="text/css" href="../css/login-register.css">
</head>
<body>
<div class="wrapper">
    <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='success'>".$_SESSION['message']."</div>";
            unset($_SESSION['message']);
        }
    ?>
    <div class="outout-wrapper">
        <img src="../Assets/outout.svg" alt="OutOut">
    </div>
    <div class="form">
        <form name='RegisterForm' method='post'>
            <div class="login-field">
                <input type='text' name='username' placeholder="Username">
                <input type='text' name='email' placeholder="Email">
                <input type='password' name='password' placeholder="Password">
                <input type='password' name='confirmPassword' placeholder="Confirm Password">
                <input type='date' name='DOB' placeholder="Select Date of Birth">
            </div>
            <div style="display: flex">
                <a href="login.php" class="login-button">Log In</a>
                <input type='submit' value='Register' class="register-button">
            </div>
        </form>
    </div>
</div>
        <?php
        if ($errorMessage != '') {
            echo "<div class='error-wrapper'><div class='error'>$errorMessage</div></div>";
        }
        ?>
    </body>
</html>
