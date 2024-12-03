<?php

    include "./sql_functions.php";

    // Ez lesz a fő adatváltoztató műveleteket végző PHP fájl
    // user_manager és course_manager törölve lesz

    function AddCourseMember() {
        $data = json_decode(file_get_contents("php://input"), true);

        // Érkezett adatok ellenőrzése
        if (!empty($data["user_id"]) && !empty($data["course_id"])) {
            $new_data = [$data["user_id"], $data["course_id"]];

            $sql_course_member_upload = "INSERT INTO `kurzustag` (`ID`, `FelhasznaloID`, `KurzusID`, `Tanar`) VALUES (NULL, ?, ?, '0');";
            $result = ModifyData($sql_course_member_upload, "ii", $new_data);

            if ($result == "Sikeres művelet!") {
                $response = [
                    "sikeres" => true,
                    "uzenet" => "Felhasználó felvéve a kurzusba"
                ];
                header("created", true, 201);
            } else if ($result == "Sikertelen művelet!") {
                $response = [
                    "sikeres" => false,
                    "uzenez" => "Nem sikerült felvenni a felhasználót a kurzusba"
                ];
                header("internal server error", true, 500);
            } else {
                $response = [
                    "sikeres" => false,
                    "uzenez" => $result
                ];
                header("internal server error", true, 500);
            }
        } else {
            $response = [
                "sikeres" => false,
                "uzenet" => "Hiányos adatok"
            ];
            header("bad request", true, 400);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    // URL végpont megállapítás
    $url = explode("/", $_SERVER["REQUEST_URI"]);
    $url_vege = end($url);
    
    switch ($url_vege) {
        case "create-user":
            break;
        case "login-user":
            break;
        case "logout-user":
            break;
        case "modify-user-data":
            break;
        case "modify-user-password":
            break;
        case "delete-user":
            break;
        case "create-course":
            break;
        case "modify-course-data":
            break;
        case "delete-course":
            break;
        case "add-course-member":
            break;
        case "modify-course-teacher":
            break;
        case "remove-course-member":
            break;
        default:
            $valasz = [
                "sikeres" => false,
                "uzenet" => "Nem megfelelő URL végpont"
            ];
            echo json_encode($valasz, JSON_UNESCAPED_UNICODE);
            header("not found", true, 404);
            break;
    }

?>