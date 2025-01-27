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

    if (!PostDataCheck(["name", "design"])) {
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

function Manage($action) {
    switch ($action) {
        case "create":
            CreateCourse();
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