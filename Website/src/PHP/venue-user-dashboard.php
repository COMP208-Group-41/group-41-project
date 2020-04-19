<!DOCTYPE html>
<html lang='en-GB'>
<head>
    <title>OutOut - Venue Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/venue-user-dashboard.css">
    <div class="header">
        <img src="../Assets/outout.svg" alt="OutOut">
        <div class="header-right">
            <a class="active" href="#event">create event</a>
            <a href="#contact">Contact</a>
            <a href="#about">About</a>
            <a href="#login">login</a>
            <a href="#viewEvents">view events</a>
        </div>
    </div>
</head>
<body>
<h1>Venues</h1>
<button class="create-edit-venue">Create/Edit Venue</button>
<table align="center" border="1px" style="width:600px; line-height:40px;">
    <t>
        <th>Venue</th>
        <th>Upcoming Events</th>
        <td>
            <div class="dropdown">
                <button onclick="dropdown()" class="editbtn">Edit</button>
                <div id="venueOptions" class="dropdown-content">
                    <a href="#venue-page">View Venue</a>
                    <a href="#edit-venue">Edit/Delete Venue</a>
                </div>
            </div>
        </td>
    </t>
</table>
<script>function dropdown() {
    document.getElementById("venueOptions").classList.toggle("show");
}
// Close the dropdown menu if the user clicks outside of it
window.onclick = function (event) {
    if (!event.target.matches('.editbtn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        var i;
        for (i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}
</script>
</body>
</html>


