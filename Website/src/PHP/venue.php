<!DOCTYPE html>
<html lang="en-GB">
<head>
    <link rel="stylesheet" type="text/css" href="../css/venue.css">
</head>
<body>
<div class="wrapper">
    <div class="container">
        <div style="display: flex; flex-direction: column">
            <h1 class="title">Venue name here</h1>
            <div class="seperator"></div>

            <label>Location:</label>
            <label>Location here</label>

            <label>Opening times:</label>
            <textarea readonly placeholder="Opening times here"></textarea>

            <label>Venue description:</label>
            <textarea readonly placeholder="Description of event here"></textarea>

            <label>Image:</label>
            <img src="../Assets/venue-image.jpg" alt="Venue Image">

            <label style="text-align: center; margin-top: 16px;"><b>Venue Tags:</b></label>
            <div style="display: flex; justify-content: center; ">
                <div class="tag-container" style="text-align: center">
                    <?php getTags($currentTagIDs,$pdo); ?>
                </div>
            </div>

        </div>
    </div>
</div>


</body>
</html>
