<?php

include "./sql_functions.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data["getdata"])) {
        $response = [
            "sikeres" => false,
            "uzenet" => "Hiányos adatok"
        ];
        header("bad request", true, 400);
    } else {
        $getdata = $data["getdata"];
       
        switch ($getdata) {
            case "user_data":
                GetUserData();
                break;
            case "user_courses":
                //GetUserCourse();
                break;
            case "course_members":
                //GetCourseMembers();
                break;
            case "course_content":
                //GetCourseContent();
                break;
            case "content_files":
                //GetContentFiles();
                break;
            default:
                $response = [
                    "sikeres" => false,
                    "uzenet" => "Hibás műveletmegadás"
                ];
                header("bad request", true, 400);
        }
    }
} else {
    $response = [
        "sikeres" => false,
        "uzenet" => "Hibás metódus"
    ];
    header("bad request", true, 400);
}

function GetUserData() {
    global $response;
    session_start();
    if(PostDataCheck([], true)) {
        $id = $_SESSION["user_id"];
        $sql_user_data_query = "SELECT `Email` AS email, `VezetekNev` AS lastname, `KeresztNev` AS firstname FROM `felhasznalo` WHERE `FelhasznaloID` = ?;";
        $user_data = DataQuery($sql_user_data_query, "i", [$id]);
        if (is_array($user_data)) {
            $response = $user_data;
        } else if($user_data == "Nincs találat!") {
            $response = [
                "hiba" => "Nincs felhasználó ilyen ID-val"
            ];
        } else {
            $response = $user_data;
            header("internal server error", true, 500);
        }
    }
}

if (isset($response)) {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

function PostDataCheck($to_check, $check_session) {
    global $response;
    global $data;
    if ($check_session && !isset($_SESSION["user_id"])) {
        $response = [
            "sikeres" => false,
            "uzenet" => "A felhasználó nincs bejelentkezve"
        ];
        header("unauthorized", true, 401);
        return false;
    }
    foreach ($to_check as $tc) {
        if (empty($data[$tc])) {
            $response = [
                "sikeres" => false,
                "uzenet" => "Hiányos adatok"
            ];
            header("bad request", true, 400);
            return false;
        }
    }
    return true;
}

?>