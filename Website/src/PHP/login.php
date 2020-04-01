<?php
    // Starting session
    session_start();
    // If the user is already logged in then they are redirected to the homepage
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
        header("location: home.php");
        exit;
    }

    /* If the user has just registered then they will be redirected to this
     * page, show them a message saying that their account has been created
     * successfully
     */

     $registeredMsg = '';
     $loginError = '';

    if (isset($_SESSION['registered'])) {
        $registeredMsg = 'Your Account has been created successfuly, please log in with your account details';
    }

    // Config file for connecting to the database is grabbed here
    require_once "config.php";

    try {
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = $_POST['email'];
            $password = $_POST['password'];
            /* The function findUser is called and the result is assigned
             * to a variable to check if it was a valid user
             */
            $result = findUser($email,$password,$pdo);
            if ($result != 0) {
                /* The user is now logged in, the session variable
                 * logged in is set to true, and the session variable
                 * UserID is assigned the value of the user's ID, the
                 * user is then redirected to the home page
                 */
                $_SESSION["loggedin"] = true;
                $_SESSION['UserID'] = $result;
                header("location: home.php");
            } else {
                /* If the user's details are not in the system then
                 * their login attempt is unsuccessful and the message
                 * is shown to them
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

    /* The function findUser checks if the account exists in the database
     * with the email and password, and returns the UserID if the user
     * exists, or 0 if they do not (no UserID can be 0)
     */
    function findUser($email,$password,$pdo) {
        /* Try to find the user in the database using provided
         * username and password
         */
        $loginstmt = $pdo->prepare("SELECT UserID FROM User WHERE UserEmail=:UserEmail AND UserPass=:UserPassword");
        $loginstmt->bindValue(':UserEmail',$_POST['email']);
        $loginstmt->bindValue(':UserPassword',$_POST['password']);
        $loginstmt->execute();
        if ($loginstmt->rowCount() == 1) {
            $row = $loginstmt->fetch();
            return $row['UserID'];
        } else {
            return 0;
        }
    }
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
            // If the details are incorrect then error message is shown
            if ($loginError != '') {
                echo "$loginError<br>";
            }
        ?>
    </body>
</html>
