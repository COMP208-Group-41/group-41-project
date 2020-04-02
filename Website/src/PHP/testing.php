<!DOCTYPE html>
<html lang='en-GB'>
    <head>
        <title>OutOut - Register</title>
    </head>
    <body>
        <h1>OutOut - Register</h1>
        <?php
            $bday = new DateTime("03-04-2002");
            $bday->add(new DateInterval("P18Y"));

            if ($bday > new DateTime("now")) {
                echo "user is under 18<br>";
            } else {
                echo "user is over 18<br>";
            }

        ?>
    </body>
</html>
