<?php

function UserDataQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("GET")) {
        return;
    }

    $user_id = $_SESSION["user_id"];

    $sql_statement = "SELECT email, firstname, lastname FROM users WHERE user_id = ?";
    $user_data = DataQuery($sql_statement, "i", $user_id);

    if (count($user_data) > 0) {
        SendResponse($user_data[0]);
    } else {
        SendResponse([
            "uzenet" => "A bejelentkezett felhasználói fiók már nem létezik"
        ], 410);
    }
}

function UserCoursesQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("GET")) {
        return;
    }

    $user_id = $_SESSION["user_id"];

    $sql_statement = "SELECT c.course_id, c.name, c.design_id, c.archived FROM courses c
    INNER JOIN memberships m ON c.course_id = m.course_id
    WHERE m.user_id = ? ORDER BY c.name";
    $user_courses = DataQuery($sql_statement, "i", [$user_id]);

    SendResponse($user_courses);
}

function CourseDataQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["course_id"])) {
        return;
    }
    
    global $data;
    $user_id = $_SESSION["user_id"];
    $course_id = $data["course_id"];

    // Benne van-e a felhasználó a kurzusban
    $sql_statement = "SELECT role FROM memberships WHERE course_id = ? AND user_id = ?;";
    $membership_data = DataQuery($sql_statement, "ii", [$course_id, $user_id]);
    if (count($membership_data) == 0) {
        SendResponse([
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    $sql_statement = "SELECT c.name, c.description, c.design_id, u.firstname, u.lastname,
    IF(u.user_id = ?, true, false) AS owned FROM courses c
    INNER JOIN memberships m ON c.course_id = m.course_id
    INNER JOIN users u ON m.user_id = u.user_id
    WHERE c.course_id = ? AND m.role = 3;";
    $course_data = DataQuery($sql_statement, "ii", [$user_id, $course_id]);

    SendResponse($course_data[0]);
}

function CourseMembersQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["course_id"])) {
        return;
    }
    
    global $data;
    $user_id = $_SESSION["user_id"];
    $course_id = $data["course_id"];

    // Benne van-e a felhasználó a kurzusban
    $sql_statement = "SELECT role FROM memberships WHERE course_id = ? AND user_id = ?;";
    $membership_data = DataQuery($sql_statement, "ii", [$course_id, $user_id]);
    if (count($membership_data) == 0) {
        SendResponse([
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    // Ha tulajdonos, akkor a tagok ID-ja is lekérdezésre kerül, hogy el tudja őket távolítani a kurzusból
    if ($membership_data[0]["role"] == 3) {
        $sql_statement = "SELECT u.user_id, u.lastname, u.firstname 
        FROM users u INNER JOIN memberships m ON u.user_id = m.user_id
        WHERE m.course_id = ? ORDER BY u.lastname, u.firstname;";
    } else {
        $sql_statement = "SELECT u.lastname, u.firstname 
        FROM users u INNER JOIN memberships m ON u.user_id = m.user_id
        WHERE m.course_id = ? ORDER BY u.lastname, u.firstname;";
    }

    // Kurzus tagok lekérdezése
    $course_members = DataQuery($sql_statement, "i", [$course_id]);
    if (count($course_members) > 0) {
        SendResponse($course_members);
    } else {
        SendResponse([
            "uzenet" => "Nincs kurzus ilyen ID-val"
        ], 404);
    }
}

function CourseContentQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["course_id"])) {
        return;
    }
    
    global $data;
    $user_id = $_SESSION["user_id"];
    $course_id = $data["course_id"];

    // Benne van-e a felhasználó a kurzusban
    $sql_statement = "SELECT role FROM memberships WHERE course_id = ? AND user_id = ?;";
    $membership_data = DataQuery($sql_statement, "ii", [$course_id, $user_id]);
    if (count($membership_data) == 0) {
        SendResponse([
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    // Ha tanár, akkor a saját nem publikus tartalmait is visszaadja
    $sql_statement = "SELECT c.content_id, c.title, c.task, c.published, u.firstname, u.lastname FROM content c
    INNER JOIN users u ON c.user_id = u.user_id WHERE c.course_id = ? AND (c.user_id = ? OR c.published IS NOT NULL);";
    $content = DataQuery($sql_statement, "ii", [$course_id, $user_id]);

    SendResponse($content);
}

function CourseContentDataQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["content_id"])) {
        return;
    }
    
    global $data;
    $user_id = $_SESSION["user_id"];
    $content_id = $data["content_id"];

    // Benne van-e a felhasználó a kurzusban
    $sql_statement = "SELECT m.user_id FROM memberships m
    INNER JOIN courses c ON m.course_id = c.course_id
    INNER JOIN content t ON t.course_id = c.course_id
    WHERE t.content_id = ? AND m.user_id = ?;";
    $membership_data = DataQuery($sql_statement, "ii", [$content_id, $user_id]);
    if (count($membership_data) == 0) {
        SendResponse([
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    $sql_statement = "SELECT c.title, c.description, c.task, c.max_points, c.deadline, c.published, c.last_modified, u.firstname, u.lastname, 
    IF(c.user_id=?, true, false) AS owned FROM content c
    INNER JOIN users u ON c.user_id = u.user_id WHERE content_id = ? AND c.published IS NOT NULL;";
    $content = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    if (count($content) > 0) {
        SendResponse($content[0]);
    } else {
        SendResponse([
            "uzenet" => "Nincs tartalom ilyen ID-val"
        ], 404);
    }
    
}

function Manage($action) {
    switch ($action) {
        case "user-data":
            UserDataQuery();
            break;
        case "user-courses":
            UserCoursesQuery();
            break;
        case "course-data":
            CourseDataQuery();
            break;
        case "course-members":
            CourseMembersQuery();
            break;
        case "course-content":
            CourseContentQuery();
            break;
        case "content-data":
            CourseContentDataQuery();
            break;
        default:
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Hibás műveletmegadás"
            ], 400);
            break;
    }
}

?>