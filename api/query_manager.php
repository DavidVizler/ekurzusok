<?php


function UserCoursesQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("GET")) {
        return;
    }

    $user_id = $_SESSION["user_id"];

    $sql_statement = "SELECT courses.course_id, courses.name, courses.design_id, courses.archived FROM courses
    INNER JOIN memberships ON courses.course_id = memberships.course_id
    WHERE memberships.user_id = ?";
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
        $sql_statement = "SELECT users.user_id, users.lastname, users.firstname 
        FROM users INNER JOIN memberships ON users.user_id = memberships.user_id
        WHERE memberships.course_id = ?;";
    } else {
        $sql_statement = "SELECT users.lastname, users.firstname 
        FROM users INNER JOIN memberships ON users.user_id = memberships.user_id
        WHERE memberships.course_id = ?;";
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