<?php

    include "../api/functions.php";
    include "./admin_components.php";

    if (isset($_POST["logout"])) {
        session_start();
        session_unset();
        session_destroy();
    }

    // Végpont
    $url = explode("/", $_SERVER["REQUEST_URI"]);
    $endpoint = explode("?", end($url))[0];

    if (!in_array($endpoint, ["", "users", "courses", "user-info", "course-info", "login", "modify-user-data"])) {
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

    if ($endpoint == "users" || $endpoint == "courses" || $endpoint == "course-info" || $endpoint == "user-info") {
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

        // Rendezési szempont
        if (isset($_GET["orderby"])) {
            $orderby = $_GET["orderby"];
        } else {
            $orderby = "default";
        }
    }

    if ($endpoint == "course-info" || $endpoint == "user-info" || $endpoint == "modify-user-data") {
        if (!isset($_GET["id"])) {
            header("Location: ./");
        } else {
            $id = $_GET["id"];
        }
    }

    switch($endpoint) {
        case "users":
            $onload_js_function = "listUsers({$page}, {$rows}, '{$orderby}')";
            break;
        case "courses":
            $onload_js_function = "listCourses({$page}, {$rows}, '{$orderby}')";
            break;
        case "course-info":
            $onload_js_function = "listCourseInfo({$page}, {$rows}, {$id}, '{$orderby}')";
            break;
        case "user-info":
            $onload_js_function = "listUserInfo({$page}, {$rows}, {$id}, '{$orderby}')";
            break;
        case "modify-user-data":
            $onload_js_function = "getUserData({$id})";
            break;
        default:
            $onload_js_function = "";
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
    <link rel="shortcut icon" href="../img/eKurzusok.png" type="image/x-icon">
    <script src='./admin.js'></script>
</head>
<body>
    <div id="site-container">
        <?php
        
            switch ($endpoint) {
                case "":
                    NavBar();
                    MainPage();
                    break;
                case "users":
                    NavBar($rows);
                    PageManager($page, $rows, $endpoint);
                    UsersTable();
                    break;
                case "courses":
                    NavBar($rows);
                    PageManager($page, $rows, $endpoint);
                    CoursesTable();
                    break;
                case "user-info":
                    NavBar($rows);
                    PageManager($page, $rows, $endpoint, $id);
                    UserInfoTable();
                    break;
                case "course-info":
                    NavBar($rows);
                    PageManager($page, $rows, $endpoint, $id);
                    CourseInfoTable();
                    break;
                case "modify-user-data":
                    NavBar();
                    UserDataForm($id);
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