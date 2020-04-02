<!DOCTYPE html>
<html lang='en-GB'>
    <head>
        <title>OutOut - password testing</title>
    </head>
    <body>
        <h1>OutOut - password testing</h1>
        <?php
            $password="password1";
            $hashed= password_hash($password, PASSWORD_DEFAULT);
            echo "$hashed<br>";

            if (password_verify($password,$hashed)) {
                echo "password correct!<br>";
            } else {
                echo "password incorrect!<br>";
            }
        ?>
    </body>
</html>
