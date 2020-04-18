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
                    header("location: home.php");
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

    /* The function findUser checks if the account exists in the database
     * with the email and password, and returns the UserID if the user
     * exists, or 0 if they do not (no UserID can be 0)
     */
    function findUser($email,$pdo) {
        /* Try to find the user in the database using provided
         * username and password
         */
        $loginstmt = $pdo->prepare("SELECT UserID FROM User WHERE UserEmail=:UserEmail");
        $loginstmt->bindValue(":UserEmail",$email);
        $loginstmt->execute();
        if ($loginstmt->rowCount() == 1) {
            $row = $loginstmt->fetch();
            return $row['UserID'];
        } else {
            return 0;
        }
    }

    /* The verifyPassword function returns true if the user's password is correct
     * using the password_verify function
     */
    function verifyPassword($UserID,$password,$pdo) {
        $checkPasswordStmt = $pdo->prepare("SELECT UserPass FROM User WHERE UserID=:UserID");
        $checkPasswordStmt->bindValue(':UserID',$UserID);
        $checkPasswordStmt->execute();
        $row = $checkPasswordStmt->fetch();
        /* If the password is verified then return true */
        if (password_verify($password,$row['UserPass'])) {
            return true;
        } else {
            return false;
        }
    }
?>
<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <link rel="stylesheet" type="text/css" href="../css/login-register.css">
</head>
<body>
        <?php
            // If the user just registered then their success message is shown here
            if ($registeredMsg != '') {
                echo "$registeredMsg<br>";
            }
        ?>
        <div class="wrapper">
            <div class="outout-wrapper">
                <img src="../Assets/outout.svg" alt="OutOut">
            </div>
            <div class="form">
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
            if ($loginError != '') {
                echo "<div class='error'>$loginError</div>";
            }

            if (isset($_SESSION['message'])) {
                echo "<div class='success'>".$_SESSION['message']."</div>";
                unset($_SESSION['message']);
            }
        ?>
    </body>
</html>
