<?php

function CreateCourseContent() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["course_id", "title", "task"], "isb")) {
        return;
    }

    global $data;

    $user_id = $_SESSION["user_id"];
    $course_id = $data["course_id"];
    $title = $data["title"];
    $task = $data["task"];
    !empty($data["desc"]) ? $desc = $data["desc"] : $desc = null;

    // Ha feladat és a határidő nem null, akkor idő formátum ellenőrzés
    if (array_key_exists("deadline", $data) && !is_null($data["deadline"]) && $task) {
        $deadline = $data["deadline"];
        $date_format = 'Y-m-d\TH:i:s.v\Z';
        $date = DateTime::createFromFormat($date_format, $deadline);
        if (!$date || $date->format($date_format) !== $deadline) {
            SendInvalidDataRespone(true, "deadline", $deadline);
            return;
        }
    } else {
        $deadline = null;
    }

    // Ha van max pont és nem null, akkor megfelelő-e
    if (array_key_exists("maxpoint", $data) && !is_null($data["maxpoint"]) && $task) {
        $maxpoint = $data["maxpoint"];
        if (!is_int($maxpoint) || $maxpoint < 5 || $maxpoint > 1000) {
            SendInvalidDataRespone(true, "maxpoint", $maxpoint);
            return;
        }
    } else {
        $maxpoint = null;
    }

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

    if (!PostDataCheck(["content_id"], "i")) {
        return;
    }

    global $data;
    $content_id = $data["content_id"];
    $user_id = $_SESSION["user_id"];

    // A felhasználóé-e a tartalom
    $sql_statement = "SELECT user_id, published FROM content WHERE content_id = ?";
    $content_data = DataQuery($sql_statement, "i", [$content_id]);

    if (count($content_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs tartalom ilyen ID-val"
        ], 404);
        return;
    }

    if ($content_data[0]["user_id"] != $user_id) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a tartalomnak"
        ], 403);
        return;
    }

    $unpublished = is_null($content_data[0]["published"]);

    if ($unpublished) {
        $sql_statement = "UPDATE content SET published = UTC_TIMESTAMP(), last_modified = UTC_TIMESTAMP() WHERE content_id = ?;";
        $word = "közzététel";
    } else {
        $sql_statement = "UPDATE content SET published = NULL WHERE content_id = ?";
        $word = "elrejtés";
    }

    $result = ModifyData($sql_statement, "i", [$content_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Sikeres tartalom {$word}"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Sikertelen tartalom {$word}"
        ]);
    }
}

function ModifyCourseContentData() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["content_id", "title", "desc", "task", "maxpoint", "deadline"], "isebnd")) {
        return;
    }

    global $data;
    $user_id = $_SESSION["user_id"];
    $content_id = $data["content_id"];
    $title = $data["title"];
    $description = $data["desc"];
    $task = $data["task"];
    $maxpoint = $data["maxpoint"];
    $deadline = $data["deadline"];

    // Tartalom adatok lekérdezése
    $sql_statement = "SELECT title, description, task, deadline, max_points 
    FROM content WHERE content_id = ? AND user_id = ?;";
    $content_data = DataQuery($sql_statement, "ii", [$content_id, $user_id]);

    if (count($content_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a tartalomnak"
        ], 403);
        return;
    }

    $sql_statement = "UPDATE content SET ";
    $new_data = [];
    $new_data_types = "";

    if ($content_data[0]["title"] != $title) {
        $sql_statement .= "title = ?";
        array_push($new_data, $title);
        $new_data_types .= "s";
    }

    if ($content_data[0]["description"] != $description) {
        if (count($new_data) > 0) $sql_statement .= ", ";
        $sql_statement .= "description = ?";
        array_push($new_data, $description);
        $new_data_types .= "s";
    }

    if ($content_data[0]["task"] != $task) {
        if (count($new_data) > 0) $sql_statement .= ", ";
        $sql_statement .= "task = ?";
        array_push($new_data, $task);
        $new_data_types .= "i";

        if (!$task) {
            $sql_statement .= ", deadline = NULL, max_points = NULL";
        }
    }

    if ($task) {
        if ($content_data[0]["deadline"] != $deadline) {
            if (count($new_data) > 0) $sql_statement .= ", ";
            $sql_statement .= "deadline = ?";
            array_push($new_data, $deadline);
            $new_data_types .= "s";
        }

        if ($content_data[0]["max_points"] != $maxpoint) {
            if (count($new_data) > 0) $sql_statement .= ", ";
            $sql_statement .= "max_points = ?";
            array_push($new_data, $maxpoint);
            $new_data_types .= "i";
        }
    }

    // Ha semmi sem változik, akkor nincs adatbázis művelet
    if (count($new_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem érkezett változtatandó adat"
        ]);
        return;
    }

    // Időbélyeg hozzáadása és where záradék hozzáadása
    $sql_statement .= ", last_modified = UTC_TIMESTAMP() WHERE content_id = ?;";
    $new_data_types .= "i";
    array_push($new_data, $content_id);

    $result = ModifyData($sql_statement, $new_data_types, $new_data);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Sikeres adatmódosítás"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Sikertelen adatmódosítás"
        ]);
    }
    
}

function DeleteCourseContent() {
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

    // Tartalom adatok lekérdezése
    $sql_statement = "SELECT * FROM content WHERE content_id = ? AND user_id = ?;";
    $content_data = DataQuery($sql_statement, "ii", [$content_id, $user_id]);

    if (count($content_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a tartalomnak"
        ], 403);
        return;
    }

    $sql_statement = "DELETE FROM content WHERE content_id = ?";
    $result = ModifyData($sql_statement, "i", [$content_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Sikeres törlés"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Sikertelen törlés"
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
        case "modify-data":
            ModifyCourseContentData();
            break;
        case "delete":
            DeleteCourseContent();
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