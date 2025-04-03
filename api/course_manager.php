<?php

function GenerateCourseCode() {
    $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $code = "";

    for ($i = 0; $i < 10; $i++) {
        $code .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $code;
}

function CreateCourse() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["name", "design"], "si")) {
        return;
    }

    global $data;
    $name = $data["name"];
    $design = $data["design"];
    $desc = isset($data["desc"]) ? $data["desc"] : NULL;
    $user_id = $_SESSION["user_id"];

    $sql_statement = "SELECT code FROM courses";
    $used_codes = DataQuery($sql_statement);

    // Kód generálása / újragenerálása ha már fel van használva
    $code = GenerateCourseCode();
    if (is_array($used_codes)) {
        while (in_array($code, $used_codes)) {
            $code = GenerateCourseCode();
        }
    }

    $sql_statement = "INSERT INTO courses (course_id, name, description, code, design_id, archived)
    VALUES (NULL, ?, ?, ?, ?, 0);";
    $result = ModifyData($sql_statement, "sssi", [$name, $desc, $code, $design]);

    if (!$result) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus létrehozása sikertelen"
        ]);
    }

    $sql_statement = "SELECT MAX(course_id) AS id FROM courses";
    $course_id = DataQuery($sql_statement)[0]["id"];

    // Kurzus tagság hozzáadása
    $sql_statement = "INSERT INTO memberships (membership_id, user_id, course_id, role) VALUE (NULL, ?, ?, 3)";
    $result_membership = ModifyData($sql_statement, "ii", [$user_id, $course_id]);

    if (!$result_membership) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Tulajdonos hozzáadása a kurzushoz sikertelen"
        ]);
    } else {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Kurzus sikeresen létrehozva"
        ], 201);
    }
}

function ModifyCourseData() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["id", "name", "desc", "design"], "isei")) {
        return;
    }

    global $data;
    $user_id = $_SESSION["user_id"];
    $course_id = $data["id"];
    $name = $data["name"];
    $description = $data["desc"] ;
    $design_id = $data["design"];

    // kurzus adatok lekérdezése
    $sql_statement = "SELECT c.name, c.description, c.design_id, m.role, c.archived FROM courses c
    INNER JOIN memberships m ON c.course_id = m.course_id
    WHERE c.course_id = ? AND m.user_id = ?;";
    $course_data = DataQuery($sql_statement, "ii", [$course_id, $user_id]);

    if (count($course_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    if ($course_data[0]["role"] != 3) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a kurzusnak"
        ], 403);
        return;
    }

    if ($course_data[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    $sql_statement = "UPDATE courses SET ";
    $new_data = [];
    $new_data_types = "";

    if ($course_data[0]["name"] != $name) {
        $sql_statement .= "name = ?";
        array_push($new_data, $name);
        $new_data_types .= "s";
    }

    if ($course_data[0]["description"] != $description) {
        if (count($new_data) > 0) $sql_statement .= ", ";
        $sql_statement .= "description = ?";
        array_push($new_data, $description);
        $new_data_types .= "s";
    }

    if ($course_data[0]["design_id"] != $design_id) {
        if (count($new_data) > 0) $sql_statement .= ", ";
        $sql_statement .= "design_id = ?";
        array_push($new_data, $design_id);
        $new_data_types .= "i";
    }
   
    // Ha semmi sem változik, akkor nincs adatbázis művelet
    if (count($new_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem érkezett változtatandó adat"
        ]);
        return;
    }

    // Where záradék hozzáadása
    $sql_statement .= " WHERE course_id = ?;";
    $new_data_types .= "i";
    array_push($new_data, $course_id);

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

function ArchiveCourse() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["id"], "i")) {
        return;
    }

    global $data;
    $user_id = $_SESSION["user_id"];
    $course_id = $data["id"];

    // kurzus adatok lekérdezése
    $sql_statement = "SELECT c.archived, m.role FROM courses c
    INNER JOIN memberships m ON c.course_id = m.course_id
    WHERE c.course_id = ? AND m.user_id = ?;";
    $course_data = DataQuery($sql_statement, "ii", [$course_id, $user_id]);

    if (count($course_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    if ($course_data[0]["role"] != 3) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a kurzusnak"
        ], 403);
        return;
    }

    $new_status = !$course_data[0]["archived"];
    $modification = $new_status ? "archiválás" : "visszaállítás";

    $sql_statement = "UPDATE courses SET archived = ? WHERE course_id = ?";
    $result = ModifyData($sql_statement, "ii", [$new_status, $course_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Sikeres {$modification}"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Sikertelen {$modification}"
        ]);
    }
}

function DeleteCourse() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["id"], "i")) {
        return;
    }

    global $data;
    $user_id = $_SESSION["user_id"];
    $course_id = $data["id"];

    $sql_statement = "SELECT * FROM courses c
    INNER JOIN memberships m ON c.course_id = m.course_id
    WHERE c.course_id = ? AND m.user_id = ?;";
    $course_data = DataQuery($sql_statement, "ii", [$course_id, $user_id]);

    if (count($course_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    if ($course_data[0]["role"] != 3) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a kurzusnak"
        ], 403);
        return;
    }

    $sql_statement = "DELETE FROM courses WHERE course_id = ?";
    $result = ModifyData($sql_statement, "i", [$course_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Kurzus sikeresen törölve"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Kurzus törlése sikertelen"
        ]);
    }
}

function Manage($action) {
    switch ($action) {
        case "create":
            CreateCourse();
            break;
        case "modify-data":
            ModifyCourseData();
            break;
        case "archive":
            ArchiveCourse();
            break;
        case "delete":
            DeleteCourse();
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