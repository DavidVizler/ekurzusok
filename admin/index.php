<?php

    include "../api/functions.php";
    include "./admin_components.php";

    // Végpont
    $url = explode("/", $_SERVER["REQUEST_URI"]);
    $endpoint = explode("?", end($url))[0];

    if (!in_array($endpoint, ["", "users", "courses", "user-info", "course-info", "login"])) {
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

    session_start();
    if ($endpoint != "login" && !isset($_SESSION["admin_id"])) {
        header("Location: login");
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
        case "courses":
            $onload_js_function = "listCourses({$page}, {$rows})";
    }
    
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKurzusok Admin</title>
    <link rel="stylesheet" href="./admin.css">
    <link rel="shortcut icon" href="../img/eKurzusok.png" type="image/x-icon">
    <script src='./admin.js'></script>
</head>
<body>
    <div id="site-container">
        <?php
        
            switch ($endpoint) {
                case "":
                    NavBar($rows);
                    MainPage();
                    break;
                case "users":
                    NavBar($rows);
                    PageManager($page, $rows, "users");
                    UsersTable();
                    break;
                case "courses":
                    NavBar($rows);
                    PageManager($page, $rows, "courses");
                    CoursesTable();
                    break;
                case "user-info":
                    NavBar($rows);
                    break;
                case "course-info":
                    NavBar($rows);
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