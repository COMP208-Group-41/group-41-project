<?php
    /* Notes on current register progress:
     * Need to implement DOB entry
     * Need to implement regex checking for username and password
     * Will need to format DOB in correct format for database
     */

    session_start();

    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        header("location: home.php");
        exit;
    }

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    $email = $password = $confirmPassword = $dob = '';

    $emailError = $passwordError = $accountExists = $timeError = '';

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
            // Account does not exist with email, continue with registration
            $dob = $_POST['DOB'];
            // Check age is over 18
            $dob18 = date_create($dob);
            $currentTime = date_create("now");
            $interval = date_diff($currentTime, $dob18);

            if ($interval->format("%y") <= "18") {
                // The user is under 18!
                $timeError = 'You must be over 18 to register for OutOut!';
            } else {
                // The user is over 18, continue with registration
                // Check passwords match
                $password = $_POST['password'];
                $confirmPassword = $_POST['confirmPassword'];
                if ($password != $confirmPassword) {
                    // Passwords do not match!
                    $passwordError = 'Passwords do not match!';
                } else {

                }
            }
        }
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
        if ($accountExists != '') {
            echo "$accountExists<br>";
        }
        echo $timeError;
        ?>
        <p>Already have an Account? <a href="register.php">Log In</a>.</p>

    </body>
</html>
