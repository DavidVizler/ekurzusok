<?php

function CreateCourseContent() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["course_id", "title", "task"])) {
        return;
    }

    global $data;

    $user_id = $_SESSION["user_id"];
    $course_id = $data["course_id"];
    $title = $data["title"];
    $task = $data["task"];
    !empty($data["desc"]) ? $desc = $data["desc"] : $desc = null;
    (!empty($data["deadline"]) && $task) ? $deadline = $data["deadline"] : $deadline = null;
    (!empty($data["maxpoint"]) && $task) ? $maxpoint = $data["maxpoint"] : $maxpoint = null;

    // Tanár-e a kurzusban a felhasználó
    $sql_statement = "SELECT role FROM memberships WHERE user_id = ? AND course_id = ?";
    $teacher_check = DataQuery($sql_statement, "ii", [$user_id, $course_id]);
    
    if (count($teacher_check) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    if ($teacher_check[0]["role"] == 1) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tanár a kurzusban"
        ], 403);
        return;
    }
    
    // Tartalom létrehozása
    $sql_statement = "INSERT INTO content (content_id, user_id, course_id, title, description, task, max_points, deadline, published, last_modified) 
    VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, NULL, current_timestamp())";
    $result = ModifyData($sql_statement, "iissiis", [$user_id, $course_id, $title, $desc, $task, $maxpoint, $deadline]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Tartalom sikeresen létrehozva"
        ], 201);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A tartalom létrehozása sikertelen"
        ]);
    }
}

function PublishCourseContent() {
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
    $content_id = $data["content_id"];
    $user_id = $_SESSION["user_id"];

    // A felhasználóé-e a tartalom
    $sql_statement = "SELECT user_id FROM content WHERE content_id = ?";
    $content_owner_check = DataQuery($sql_statement, "i", [$content_id]);

    if (count($content_owner_check) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs tartalom ilyen ID-val"
        ], 404);
        return;
    }

    if ($content_owner_check[0]["user_id"] != $user_id) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a tartalomnak"
        ], 403);
        return;
    }

    $sql_statement = "UPDATE content SET published = NOW(), last_modified = NOW() WHERE content_id = ?;";
    $result = ModifyData($sql_statement, "i", [$content_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Tartalom sikeresen közzétéve"
        ], 201);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A tartalom közzététele sikertelen"
        ]);
    }
}

function Manage($action) {
    switch ($action) {
        case "create":
            CreateCourseContent();
            break;
        case "publish":
            PublishCourseContent();
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