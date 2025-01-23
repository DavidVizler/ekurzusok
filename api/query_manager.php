<?php


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

    if (!is_array($user_courses)) {
        SendResponse([
            "uzenet" => "A felhasználó nem tagja egy kurzusnak sem"
        ]);
        return;
    }

    SendResponse($user_courses);
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
    if (!is_array($membership_data)) {
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
        WHERE m.course_id = ? ORDER BY u.lastname, u.firstnam;";
    }

    // Kurzus tagok lekérdezése
    $course_members = DataQuery($sql_statement, "i", [$course_id]);
    if (is_array($course_members)) {
        SendResponse($course_members);
    } else {
        SendResponse([
            "uzenet" => "Nincs kurzus ilyen ID-val"
        ]);
    }
}

function Manage($action) {
    switch ($action) {
        case "user-courses":
            UserCoursesQuery();
            break;
        case "course-members":
            CourseMembersQuery();
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