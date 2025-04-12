<?php

include "../../api/functions.php";

function AdminGetUsers() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["rows", "page"], "ii", true, false)) {
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

    if (!empty($data["keyword"]) && !empty($data["field"])) {
        switch ($data["field"]) {
            case "user_id":
                $filter = "u.user_id = ?";
                $keyword = $data["keyword"];
                $keyword_type = "i";
                break;
            case "name":
                $filter = "CONCAT(u.lastname, ' ', u.firstname) LIKE ?";
                $keyword = "%{$data["keyword"]}%";
                $keyword_type = "s";
                break;
            case "email":
                $filter = "u.email LIKE ?";
                $keyword = "%{$data["keyword"]}%";
                $keyword_type = "s";
                break;
            default:
                SendResponse([
                    "uzenet" => "Érvénytelen keresési terület"
                ], 400);
                return;
        }
    }

    if (!empty($data["keyword"]) && !empty($data["field"])) {
        $sql_statement = "SELECT u.user_id, u.email, u.firstname, u.lastname, COUNT(m.membership_id) AS courses, 
        COUNT(CASE WHEN m.role = 3 THEN 1 END) AS own_courses
        FROM users u LEFT JOIN memberships m ON u.user_id = m.user_id
        GROUP BY u.user_id HAVING {$filter} {$order} LIMIT ? OFFSET ?;";
        $users = DataQuery($sql_statement, $keyword_type . "ii", [$keyword, $limit, $offset]);
    } else {
        $sql_statement = "SELECT u.user_id, u.email, u.firstname, u.lastname, COUNT(m.membership_id) AS courses, 
        COUNT(CASE WHEN m.role = 3 THEN 1 END) AS own_courses
        FROM users u LEFT JOIN memberships m ON u.user_id = m.user_id
        GROUP BY u.user_id {$order} LIMIT ? OFFSET ?;";
        $users = DataQuery($sql_statement, "ii", [$limit, $offset]);
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

    if (!PostDataCheck(["rows", "page"], "ii", true, false)) {
        return;
    }

    global $data;
    $limit = $data["rows"];
    $offset = $limit*($data["page"]-1);

    if (isset($data["orderby"])) {
        switch ($data["orderby"]) {
            case "course_id":
                $order = "ORDER BY c.course_id";
                break;
            case "name":
                $order = "ORDER BY c.name, c.course_id";
                break;
            case "members":
                $order = "ORDER BY members DESC, teachers DESC, c.course_id";
                break;
            case "owner":
                $order = "ORDER BY u.lastname, u.firstname, u.user_id";
                break;
            default:
                $order = "ORDER BY c.course_id";
                break;
        }
    } else {
        $order = "ORDER BY c.course_id";
    }

    if (!empty($data["keyword"]) && !empty($data["field"])) {
        switch ($data["field"]) {
            case "course_id":
                $filter = "c.course_id = ?";
                $keyword = $data["keyword"];
                $keyword_type = "i";
                break;
            case "name":
                $filter = "c.name LIKE ?";
                $keyword = "%{$data["keyword"]}%";
                $keyword_type = "s";
                break;
            case "code":
                $filter = "c.code = ?";
                $keyword = $data["keyword"];
                $keyword_type = "s";
                break;
            default:
                SendResponse([
                    "uzenet" => "Érvénytelen keresési terület"
                ], 400);
                return;
        }
    }

    if (!empty($data["keyword"]) && !empty($data["field"])) {
        $sql_statement = "SELECT c.course_id, c.name, c.code, c.archived, u.firstname, u.lastname, u.user_id, 
        COUNT(m.membership_id) AS members, COUNT(CASE WHEN m.role = 2 OR m.role = 3 THEN 1 END) AS teachers
        FROM courses c LEFT JOIN memberships m ON c.course_id = m.course_id LEFT JOIN users u ON m.user_id = u.user_id
        GROUP BY c.course_id HAVING {$filter} {$order} LIMIT ? OFFSET ?;";
        $courses = DataQuery($sql_statement, $keyword_type . "ii", [$keyword, $limit, $offset]);
    } else {
        $sql_statement = "SELECT c.course_id, c.name, c.code, c.archived, u.firstname, u.lastname, u.user_id, 
        COUNT(m.membership_id) AS members, COUNT(CASE WHEN m.role = 2 OR m.role = 3 THEN 1 END) AS teachers
        FROM courses c LEFT JOIN memberships m ON c.course_id = m.course_id LEFT JOIN users u ON m.user_id = u.user_id
        GROUP BY c.course_id {$order} LIMIT ? OFFSET ?;";
        $courses = DataQuery($sql_statement, "ii", [$limit, $offset]);
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

    if (!PostDataCheck(["rows", "page", "id"], "iii")) {
        return;
    }

    global $data;
    $limit = $data["rows"];
    $offset = $limit*($data["page"]-1);
    $id = $data["id"];

    if (isset($data["orderby"])) {
        switch ($data["orderby"]) {
            case "lastname":
                $order = "ORDER BY m.role DESC, u.lastname, u.firstname, u.user_id";
                break;
            case "firstname":
                $order = "ORDER BY m.role DESC, u.firstname, u.lastname, u.user_id";
                break;
            case "membership_id":
                $order = "ORDER BY m.role DESC, m.membership_id";
                break;
            case "email":
                $order = "ORDER BY m.role DESC, u.email";
                break;
            case "user_id":
                $order = "ORDER BY m.role DESC, u.user_id";
                break;
            default:
                $order = "ORDER BY m.role DESC, u.lastname, u.firstname, u.user_id";
                break;
        }
    } else {
        $order = "ORDER BY m.role DESC, u.lastname, u.firstname, u.user_id";
    }

    // Kurzus adatainak lekérdezése
    $sql_statement = "SELECT * FROM courses WHERE course_id = ?";
    $course_data = DataQuery($sql_statement, "i", [$id])[0];

    // Kurzus tagjainak lekérdezése 
    $sql_statement = "SELECT u.user_id, u.email, u.firstname, u.lastname, m.role, m.membership_id FROM users u
    INNER JOIN memberships m ON u.user_id = m.user_id
    WHERE m.course_id = ? {$order} LIMIT ? OFFSET ?;";
    $course_members = DataQuery($sql_statement, "iii", [$id, $limit, $offset]);

    if (count($course_members) == 0) {
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

function AdminGetUserInfo() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["rows", "page", "id"], "iii")) {
        return;
    }

    global $data;
    $limit = $data["rows"];
    $offset = $limit*($data["page"]-1);
    $id = $data["id"];

    if (isset($data["orderby"])) {
        switch ($data["orderby"]) {
            case "course_id":
                $order = "ORDER BY m.role DESC, c.course_id";
                break;
            case "name":
                $order = "ORDER BY m.role DESC, c.name, c.course_id";
                break;
            case "membership_id":
                $order = "ORDER BY m.role DESC, m.membership_id";
                break;
            default:
                $order = "ORDER BY m.role DESC, c.name, c.course_id";
                break;
        }
    } else {
        $order = "ORDER BY m.role DESC, c.name, c.course_id";
    }

    // Felhasználó adatainak lekérdezése
    $sql_statement = "SELECT * FROM users WHERE user_id = ?";
    $user_data = DataQuery($sql_statement, "i", [$id])[0];

    // Felhasználó kurzusainak lekérdezése
    $sql_statement = "SELECT c.course_id, m.membership_id, c.name, c.code, c.archived, m.role FROM courses c
    INNER JOIN memberships m ON c.course_id = m.course_id
    WHERE m.user_id = ? {$order} LIMIT ? OFFSET ?;";
    $user_courses = DataQuery($sql_statement, "iii", [$id, $limit, $offset]);

    SendResponse([
        "user_data" => $user_data,
        "user_courses" => $user_courses
    ]);
}

function AdminLogin() {
    if (!PostDataCheck(["username", "password"], "ss")) {
        return;
    }
    global $data;

    $sql_statement = "SELECT password, admin_id FROM admins WHERE username = ?;";
    $admin_data = DataQuery($sql_statement, "s", [$data["username"]]);

    if (count($admin_data) == 0) {
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

function AdminDeleteUser() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["user_id"], "i")) {
        return;
    }

    global $data;
    $user_id = $data["user_id"];

    // Saját kurzusok törlése
    $sql_statement = "DELETE courses, memberships FROM courses
    INNER JOIN memberships ON courses.course_id = memberships.course_id
    WHERE memberships.user_id = ? AND memberships.role = 3;";
    $deleted_courses = ModifyData($sql_statement, "i", [$user_id])/2;

    // Felhasználó törlése
    $sql_statement = "DELETE FROM users WHERE user_id = ?";
    $result = ModifyData($sql_statement, "i", [$user_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Felhasználó és {$deleted_courses} kurzusa törölve"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem sikerült törölni a felhasználót, {$deleted_courses} kurzusa lett törölve"
        ]);
    }
}

