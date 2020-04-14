<?php

    session_start();

    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        header("location: home.php");
        exit;
    }

    error_reporting( E_ALL );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    require_once "config.php";

    $email = $password = $passwordConfirm = $name = "";
    $emailError = $passwordError = $accountExists = $nameError = $createError = '';

    try {
        if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirmPassword']) && isset($_POST['nameOfCompany'])) {
            $email = trim($_POST['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailError = "The email address is not valid!";
            }  else {
                if (checkVenueEmailExists($email,$pdo)) {
                    $accountExists = 'An Account already exists with that email!';
                } else {
                    $password = $_POST['password'];
                    $confirmPassword = $_POST['confirmPassword'];
                    if($password != $confirmPassword) {
                        $passwordError = 'Passwords do not match!';
                    } else {
                        if (!validatePassword($password)) {
                            $passwordError = 'password must be at least 8 characters long and contain a lower case letter and a number!';
                        } else {
                            $hashedPassword = passwordHasher($password);
                            $name = trim($_POST['nameOfCompany']);
                            if (!validateVenueName($name)) {
                                $nameError = 'Name of Company cannot be more than 255 characters!';
                            } else {
                                if (createUser($email,$hashedPassword,$name,$pdo)) {
                                    /* The verification email would be sent here but
                                     * as we do not have a working mail server this
                                     * will not work at the moment
                                     */

                                     // CREATE FOLDER IN PRIVATE_UPLOAD FOR IMAGES
                                     if (!createVenueUserFolder($email,$hashedPassword,$pdo)) {
                                         // ERROR!
                                         $createError = "Error creating user folder!";
                                     } else {
                                         // sendVerificationEmail($email,$hash);
                                         // Verification not working so set verified to true
                                         $_SESSION['verified'] = true;

                                         header('location: venue-login.php');
                                         exit;
                                     }

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
        $pdo->rollBack();
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

    function createUser($email,$password,$name,$pdo) {
        // Verification hash is generated here using md5 and random numbers

        // Create user in db
        $pdo->beginTransaction();
        $registerStmt = $pdo->prepare("INSERT INTO VenueUser (VenueUserEmail,VenueUserPass,VenueUserName) VALUES (:VenueUserEmail,:VenueUserPass,:VenueUserName)");
        $registerStmt->bindValue(':VenueUserEmail',$email);
        $registerStmt->bindValue(':VenueUserPass',$password);
        $registerStmt->bindValue(':VenueUserName',$name);
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

    function createVenueUserFolder($email,$pass,$pdo) {
        $getVenueIDStmt = $pdo->prepare("SELECT VenueUserID FROM VenueUser WHERE VenueUserEmail=:VenueUserEmail AND VenueUserPass=:VenueUserPass");
        $getVenueIDStmt->bindValue(':VenueUserEmail',$email);
        $getVenueIDStmt->bindValue(':VenueUserPass',$pass);
        $getVenueIDStmt->execute();
        $row = $getVenueIDStmt->fetch();
        $venueUserID = $row['VenueUserID'];

        $path = '/home/sgstribe/private_upload/' + $venueUserID;
        if (mkdir($path,0755)) {
            // Folder created successfully
            return true;
        } else {
            // Error in folder creation!
            return false;
        }
    }

?>

<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <link rel="stylesheet" type="text/css" href="../css/login-register.css">
    <title>OutOut - Venue Registration</title>
</head>
<body>
<div class="wrapper">
    <div class="outout-wrapper">
        <img src="../Assets/outout.svg" alt="OutOut">
    </div>
    <div class="form">
        <div style="padding-bottom: 8px; text-align: center">
            <b style="color: #e9e9e9; font-size: 24px">Venue Registration</b>
        </div>
        <form name='RegisterForm' method='post'>
            <div class="login-field">
                <input type='text' name='email' placeholder="Email">
                <input type='password' name='password' placeholder="Password">
                <input type='password' name='confirmPassword' placeholder="Confirm Password">
                <input type="text" name='nameOfCompany' placeholder="Name of Company">
            </div>
            <div style="display: flex">
                <a href="venue-login.php" class="login-button">Log In</a>
                <input type='submit' value='Register' class="register-button">
            </div>
        </form>
    </div>
</div>
<?php
/* If the email entered is not valid then the user is dispalayed an
 * error message below
 */
if ($emailError != '') {
    echo "<div class='error'>$emailError</div>";
}
/* If the accountExists string is not blank then the error message is
 * displayed telling the user that an account already exists in the
 * database with the email they provided
 */
if ($accountExists != '') {
    echo "<div class='error'>$accountExists</div>";
}

/* If there are any errors with the password (not matching or not valid)
 * then the error is displayed below
 */
if ($passwordError != '') {
    echo "<div class='error'>$passwordError</div>";
}

if ($nameError != '') {
    echo "<div class='error'>$nameError</div>";
}
/* If there is an error in creating the account then the error message
 * is displayed below
 */
if ($createError != '') {
    echo "<div class='error'>$createError</div>";
}
?>

</body>
</html>
