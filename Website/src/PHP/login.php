<?php
    // Starting session
    session_start();
    // If the user is already logged in then they are redirected to the homepage
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
        header("location: user-dashboard.php");
        exit;
    }

    /* If the user has just registered then they will be redirected to this
     * page, show them a message saying that their account has been created
     * successfully
     */

     $registeredMsg = '';
     $errorMessage = '';

    // Config file for connecting to the database is grabbed here
    require_once "config.php";

    try {
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = $_POST['email'];
            $password = $_POST['password'];
            /* The function findUser is called and the result is assigned
             * to a variable to check if it was a valid user
             */
            $result = findUser($email,$pdo);
            if ($result != 0) {
                if (verifyPassword($result,$password,$pdo)) {
                    /* The user is now logged in, the session variable
                     * logged in is set to true, and the session variable
                     * UserID is assigned the value of the user's ID, the
                     * user is then redirected to the home page
                     */
                    $_SESSION["loggedin"] = true;
                    $_SESSION['UserID'] = $result;
                    header("location: user-dashboard.php");
                    exit;
                } else {
                    // Password doesn't match!
                    $errorMessage = 'Email or Password incorrect!';
                }
            } else {
                /* If the user's details are not in the system or their account
                 * is not verified then their login attempt is unsuccessful
                 * and the message is shown to them
                 */
                $errorMessage = 'Email or Password incorrect!';
            }
        }
    } catch (PDOException $e) {
        /* If a PDO Execption is thrown then it is caught here and an
         * error page is shown, this needs to be made more user friendly
         * for the final page
         */
        exit("PDO Error: ".$e->getMessage()."<br>");
    }

?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <link rel="stylesheet" type="text/css" href="../css/login-register.css">
    <link rel="stylesheet" type="text/css" href="../css/navbar.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OutOut - Login</title>
</head>
<body>
    <?php include "navbar.php" ?>
    <div style="display: flex; height: 100%; justify-content: center; align-items: center">
        <div class="wrapper">
            <?php
                if (isset($_SESSION['message'])) {
                    echo "<div class='message-wrapper'><div class='success'>".$_SESSION['message']."</div></div>";
                    unset($_SESSION['message']);
                }
            ?>
            <div class="outout-wrapper">
                <img src="../Assets/outout.svg" alt="OutOut">
            </div>
            <div class="form">
                <div style="padding-bottom: 8px; text-align: center">
                    <b style="color: #e9e9e9; font-size: 24px">Login</b>
                </div>
                <form name='LoginForm' method='post'>
                    <div class="login-field">
                        <input type='text' name='email' placeholder="Email..">
                        <input type='password' name='password' placeholder="Password..">
                    </div>
                    <div style="display: flex">
                        <input type='submit' value='Login' class="login-button">
                        <a class="register-button" href="register.php">Register</a>
                    </div>
                </form>
            </div>
        </div>
        <?php
            // If the details are incorrect then error message is shown
            if ($errorMessage != '') {
                echo "<div class='message-wrapper'><div class='error'>$errorMessage</div></div>";
            }
        ?>
    </div>
    </body>
</html>
