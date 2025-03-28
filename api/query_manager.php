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
    $user_data = DataQuery($sql_statement, "i", [$user_id]);

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

    $sql_statement = "SELECT c.course_id, c.name, c.design_id, c.archived, m.role,
    (SELECT u.firstname FROM users u INNER JOIN memberships m ON u.user_id = m.user_id WHERE m.role = 3 AND m.course_id = c.course_id) AS firstname,
    (SELECT u.lastname FROM users u INNER JOIN memberships m ON u.user_id = m.user_id WHERE m.role = 3 AND m.course_id = c.course_id) AS lastname
    FROM courses c INNER JOIN memberships m ON c.course_id = m.course_id INNER JOIN users u ON m.user_id = u.user_id
    WHERE m.user_id = ? ORDER BY c.name;";
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

    if (!PostDataCheck(["course_id"], "i")) {
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

    $sql_statement = "SELECT c.name, c.description, c.design_id, c.archived, u.firstname, u.lastname,
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

    if (!PostDataCheck(["course_id"], "i")) {
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
        $sql_statement = "SELECT u.user_id, u.lastname, u.firstname, m.role 
        FROM users u INNER JOIN memberships m ON u.user_id = m.user_id
        WHERE m.course_id = ? ORDER BY u.lastname, u.firstname;";
    } else {
        $sql_statement = "SELECT u.lastname, u.firstname , m.role
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

    if (!PostDataCheck(["course_id"], "i")) {
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
    $sql_statement = "SELECT c.content_id, c.title, c.task, c.published, c.deadline, c.max_points, u.firstname, u.lastname FROM content c
    INNER JOIN users u ON c.user_id = u.user_id WHERE c.course_id = ? AND (c.user_id = ? OR c.published IS NOT NULL) ORDER BY c.content_id DESC;";
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

    if (!PostDataCheck(["content_id"], "i")) {
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

    $sql_statement = "SELECT t.title, t.description, t.task, t.max_points, t.deadline, t.published, t.last_modified, c.archived, u.firstname, u.lastname, 
    IF(t.user_id=?, true, false) AS owned FROM content t
    INNER JOIN users u ON t.user_id = u.user_id INNER JOIN courses c ON t.course_id = c.course_id
    WHERE content_id = ?;";
    $content = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    if (count($content) > 0) {
        SendResponse($content[0]);
    } else {
        SendResponse([
            "uzenet" => "Nincs tartalom ilyen ID-val"
        ], 404);
    }
    
}

function CourseContentFilesQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["content_id"], "i")) {
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

    $sql_statement = "SELECT file_id, name, size FROM files WHERE content_id = ?;";
    $files = DataQuery($sql_statement, "i", [$content_id]);

    SendResponse($files);
}

function DeadlineTasksQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("GET")) {
        return;
    }

    $user_id = $_SESSION["user_id"];

    $sql_statement = "SELECT t.content_id, t.deadline, t.title, c.name AS course_name FROM content t
    INNER JOIN courses c ON t.course_id = c.course_id
    INNER JOIN memberships m ON c.course_id = m.course_id
    WHERE t.deadline IS NOT NULL AND m.user_id = ? AND c.archived = 0
    ORDER BY t.deadline;";
    $tasks = DataQuery($sql_statement, "i", [$user_id]);

    SendResponse($tasks);
}

function SubmissionsQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["content_id"], "i")) {
        return;
    }

    global $data;
    $user_id = $_SESSION["user_id"];
    $content_id = $data["content_id"];

    // Tulajdonosa-e a felhasználó a tartalomnak
    $sql_statement = "SELECT * FROM content WHERE user_id = ? AND content_id = ?;";
    $owner_check = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    if (count($owner_check) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a feladatnak"
        ], 403);
        return;
    }

    $sql_statement = "SELECT s.submission_id, u.lastname, u.firstname, s.submitted, COUNT(f.file_id) AS files_count FROM submissions s
    INNER JOIN content c ON s.content_id = c.content_id
    INNER JOIN users u ON s.user_id = u.user_id
    INNER JOIN files f ON s.submission_id = f.submission_id
    WHERE c.content_id = ? AND s.submitted IS NOT NULL GROUP BY u.user_id;";
    $submissions = DataQuery($sql_statement, "i", [$content_id]);

    SendResponse($submissions);
}

function SubmittedFilesQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["submission_id"], "i")) {
        return;
    }

    global $data;
    $submission_id = $data["submission_id"];
    $user_id = $_SESSION["user_id"];

    // A felhasználó tulajdonosa-e a beadandónak vagy létrehozója a tartalomnak
    $sql_statement = "SELECT c.user_id AS content_owner, s.user_id AS submission_owner FROM submissions s
    INNER JOIN content c ON s.content_id = c.content_id WHERE submission_id = ?;";
    $submission_data = DataQuery($sql_statement, "i", [$submission_id]);

    if (count($submission_data) == 0) {
        SendResponse([
            "uzenet" => "Nincs beadandó ilyen azonosítóval"
        ], 404);
        return;
    }

    if ($submission_data[0]["content_owner"] != $user_id && $submission_data[0]["submission_owner"] != $user_id) {
        SendResponse([
            "uzenet" => "A felhasználó nem tulajdonosa sem a beadandónak, sem a feladatnak"
        ], 403);
        return;
    }
    
    $sql_statement = "SELECT file_id, name, size FROM files WHERE submission_id = ?;";
    $files = DataQuery($sql_statement, "i", [$submission_id]);

    SendResponse($files);
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
        case "content-files":
            CourseContentFilesQuery();
            break;
        case "deadline-tasks":
            DeadlineTasksQuery();
            break;
        case "submissions":
            SubmissionsQuery();
            break;
        case "submission-files":
            SubmittedFilesQuery();
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