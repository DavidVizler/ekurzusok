<?php

switch ($action) {
    case "create":
        CreateContent();
        break;
    default:
        $response = [
            "sikeres" => false,
            "uzenet" => "Hibás műveletmegadás"
        ];
        header("bad request", true, 400);
        break;
}

function CreateContent() {
    global $data;
    global $response;
    if (PostDataCheck(["course_id", "title", "desc", "task"], true)) {
        $user_id = $_SESSION["user_id"];
        $course_id = $data["course_id"];
        $title = $data["title"];
        $desc = $data["desc"];
        $task = $data["task"];

        // A felhasználó benne van-e a kurzusban és tanár-e
        $sql_is_user_teacher_query = "SELECT `Tanar` FROM `kurzustag` WHERE `FelhasznaloID` = ? AND `KurzusID` = ?";
        $is_user_teacher = DataQuery($sql_is_user_teacher_query, "ii", [$user_id, $course_id]);
        if (is_array($is_user_teacher)) {
            if ($is_user_teacher[0]["Tanar"] == 1) {
                // Feladat-e a tartalom
                if ($task == 1) {
                    $max_point = isset($data["max_point"]) ? NULL : $data["max_point"];
                    $deadline = isset($data["deadline"]) ? NULL : $data["deadline"];
                    $sql_content_create = "INSERT INTO `tartalom` (`TartalomID`, `FelhasznaloID`, `KurzusID`, `Cim`, `Leiras`, `Feladat`, `MaxPont`, `Hatarido`, `Modositva`, `Kiadva`) 
                    VALUES (NULL, ?, ?, ?, ?, 1, ?, ?, current_timestamp(), current_timestamp());";
                    $result = ModifyData($sql_content_create, "iissis", [$user_id, $course_id, $title, $desc, $max_point, $deadline]);
                } else {
                    $sql_content_create = "INSERT INTO `tartalom` (`TartalomID`, `FelhasznaloID`, `KurzusID`, `Cim`, `Leiras`, `Feladat`, `MaxPont`, `Hatarido`, `Modositva`, `Kiadva`) 
                    VALUES (NULL, ?, ?, ?, ?, 0, NULL, NULL, current_timestamp(), current_timestamp());";
                    $result = ModifyData($sql_content_create, "iiss", [$user_id, $course_id, $title, $desc]);
                }

                if ($result == "Sikeres művelet!") { 
                    $response = [
                        "sikeres" => true,
                        "uzenet" => "Tartalom hozzáadva a kurzushoz"
                    ];
                    header("created", true, 201);
                } else if ($result == "Sikertelen művelet!") {
                    $response = [
                        "sikeres" => false,
                        "uzenet" => "Nem sikerült hozzáadni a tartalmat a kurzushoz"
                            ];
                    header("internal server error", true, 500);
                } else {
                    $response = [
                        "sikeres" => false,
                        "uzenet" => $result
                    ];
                    header("internal server error", true, 500);
                }  
            } else {
                $response = [
                    "sikeres" => false,
                    "uzenet" => "A felhasználó nem tanár a kurzusban"
                ];
                header("forbidden", true, 403);
            }
        } else {
            $response = [
                "sikeres" => false,
                "uzenet" => "A felhasználó nincs benne a kurzusban"
            ];
            header("forbidden", true, 403);
        }
    } 
}

?>