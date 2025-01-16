<?php

    include "../api/functions.php";
    include "./admin_components.php";

    // Végpont
    $url = explode("/", $_SERVER["REQUEST_URI"]);
    $endpoint = explode("?", end($url))[0];

    if (!in_array($endpoint, ["", "users", "courses", "user-info", "course-info"])) {
        echo <<<HTML
            <!DOCTYPE html>
            <html lang="hu">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>eKurzusok Admin</title>
                </head>
                <body>
                    <center>
                        <h1>404 Not Found</h1>
                        <h2>Nem megfelelő url végpont: "{$endpoint}"</h2>
                    </center>
                </body>
            </html>
        HTML;
        exit;
    }

    if ($endpoint == "users" || $endpoint == "courses") {
        // Oldal
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        } else {
            $page = 1;
        }

        // Sorok oldalanként
        if (isset($_GET["rows"])) {
            $rows = $_GET["rows"];
        } else {
            $rows = 25;
        }
    }

    switch($endpoint) {
        case "users":
            $onload_js_function = "listUsers({$page}, {$rows})";
            break;
    }
    
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKurzusok Admin</title>
    <link rel="stylesheet" href="./admin.css">
    <script src='./admin.js'></script>
</head>
<body>
    <div id="site-container">
        <?php
        
            NavBar();
            switch ($endpoint) {
                case "":
                    MainPage();
                    break;
                case "users":
                    PageManager($page, $rows);
                    UsersTable();
                    break;
                case "courses":
                    PageManager($page, $rows);
                    CoursesTable();
                    break;
                case "user-info":
                    break;
                case "course-info":
                    break;
                case "login":
                    LoginForm();
                    break;
            }

            echo "<script>{$onload_js_function}</script>";

        ?>
    </div>
</body>
</html>