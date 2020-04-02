<?php
    $target_file = "/home/sgstribe/private_upload/" . $_GET['f'];
    if (!file_exists($target_file)) {
        die("file not found");
    } else {
        echo file_get_contents($target_file);
    }
?>
