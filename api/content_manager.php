<?php

function CreateCourseContent() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["course_id", "title", "task"], "sss", true, true, true)) {
        return;
    }

    // Érkeztek-e fájlok
    $files = array_key_exists("files", $_FILES);

    if ($files) {
        // Fájl feltöltés limit
        if (count($_FILES["files"]["name"]) > 10) {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Maximum 10 fájl tölthető fel"
            ], 413);
            return;
        }

        // Nincsenek-e túl nagy fájlok
        // Max fájl méret --> php.ini --> upload_max_filesize 
        for ($i = 0; $i < count($_FILES["files"]["name"]); $i++) {
            if ($_FILES["files"]["error"][$i]) {
                SendResponse([
                    "sikeres" => false,
                    "uzenet" => "A(z) '{$_FILES["files"]["name"][$i]}' túl nagy méretű"
                ], 413);
                return;
            }
        } 
    }

    $data = $_POST;

    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;
    $course_id = $data["course_id"];
    $title = $data["title"];
    $task = $data["task"] == "true";
    !empty($data["desc"]) ? $desc = $data["desc"] : $desc = null;

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

    // Ellenőrzés, hogy létezik-e vagy nem-e archivált a kurzs
    $sql_statement = "SELECT archived FROM courses WHERE course_id = ?;";
    $course_data = DataQuery($sql_statement, "i", [$course_id]);
    if ($course_data[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    // Ha feladat és a határidő nem null, akkor idő formátum ellenőrzés
    if (array_key_exists("deadline", $data) && $data["deadline"] != "null" && $task) {
        $deadline = $data["deadline"];
        $date_format = 'Y-m-d H:i:s';
        $date = DateTime::createFromFormat($date_format, $deadline);
        if (!$date || $date->format($date_format) !== $deadline) {
            SendInvalidDataRespone(true, "deadline", $deadline);
            return;
        }
        $now = new DateTime('now', new DateTimeZone('Europe/Budapest'));
        if ($now->format('Y-m-d H:i:s') > $deadline) {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Túl korai határidő"
            ], 403);
            return;
        }
    } else {
        $deadline = null;
    }

    // Ha van max pont és nem null, akkor megfelelő-e
    if (array_key_exists("maxpoint", $data) && $data["maxpoint"] != "null" && $task) {
        $maxpoint = (int)$data["maxpoint"];
        if ($maxpoint < 5 || $maxpoint > 1000) {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "A ponthatár 5 és 1000 közötti érték lehet"
            ], 400);
            return;
        }
    } else {
        $maxpoint = null;
    }
    
    // Tartalom létrehozása
    $sql_statement = "SELECT MAX(content_id) AS max_id FROM content;";
    $contents = DataQuery($sql_statement)[0]["max_id"];
    if (is_null($contents)) {
        $content_id = 1;
    } else {
        $content_id = $contents + 1;
    }

    $sql_statement = "INSERT INTO content (content_id, user_id, course_id, title, description, task, max_points, deadline, published, last_modified) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, current_timestamp())";
    $result = ModifyData($sql_statement, "iiissiis", [$content_id, $user_id, $course_id, $title, $desc, $task, $maxpoint, $deadline]);

    // Fájlok feltöltése
    if ($files) {
        FileUpload($content_id, "content");
    }

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Tartalom sikeresen létrehozva"
        ], 201);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A tartalom létrehozása sikertelen"
        ], 400);
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
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;

    // A felhasználóé-e a tartalom
    $sql_statement = "SELECT user_id, published, course_id FROM content WHERE content_id = ?";
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

    $course_id = $content_data[0]["course_id"];

    $sql_statement = "SELECT archived FROM courses WHERE course_id = ?;";
    $archived_check = DataQuery($sql_statement, "i", [$course_id]);

    if ($archived_check[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    // Publikált állapot módosítása az ellentétére
    $unpublished = is_null($content_data[0]["published"]);

    if ($unpublished) {
        $sql_statement = "UPDATE content SET published = NOW(), last_modified = NOW() WHERE content_id = ?;";
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
        ], 400);
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
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;
    $content_id = $data["content_id"];
    $title = $data["title"];
    $description = $data["desc"];
    $task = $data["task"];
    $maxpoint = $data["maxpoint"];
    $deadline = $data["deadline"];

    // Tartalom adatok lekérdezése
    $sql_statement = "SELECT title, description, task, deadline, max_points, course_id
    FROM content WHERE content_id = ? AND user_id = ?;";
    $content_data = DataQuery($sql_statement, "ii", [$content_id, $user_id]);

    if (count($content_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a tartalomnak"
        ], 403);
        return;
    }

    $course_id = $content_data[0]["course_id"];

    // Jogosultságok ellenőrzése
    $sql_statement = "SELECT m.role, c.archived FROM courses c
    INNER JOIN memberships m ON c.course_id = m.course_id
    WHERE c.course_id = ? AND m.user_id = ?;";
    $permission_check = DataQuery($sql_statement, "ii", [$course_id, $user_id]);

    if ($permission_check[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    if ($permission_check[0]["role"] == 1) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó már nem tanár a kurzusban"
        ], 403);
        return;
    }

    // Változott adatok hozzáadása az SQL parancshoz
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
            $now = new DateTime('now', new DateTimeZone('Europe/Budapest'));
            if ($now->format('Y-m-d H:i:s') > $deadline) {
                SendResponse([
                    "sikeres" => false,
                    "uzenet" => "Túl korai határidő"
                ], 400);
                return;
            }
            if (count($new_data) > 0) $sql_statement .= ", ";
            $sql_statement .= "deadline = ?";
            array_push($new_data, $deadline);
            $new_data_types .= "s";
        }

        if ($content_data[0]["max_points"] != $maxpoint) {
            if ($maxpoint < 5 || $maxpoint > 1000) {
                SendResponse([
                    "sikeres" => false,
                    "uzenet" => "Érvénytelen pontszám"
                ], 400);
                return;
            }
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
        ], 400);
        return;
    }

    // Időbélyeg hozzáadása és where záradék hozzáadása
    $sql_statement .= ", last_modified = NOW() WHERE content_id = ?;";
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
        ], 400);
    }
    
}

