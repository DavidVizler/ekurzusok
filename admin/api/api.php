<?php

include "../../api/functions.php";

function AdminGetUsers() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["rows", "page"])) {
        return;
    }

    global $data;
    $limit = $data["rows"];
    $offset = $limit*($data["page"]-1);

    if (isset($data["orderby"])) {
        switch ($data["orderby"]) {
            case "lastname":
                $order = "ORDER BY u.lastname, u.firstname, u.user_id";
                break;
            case "firstname":
                $order = "ORDER BY u.firstname, u.lastname, u.user_id";
                break;
            case "email":
                $order = "ORDER BY u.email";
                break;
            case "courses":
                $order = "ORDER BY courses DESC, own_courses DESC, u.lastname, u.firstname, u.user_id";
                break;
            case "own_courses":
                $order = "ORDER BY own_courses DESC, courses DESC, u.lastname, u.firstname, u.user_id";
                break;
            default:
                $order = "ORDER BY u.user_id";
                break;
        }
    } else {
        $order = "ORDER BY u.user_id";
    }

    $sql_statement = "SELECT u.user_id, u.email, u.firstname, u.lastname, COUNT(m.membership_id) AS courses, 
    COUNT(CASE WHEN m.role = 3 THEN 1 END) AS own_courses
    FROM users u LEFT JOIN memberships m ON u.user_id = m.user_id
    GROUP BY u.user_id {$order} LIMIT ? OFFSET ?;";
    $users = DataQuery($sql_statement, "ii", [$limit, $offset]);

    if (!is_array($users)) {
        SendResponse([
            "uzenet" => "Nincsenek felhasználók az adatbázisban"
        ]);
        return;
    }

    SendResponse($users);
}

function AdminGetCourses() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["rows", "page"])) {
        return;
    }

    global $data;
    $limit = $data["rows"];
    $offset = $limit*($data["page"]-1);

    $sql_statement = "SELECT c.course_id, c.name, c.code, c.archived, u.firstname, u.lastname, u.user_id, 
    COUNT(m.membership_id) AS members, COUNT(CASE WHEN m.role = 2 OR m.role = 3 THEN 1 END) AS teachers
    FROM courses c LEFT JOIN memberships m ON c.course_id = m.course_id LEFT JOIN users u ON m.user_id = u.user_id
    GROUP BY c.course_id LIMIT ? OFFSET ?;";
    $courses = DataQuery($sql_statement, "ii", [$limit, $offset]);

    if (!is_array($courses)) {
        SendResponse([
            "uzenet" => "Nincsenek kurzusok az adatbázisban"
        ]);
        return;
    }

    SendResponse($courses,);

}

function AdminGetCourseInfo() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["rows", "page", "id"])) {
        return;
    }

    global $data;
    $limit = $data["rows"];
    $offset = $limit*($data["page"]-1);
    $id = $data["id"];

    // Kurzus adatainak lekérdezése
    $sql_statement = "SELECT * FROM courses WHERE course_id = ?";
    $course_data = DataQuery($sql_statement, "i", [$id])[0];

    // Kurzus adatainak lekérdezése 
    $sql_statement = "SELECT u.user_id, u.email, u.firstname, u.lastname, m.role, m.membership_id FROM users u
    INNER JOIN memberships m ON u.user_id = m.user_id
    WHERE m.course_id = ? ORDER BY m.role DESC, u.lastname, u.firstname
    LIMIT ? OFFSET ?;";
    $course_members = DataQuery($sql_statement, "iii", [$id, $limit, $offset]);

    if (!is_array($course_members)) {
        SendResponse([
            "uzenet" => "Nincsenek tagjai a kurzusnak"
        ]);
        return;
    }

    SendResponse([
        "course_data" => $course_data,
        "course_members" => $course_members
    ]);
}

function AdminLogin() {
    if (!PostDataCheck(["username", "password"])) {
        return;
    }
    global $data;

    $sql_statement = "SELECT password, admin_id FROM admins WHERE username = ?;";
    $admin_data = DataQuery($sql_statement, "s", [$data["username"]]);

    if (!is_array($admin_data)) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Helytelen felhasználónév vagy jelszó"
        ]);
        return;
    }

    if (!password_verify($data["password"], $admin_data[0]["password"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Helytelen felhasználónév vagy jelszó"
        ]);
        return;
    }  

    session_start();
    $_SESSION["admin_id"] = $admin_data[0]["admin_id"];
    SendResponse([
        "sikeres" => true,
        "uzenet" => "Admin bejelentkezve"
    ]);
}

function AdminRemoveMember() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["membership_id"])) {
        return;
    }

    global $data;
    $membership_id = $data["membership_id"];

    $sql_statement = "DELETE FROM `kurzustag` WHERE `ID` = ?";
    $result = ModifyData($sql_statement, "i", [$membership_id]);

    if ($result == "Sikeres művelet!") {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Felhasználó eltávolítva a kurzusból"
        ]);
    } else if ($result == "Sikertelen művelet!") {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem sikerült eltávolítani a felhasználót a kurzusból"
        ]);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
}

$url = explode("/", $_SERVER["REQUEST_URI"]);
$action = end($url);
switch($action) {
    case "get-users":
        AdminGetUsers();
        break;
    case "get-courses":
        AdminGetCourses();
        break;
    case "get-course-info":
        AdminGetCourseInfo();
        break;
    case "remove-member":
        AdminRemoveMember();
        break;
    case "login":
        AdminLogin();
        break;
    default:
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Hibás műveletmegadás"
        ], 400);
        break;
}

?>