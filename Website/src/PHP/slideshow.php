<!-- Slideshow from W3Schools https://www.w3schools.com/howto/howto_js_slideshow.asp -->
<div class="slideshow-container">
    <div class="mySlides fade">
        <div class="numbertext">1 / 3</div>
        <img src="https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/4/5/venue.jpg" onclick="location.href='venue.php?venueID=5'" alt="Venue Image" class="title-img">
        <div class="text">LEVEL</div>
    </div>

    <div class="mySlides fade">
        <div class="numbertext">2 / 3</div>
        <img src="https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/4/6/venue.jpg" onclick="location.href='venue.php?venueID=6'" alt="Venue Image" class="title-img">
        <div class="text">Electrik Warehouse</div>
    </div>

    <div class="mySlides fade">
        <div class="numbertext">3 / 3</div>
        <img src="https://student.csc.liv.ac.uk/~sgstribe/Images/Venue/4/8/venue.jpg" onclick="location.href='venue.php?venueID=8'" alt="Venue Image" class="title-img">
        <div class="text">MODO</div>
    </div>

    <!-- Next and previous buttons -->
    <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
    <a class="next" onclick="plusSlides(1)">&#10095;</a>
</div>

<div style="text-align:center; margin-top: 8px">
    <span class="dot" onclick="currentSlide(1)"></span>
    <span class="dot" onclick="currentSlide(2)"></span>
    <span class="dot" onclick="currentSlide(3)"></span>
</div>
<script src="slideshow.js"></script>
<br>
