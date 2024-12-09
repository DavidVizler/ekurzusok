<?php

include "./sql_functions.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data["manage"]) || empty($data["action"])) {
        $response = [
            "sikeres" => false,
            "uzenet" => "Hiányos adatok"
        ];
        header("bad request", true, 400);
    } else {
        $manage = $data["manage"];
        $action = $data["action"];

        if (in_array($manage,  ["user", "course", "member", "content", "assignment"])) {
            include "./{$manage}_manager.php";
        } else {
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