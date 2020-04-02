<?php
    /* Testing interacting with file storage on the uni server in this file */
    if ($_FILES['photo']['size'] == 0) {
        die("No file selected");
    } else {
        $filename = $_FILES['photo']['name'];
        echo "Stored in: " . $_FILES["photo"]["tmp_name"];
        if (move_uploaded_file($_FILES['photo']['tmp_name'],"/home/sgstribe/private_upload/profile.jpg")) {
            echo "success";
        } else {
            echo "error";
        }
    }



?>
