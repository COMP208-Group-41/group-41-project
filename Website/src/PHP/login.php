<?php
    // Starting session
    session_start();
    // If the user is already logged in then they are redirected to the homepage
    if(isset($_SESSION["loggedin"]) && $_SESSION === true) {
        header("location: home.php");
        exit;
    }

    /* If the user has just registered then they will be redirected to this
     * page, show them a message saying that their account has been created
     * successfully
     */

     $registeredMsg = '';

    if (isset($_SESSION['registered'])) {
        $registeredMsg = 'Your Account has been created successfuly, please log in with your account details';
    }

    // Config file for connecting to the database is grabbed here
    require_once "config.php";
?>
<!DOCTYPE html>
<html lang='en-GB'>
    <head>
        <title>OutOut - Log In</title>
    </head>
    <body>
        <h1>Log In</h1>
        <?php
        // If the user just registered then their success message is shown here
            if ($registeredMsg != '') {
                echo "$registeredMsg<br>";
            }
        ?>
        <form name='LoginForm' method='post'>
            <label>Email:
                <input type='text' name='email'></label><br>
            <label>Password:
                <input type='password' name='password'></label><br>
        <input type='submit' value='Login'></form>
        <p>Don't have an account? <a href="register.php">Create an Account</a>.</p>
        <?php
            try {
                if (isset($_POST['email']) && isset($_POST['password'])) {
                    /* Try to find the user in the database using provided
                     * username and password
                     */
                    $loginstmt = $pdo->prepare("SELECT UserID FROM User WHERE UserEmail=:UserEmail AND UserPass=:UserPassword");
                    $loginstmt->bindValue(':UserEmail',$_POST['email']);
                    $loginstmt->bindValue(':UserPassword',$_POST['password']);
                    $loginstmt->execute();
                    if ($loginstmt->rowCount() == 1) {
                        /* The user is now logged in, the session variable is set
                         * and the homepage is shown using javascript
                         */
                        $_SESSION["loggedin"] = true;
                        echo '<script type="text/javascript">
                                  window.location="https://student.csc.liv.ac.uk/~sgstribe/OutOut/home.php"
                              </script>';
                    } else {
                        /* If the user's details are not in the system then
                         * their login attempt is unsuccessful and the message
                         * is shown to them
                         */
                        echo 'Email or Password Incorrect!<br>';
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
    </body>
</html>
