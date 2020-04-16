<?php

?>

<!DOCTYPE html>
<head>
    <title>OutOut - Edit Event Details</title>
    <link rel="stylesheet" type="text/css" href="../css/events.css">
</head>
<body>
<form name='EventForm' method='post'>
    <div>
<!--    TODO: NEED TO FILL INPUT FIELDS WITH DATA SUBMITTED PRIOR TO DATABASE -->
        <input type='text' name='name' placeholder="Event Name" required><br>
        <input type='text' name='description' placeholder="Event Description" required> <br>
        <input type='text' id="startTime" name='startTime' placeholder="Start time" required>
        <input type='text' id="endTime" name='endTime' placeholder="End time" required><br>
<!--    TODO: RESTRICT SIZE OF PICTURE THAT CAN BE UPLOADED -->
        Event Image: <br>
        <input type='file' id="eventImage" name='eventImage' class='input-file' accept="image/*">
        <label for="eventImage">Upload Image</label>
        <div class="image-preview" id="imagePreview">
            <img src="" alt="Image Preview" class="image-preview__image">
            <span class="image-preview__default-text">Image Preview</span>
        </div>

        <input type='text' id="ticketSite" name='ticketSite' placeholder="Ticket Sale Link"><Br>
        <input type='text' name="tags" placeholder="Tags: Indie, Pop, etc">
        <script>
            var dtt = document.getElementById('startTime');
            dtt.onfocus = function (event) {
                this.type = 'datetime-local';
                this.focus();
            };
            dtt.onblur = function (event) {
                this.type = 'text';
                this.blur();
            };
            var ett = document.getElementById('endTime');
            ett.onfocus = function (event) {
                this.type = 'datetime-local';
                this.focus();
            };
            ett.onblur = function (event) {
                this.type = 'text';
                this.blur();
            };

            // For Image Preview
            const inpFile = document.getElementById("eventImage");
            const previewContainer = document.getElementById("imagePreview");
            const previewImage = previewContainer.querySelector(".image-preview__image");
            const previewDefaultText = previewContainer.querySelector(".image-preview__default-text");

            inpFile.addEventListener("change", function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();

                    previewDefaultText.style.display = "none";
                    previewImage.style.display = "block";

                    reader.addEventListener("load", function() {
                        previewImage.setAttribute("src", this.result);
                    });

                    reader.readAsDataURL(file);
                }
            });
        </script>
    </div>
    <div style= "display: flex">
        <input type='submit' value='Update'>
<!--        TODO: FILL IN HREF ONCLICK OF CANCEL-->
        <input type="button" onclick="location.href='BACK TO DASHBOARD OR HOMEPAGE';" value="Cancel" />
    </div>
</form>
</body>
