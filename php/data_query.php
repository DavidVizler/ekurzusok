<?php

include "./sql_functions.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (PostDataCheck(["getdata"])) {
        $getdata = $data["getdata"];
       
        switch ($getdata) {
            case "user_data":
                GetUserData();
                break;
            case "user_courses":
                GetUserCourses();
                break;
            case "course_data":
                GetCourseData();
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
    if (PostDataCheck([], true)) {
        $id = $_SESSION["user_id"];
        $sql_user_data_query = "SELECT `Email` AS email, `VezetekNev` AS lastname, `KeresztNev` AS firstname FROM `felhasznalo` WHERE `FelhasznaloID` = ?;";
        $user_data = DataQuery($sql_user_data_query, "i", [$id]);
        if (is_array($user_data)) {
            $response = $user_data;
        } else if($user_data == "Nincs találat!") {
            $response = [
                "uzenet" => "Nincs felhasználó ilyen ID-val"
            ];
        } else {
            $response = $user_data;
            header("internal server error", true, 500);
        }
    }
}

function GetUserCourses() {
    global $response;
    session_start();
    if (PostDataCheck([], true)) {
        $id = $_SESSION["user_id"];
        $sql_user_courses_query = "SELECT `kurzus`.`KurzusID` AS course_id, `kurzus`.`KurzusNev` AS name, `kurzus`.`Design` AS design, 
        `kurzus`.`Archivalt` AS archived, CONCAT(`felhasznalo`.`VezetekNev`, ' ', `felhasznalo`.`KeresztNev`) AS owner FROM `kurzus`
        INNER JOIN `kurzustag` ON `kurzus`.`KurzusID` = `kurzustag`.`KurzusID` 
        INNER JOIN `felhasznalo` ON `kurzus`.`FelhasznaloID` = `felhasznalo`.`FelhasznaloID`
        WHERE `kurzustag`.`FelhasznaloID` = ?;";
        $user_courses = DataQuery($sql_user_courses_query, "i", [$id]);

        if (is_array($user_courses)) {
            $response = $user_courses;
        } else if ($user_courses == "Nincs találat!") {
            $response = [
                "uzenez" => "A felhasználó nem tagja egy kurzusnak sem"
            ];
        } else {
            $response = $user_courses;
            header("internal server error", true, 500);
        }
    }
}

function GetCourseData() {
    global $response;
    global $data;
    session_start();
    if (PostDataCheck(["course_id"], true)) {
        $user_id = $_SESSION["user_id"];
        $course_id = $data["course_id"];

        // Ellenőrzés, hogy a felhasználó benne van-e a kurzusban
        $sql_check_user_in_course_query = "SELECT `ID` FROM `kurzustag` WHERE `KurzusID` = ? AND `FelhasznaloID` = ?;";
        $check_user_in_course = DataQuery($sql_check_user_in_course_query, "ii", [$course_id, $user_id]);
        if (is_array($check_user_in_course)) {
            $sql_course_data_query = "SELECT `kurzus`.`KurzusNev` AS name, `kurzus`.`Leiras` AS 'desc', `kurzus`.`Design` AS design, 
            CONCAT(`felhasznalo`.`VezetekNev`, ' ', `felhasznalo`.`KeresztNev`) AS owner FROM `kurzus` 
            INNER JOIN `felhasznalo` ON `kurzus`.`FelhasznaloID` = `felhasznalo`.`FelhasznaloID` WHERE `kurzus`.`KurzusID` = 8;";
            $course_data = DataQuery($sql_course_data_query, "i", [$course_id]);
    
            if (is_array($course_data)) {
                $response = $course_data;
            } else if ($course_data == "Nincs találat!") {
                $response = [
                    "uzenez" => "Nincs kurzus ilyen ID-val"
                ];
            } else {
                $response = $course_data;
                header("internal server error", true, 500);
            }
        } else {
            $response = [
                "uzenet" => "A felhasználó nem tagja a kurzusnak"
            ];
            header("forbidden", true, 403);
        }
    }
}

if (isset($response)) {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

function PostDataCheck($to_check, $check_session = false) {
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