function AdminDeleteCourse() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["course_id"], "i")) {
        return;
    }

    global $data;
    $course_id = $data["course_id"];

    // Kurzus törlése
    $sql_statement = "DELETE FROM courses WHERE course_id = ?;";
    $result = ModifyData($sql_statement, "i", [$course_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Kurzus törölve"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem sikerült törölni a kurzust"
        ]);
    }
}

function AdminRemoveMember() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["membership_id"], "i")) {
        return;
    }

    global $data;
    $membership_id = $data["membership_id"];

    // Nem-e tulajdonos
    $sql_statement = "SELECT role FROM memberships WHERE membership_id = ?";
    $role = DataQuery($sql_statement, "i", [$membership_id]);
    if (count($role) > 0) {
        if ($role[0]["role"] == 3) {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "A tulajdonos nem távolítható el a kurzusból"
            ]);
            return;
        }
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs tagság ilyen ID-val"
        ]);
        return;
    }

    $sql_statement = "DELETE FROM memberships WHERE membership_id = ?";
    $result = ModifyData($sql_statement, "i", [$membership_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Felhasználó eltávolítva a kurzusból"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem sikerült eltávolítani a felhasználót a kurzusból"
        ]);
    }
}

function AdminModifyUserData() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["user_id", "email", "firstname", "lastname"], "isss")) {
        return;
    }

    global $data;
    $email = $data["email"];
    $firstname = $data["firstname"];
    $lastname = $data["lastname"];
    $user_id = $data["user_id"];

    $sql_statement = "UPDATE users SET email = ?, firstname = ?, lastname = ? WHERE user_id = ?";
    $result = ModifyData($sql_statement, "sssi", [$email, $firstname, $lastname, $user_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Felhasználó adatai módosítva"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Felhasználó adatainak módosítása sikertelen"
        ]);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
}

