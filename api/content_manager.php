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

    $sql_statement = "INSERT INTO `tartalom` (`TartalomID`, `FelhasznaloID`, `KurzusID`, `Cim`, `Leiras`, `Feladat`, `MaxPont`, `Hatarido`, `Modositva`, `Kiadva`) 
    VALUES (NULL, '6', '5', 'Valami', 'Valami', '0', NULL, NULL, current_timestamp(), NULL)";

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