function AttachFileToContent() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }
    
    if (!PostDataCheck(["content_id"], "s", true, true, true)) {
        return;
    }

    // Érkeztek-e fájlok
    if (!array_key_exists("files", $_FILES)) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem érkeztek fájlok"
        ], 400);
        return;
    }

    $content_id = $_POST["content_id"];
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;

    // Ellenőrzés, hogy a felhaználóé-e a tartalom
    $sql_statement = "SELECT user_id, course_id FROM content WHERE content_id = ?;";
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

    $course_id = $content_data[0]["course_id"];

    // Jogosultságkezelés
    $sql_statement = "SELECT m.role, c.archived FROM courses c
    INNER JOIN memberships m ON c.course_id = m.course_id
    WHERE c.course_id = ? AND m.user_id = ?;";
    $permission_check = DataQuery($sql_statement, "ii", [$course_id, $user_id]);

    if ($permission_check[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    if ($permission_check[0]["role"] == 1) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó már nem tanár a kurzusban"
        ], 403);
        return;
    }
    
    // Fájlok feltöltése
    $results = FileUpload($content_id, "content");

    if ($results) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Fájlok sikeresen feltöltve"
        ], 201);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Sikertelen fájlfeltöltés"
        ], 400);
    }
}

function RemoveFileFromContent() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["file_id", "content_id"], "ii")) {
        return;
    }

    global $data;
    $file_id = $data["file_id"];
    $content_id = $data["content_id"];
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;

    // A felhasználó-e a tartalom tulajdonosa
    $sql_statement = "SELECT c.user_id, c.course_id FROM content c
    INNER JOIN files f ON c.content_id = f.content_id
    WHERE c.content_id = ? AND f.file_id = ?;";
    $file_data = DataQuery($sql_statement, "ii", [$content_id, $file_id]);

    if (count($file_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs fájl vagy tartalom ilyen ID-val"
        ], 404);
        return;
    }

    if ($file_data[0]["user_id"] != $user_id) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a tartalomnak"
        ], 403);
        return;
    }

    $course_id = $file_data[0]["course_id"];

    // Jogosultságkezelés
    $sql_statement = "SELECT m.role, c.archived FROM courses c
    INNER JOIN memberships m ON c.course_id = m.course_id
    WHERE c.course_id = ? AND m.user_id = ?;";
    $permission_check = DataQuery($sql_statement, "ii", [$course_id, $user_id]);

    if ($permission_check[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    if ($permission_check[0]["role"] == 1) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó már nem tanár a kurzusban"
        ], 403);
        return;
    }

    $sql_statement = "DELETE FROM files WHERE file_id = ?;";
    $result = ModifyData($sql_statement, "i", [$file_id]);

    unlink("../files/" . $file_id);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Sikeres törlés"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Sikertelen törlés"
        ], 400);
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
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;
    $content_id = $data["content_id"];

    // Tartalom adatok lekérdezése
    $sql_statement = "SELECT course_id, user_id FROM content WHERE content_id = ?;";
    $content_data = DataQuery($sql_statement, "i", [$content_id]);

    if (count($content_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs tartalom ilyen ID-val"
        ], 404);
        return;
    }

    // Jogosultságkezelés
    $course_id = $content_data[0]["course_id"];
    $sql_statement = "SELECT role FROM memberships WHERE course_id = ? AND user_id = ?;";
    $membership_data = DataQuery($sql_statement, "ii", [$course_id, $user_id]);

    if ($content_data[0]["user_id"] != $user_id && $membership_data[0]["role"] != 3) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa sem a tartalomnak, sem a kurzusnak"
        ], 403);
        return;
    } 
    
    if ($membership_data[0]["role"] == 1) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó már nem tanár a kurzusban"
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
        ], 400);
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
        case "upload-files":
            AttachFileToContent();
            break;
        case "remove-file":
            RemoveFileFromContent();
            break;
        case "delete":
            DeleteCourseContent();
            break;
        default:
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Hibás műveletmegadás: {$action}"
            ], 400);
            break;
    }
}

?>