<?php
    /* This php file is for verifying the user's account, taking in the email
     * and hash as GET values for their account
     */
    session_start();

    require_once "config.php";

    $verificationError = '';
    /* If the email and hash values are provided in the url using GET then the
     * user's account will try to be verified
     */
    if(isset($_GET['email']) && !empty($_GET['email']) && isset($_GET['hash']) && !empty($_GET['hash'])) {
        $email = $_GET['email'];
        $hash = $_GET['hash'];
        try {
            $pdo->beginTransaction();
            $userID = getUserID($email,$hash,$pdo);
            if ($userID != 0) {
                if (updateVerifyAccount($userID,$pdo)) {
                    $pdo->commit();
                    $_SESSION['verified'] = true;
                    header("location: login.php");
                    exit;
                } else {
                    // Error in updating IsVerified!
                    $verificationError = 'Error verifying your account, the details are incorrect';
                    $pdo->rollBack();
                }
            } else {
                // Account being verified does not exist with details provided
                $verificationError = 'Error verifying your account, the details are incorrect';
                $pdo->rollBack();
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            exit("PDO Error: ".$e->getMessage()."<br>");
        }

    }

    function getUserID($email,$hash,$pdo) {
        $findAccountStmt = $pdo->prepare("SELECT UserID FROM User WHERE UserEmail=:email AND VerifyHash=:hash");
        $findAccountStmt->bindValue(':email',$email);
        $findAccountStmt->bindValue(':hash',$hash);
        $findAccountStmt->execute();
        if ($findAccountStmt->rowCount() == 1) {
            $row = $findAccountStmt->fetch();
            return $row['UserID'];
        } else {
            return 0;
        }
    }

    /* The function updateVerifyAccount tries to update the user's account in
     * the database to set the IsVerified variable to 1, if this is successful
     * then true is returned, otherwise false is returned
     */
    function updateVerifyAccount($userID,$pdo) {
        $updateAccountStmt = $pdo->prepare("UPDATE User SET IsVerified=1 WHERE UserID=:userID");
        $updateAccountStmt->bindValue(":userID",$userID);
        if ($updateAccountStmt->execute()) {
            // Account updated successfully
            return true;
        } else {
            return false;
        }
    }
?>
<!DOCTYPE html>
<html lang='en-GB'>
    <head>
        <title>OutOut - Verify Account</title>
    </head>
    <body>
        <h1>OutOut - Verify Account</h1>
        <?php
            if ($verificationError != '') {
                echo "$verificationError<br>";
            } else {
                echo '<p>Account verified successfully! You can now <a href="login.php">Log In</a>.</p>';
            }
        ?>
    </body>
</html>
