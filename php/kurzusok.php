<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/kurzusok.css">
    <title>Kurzusok</title>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div id="menu">
            <!-- Hamburger menu -->
            <div id="icon">
                <div id="line1" class="line">&nbsp;</div>
                <div id="line2" class="line">&nbsp;</div>
                <div id="line3" class="line">&nbsp;</div>
            </div>
            <ul id="menuContent">
                <a href="#"><li>Felvett kurzusok</li></a>
                <ul id="addedCourses">
                    <a href="#"><li>kurzus neve</li></a>
                    <a href="#"><li>kurzus neve</li></a>
                    <a href="#"><li>kurzus neve</li></a>
                    <a href="#"><li>kurzus neve</li></a>
                </ul>
                <a href="#"><li>Létrehozott kurzusok</li></a>
                <ul id="createdCourses">
                    <a href="#"><li>kurzus neve</li></a>
                    <a href="#"><li>kurzus neve</li></a>
                    <a href="#"><li>kurzus neve</li></a>
                </ul>
            </ul>
        </div>
        <!-- Plus icon -->
        <div class="plusIcon">
            <a onclick="openPopUp()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg></a>
        </div>

        <!-- Calendar icon -->
        <div class="calendarIcon">
            <a href="">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Popup -->
    <div id="popup" class="popup-overlay" style="display:none">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup()">&times;</span>
            <div class="popup-content-title">
                <h2>Kurzus felvétele</h2>
            </div><hr id="hr">
            <div class="courseCodeAdd">
                <h7>Kurzus kód megadása: </h7>
                <input type="text" name="coursedCode">
            </div><hr id="hr">
            <div class="createCourse">
                <a href="">Kurzus létrehozása</a>
            </div>
        </div>
    </div>

    <script src="../js/toggle.js"></script>
</body>
</html>