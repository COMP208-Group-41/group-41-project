<?php
    /* This php script executes when a user uploads a file through html and
     * moves the uploaded file from the temporary file on the web server to a
     * permanent location specified here
     */

    /* If the file is empty (no size) then error is shown, however the size is
     * also set to 0 if the file is too large for the uni server so the error
     * message covers both possibilities
     */
    if ($_FILES['photo']['size'] == 0) {
        die("No file selected or the selected file is too large!");
    }

    if ($_FILES['photo']['error'] != 0) {
        die("Error in file upload");
    }

    if ($_FILES['photo']['type'] != "image/jpeg") {
        die("File must be a jpeg!");
    }


    $filename = $_FILES['photo']['name'];
    $directory = "home/sgstribe/private_upload/";
    if (move_uploaded_file($_FILES['photo']['tmp_name'],"/home/sgstribe/private_upload/profile.jpg")) {
        echo "success!<br>";
    } else {
        echo "error!<br>";
    }
?>
