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
    $emailError = $passwordError = $accountExists = $ageError = $createError = '';

    try {
        /* If email, password, confirm password and dob are provided using the submit
         * form then start processing inputs (validation, assigning values to
         * variables etc.) */
        if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirmPassword']) && isset($_POST['DOB'])) {
            // Trim email to remove whitespaces at start or end
            $email = trim($_POST['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // The email address provided is invalid!
                $emailError = 'The email address entered is not valid!';
            } else {
                if (checkEmailExists($email,$pdo)) {
                    // Account already exists with email address entered!
                    $accountExists = 'An Account already exists with that email!';
                } else {
                    $accountExists = '';
                    // Account does not exist with email, continue with registration
                    $dob = $_POST['DOB'];

                    if (!checkValidAge($dob)) {
                        /* The date of birth given by the user is invalid
                         * (either they are under 18 or the date given is in
                         * the future)
                         */
                        $ageError = 'You must be over 18 to register an account!';
                    } else {
                        $ageError = '';
                        // The user is over 18, continue with registration
                        // Check passwords match
                        $password = $_POST['password'];
                        $confirmPassword = $_POST['confirmPassword'];
                        if ($password != $confirmPassword) {
                            // Passwords do not match!
                            $passwordError = 'Passwords do not match!';
                        } else {
                            $passwordError = '';
                            // Validate password
                            if (!validatePassword($password)) {
                                // The password is not valid
                                $passwordError = 'password must be at least 8 characters long and contain a lower case letter and a number!';
                            } else {
                                if (createUser($email,$password,$dob,$pdo)) {
                                    /* The verification email would be sent here but
                                     * as we do not have a working mail server this
                                     * will not work at the moment
                                     */
                                    // sendVerificationEmail($email,$hash);
                                    // Verification not working so set verified to true
                                    $_SESSION['verified'] = true;

                                    header('location: login.php');
                                    exit;
                                } else {
                                    $createError = 'Error creating new account, please try again later!';
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

        $dob18 = date_create($dob);
        $currentTime = date_create("now");

        if ($dob18 > $currentTime) {
            // dob given is in the future, return true
            return false;
        } else {
            $interval = date_diff($currentTime, $dob18);

            if ($interval->format("%y") <= "18") {
                // DOB provided means the user is under 18!
                return false;
            } else {
                return true;
            }
        }
    }

    /* The function validatePassword returns true if the password provided by
     * the user is valid according to validation rules: must be at least 8
     * characters, must contain at least 1 lower case letter and at least one
     * number
     */
    function validatePassword($password) {
        if ((strlen($password) >= 8) && (preg_match("/[a-z]/",$password)) && (preg_match("/[0-9]/",$password))) {
            return true;
        } else {
            return false;
        }
    }

    /* The function createUser returns true if the account has been created
     * successfully
     */
    function createUser($email,$password,$dob,$pdo) {
        // Verification hash is generated here using md5 and random numbers
        $hash = md5(rand(0,1000));
        // Create user in db
        $pdo->beginTransaction();
        $registerStmt = $pdo->prepare("INSERT INTO User (UserEmail,UserPass,UserDOB,VerifyHash) VALUES (:UserEmail,:UserPass,:UserDOB,:VerifyHash)");
        $registerStmt->bindValue(':UserEmail',$email);
        $registerStmt->bindValue(':UserPass',$password);
        $registerStmt->bindValue(':UserDOB',$dob);
        $registerStmt->bindValue(':VerifyHash',$hash);
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
    function sendVerificationEmail($email,$hash) {
        $to = $email;
        $subject = 'OutOut | Verify your account';
        $message = '

        Thank you for registering for OutOut!
        Please click the following link to verify your account:
        https://student.csc.liv.ac.uk/~sgstribe/test/verify.php?email='.$email.'&hash='.$hash.'

        You will be able to log in using the email: '.$email.' and the password you used in registration

        ';

        $headers = 'From:noreply@LiveproolOutOut.com' . "\r\n";
        mail($to,$subject,$message,$headers);
    }
?>

<!DOCTYPE html>
<html lang='en-GB'>
    <head>
        <title>OutOut - Register</title>
    </head>
    <body>
        <h1>OutOut - Register</h1>
        <form name='RegisterForm' method='post'>
            <label>Email:
                <input type='text' name='email'></label><br>
            <label>Password:
                <input type='password' name='password'></label><br>
            <label>Confirm Password:
                <input type='password' name='confirmPassword'></label><br>
            <label>Date of Birth:
                <input type='date' name='DOB' placeholder="select Date of Birth"></label><br>
        <input type='submit' value='Register'></form>
        <?php
        /* If the email entered is not valid then the user is dispalayed an
         * error message below
         */
        if ($emailError != '') {
            echo "$emailError<br>";
        }
        /* If the accountExists string is not blank then the error message is
         * displayed telling the user that an account already exists in the
         * database with the email they provided
         */
        if ($accountExists != '') {
            echo "$accountExists<br>";
        }
        /* If the age entered by the user is under 18 then ageError is set as an
         * error string which is displayed below
         */
        if ($ageError != '') {
            echo "$ageError<br>";
        }
        /* If there are any errors with the password (not matching or not valid)
         * then the error is displayed below
         */
        if ($passwordError != '') {
            echo "$passwordError<br>";
        }
        /* If there is an error in creating the account then the error message
         * is displayed below
         */
        if ($createError != '') {
            echo "$createError<br>";
        }
        ?>
        <p>Already have an Account? <a href="login.php">Log In</a>.</p>

    </body>
</html>