function AdminResetUserPassword() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["user_id"], "i")) {
        return;
    }

    global $data;
    $user_id = $data["user_id"];

    $sql_statement = "SELECT firstname, email FROM users WHERE user_id = ?;";
    $user_data = DataQuery($sql_statement, "i", [$user_id]);

    if (count($user_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs felhasználó ilyen ID-val"
        ], 404);
    }

    $email = $user_data[0]["email"];
    $firstname = $user_data[0]["firstname"];

    $new_passwd = GenerateTemporaryPassword();

    include "../../api/mail.php";

    if ($mail_success) {
        $sql_statement = "UPDATE users SET temp_password = ? WHERE user_id = ?;";
        ModifyData($sql_statement, "si", [$new_hashed_passwd, $user_id]);
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Ideiglenes belépési jelszó elküldve"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Sikertelen művelet"
        ], 400);
    }
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
    case "get-user-info":
        AdminGetUserInfo();
        break;
    case "delete-user":
        AdminDeleteUser();
        break;
    case "delete-course":
        AdminDeleteCourse();
        break;
    case "remove-member":
        AdminRemoveMember();
        break;
    case "login":
        AdminLogin();
        break;
    case "modify-user-data":
        AdminModifyUserData();
        break;
    case "reset-user-password":
        AdminResetUserPassword();
        break;
    default:
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Hibás műveletmegadás"
        ], 400);
        break;
}

?>