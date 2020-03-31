<?php
    /* Notes on current register progress:
     *
     * Need to implement regex checking for email and password
     * Samuel tribe, 31/03/2020
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
    // error_reporting( E_ALL );
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);

    // config file imported here
    require_once "config.php";

    /* All variables needed for registration delcared here as empty string,
     * Error messages are also declared here */
    $email = $password = $confirmPassword = $dob = '';
    $emailError = $passwordError = $accountExists = $ageError = '';

    try {
        /* If email, password, confirm password and dob are provided using the submit
         * form then start processing inputs (validation, assigning values to
         * variables etc.) */
        if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirmPassword']) && isset($_POST['DOB'])) {
            // Trim email to remove whitespaces at start or end
            $email = trim($_POST['email']);
            // Register form has been filled out and submitted, check if email already exists in db
            $checkExistingStmt = $pdo->prepare("SELECT UserEmail FROM User WHERE UserEmail=:UserEmail");
            $checkExistingStmt->bindValue(':UserEmail',$email);
            $checkExistingStmt->execute();
            if ($checkExistingStmt->rowCount() > 0) {
                // Account already exists with email address entered!
                $accountExists = 'An Account already exists with that email!';
            } else {
                $accountExists = '';
                // Account does not exist with email, continue with registration
                $dob = $_POST['DOB'];
                // Check age is over 18
                $dob18 = date_create($dob);
                $currentTime = date_create("now");
                $interval = date_diff($currentTime, $dob18);

                if ($interval->format("%y") <= "18") {
                    // The user is under 18!
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
                        // In final implementation, do validation on password here

                        // Create user in db
                        $pdo->beginTransaction();
                        $registerStmt = $pdo->prepare("INSERT INTO User (UserEmail,UserPass,UserDOB,IsAdmin) VALUES (:UserEmail,:UserPass,:UserDOB,:IsAdmin)");
                        $registerStmt->bindValue(':UserEmail',$email);
                        $registerStmt->bindValue(':UserPass',$password);
                        $registerStmt->bindValue(':UserDOB',$dob);
                        $registerStmt->bindValue(':IsAdmin', 0);
                        if ($registerStmt->execute()) {
                            // if statement executes successfully, redirect to login page
                            $pdo->commit();
                            /* Session variable registered is set to true to display
                             * message on login page telling user their account has
                             * been created successfully */
                            $_SESSION['registered'] = true;
                            header('location: login.php');
                            die();
                        } else {
                            // Error in creating account in db!
                            $pdo->rollBack();
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
        /* If the accountExists string is not blank then the error message is
         * displayed telling the user that an account already exists in the
         * database with the email they provided */
        if ($accountExists != '') {
            echo "$accountExists<br>";
        }
        /* If the age entered by the user is under 18 then ageError is set as an
         * error string which is displayed below */
        if ($ageError != '') {
            echo "$ageError<br>";
        }
        ?>
        <p>Already have an Account? <a href="login.php">Log In</a>.</p>

    </body>
</html>
