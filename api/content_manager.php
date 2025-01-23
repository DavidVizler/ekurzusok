<?php

include "./functions.php";

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

    $user_id = $_SESSION["used_id"];
    $course_id = $data["course_id"];
    $title = $data["title"];
    $task = $data["task"];
    !empty($data["desc"]) ? $desc = $data["desc"] : $desc = null;
    if ($task) {
        !empty($data["deadline"]) ? $deadline = $data["deadline"] : $deadline = null;
        !empty($data["maxpoint"]) ? $maxpoint = $data["maxpoint"] : $maxpoint = null;
    } else {
        $deadline = null;
        $maxpoint = null;
    }

    $sql_statement = "INSERT INTO content (user_id, course_id, title, description, task, max_points, deadline, published, last_modified) 
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

function Manage($action) {
    switch ($action) {
        case "create":
            CreateCourseContent();
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