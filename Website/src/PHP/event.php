<?php

    session_start();

?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
</head>
<body>
<div class="wrapper">
    <div class="container">
        <div style="display: flex; flex-direction: column">
            <h1 class="title">Event name here</h1>
            <label>Image:</label>
            <img src="../Assets/event-image.jpg">
            <div class="seperator"></div>
            <label>Location:</label>
            <label>Location here</label>

            <label>Event time:</label>
            <label>Time here:</label>
            <label>Event description:</label>
            <textarea readonly placeholder="Description of event here"></textarea>


        </div>
    </div>
</div>
</body>
</html>
