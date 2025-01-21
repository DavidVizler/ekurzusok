<?php

// PHP hibakijelzés konfiguráció
//error_reporting(0);

// Adatbázis konfiguráció
$db_config = [
    "db_host" => "localhost",
    "db_name" => "ekurzusok",
    "db_query_username" => "root",
    "db_query_password" => "",
    "db_modifier_username" => "root",
    "db_modifier_password" => ""
];

// SQL lekérdezés
function DataQuery($operation, $var_types = null, $parameters = null) {
    global $db_config;

    $db = new mysqli(
        $db_config["db_host"], 
        $db_config["db_query_username"], 
        $db_config["db_query_password"], 
        $db_config["db_name"]
    );

    if ($db -> connect_errno != 0) {
        SendResponse([
            "hiba" => "Nem sikerült kapcsolódni az adatbázishoz"
        ], 500);
        exit;
    }

    if (!is_null($var_types) && !is_null($parameters)) {
        $statement = $db -> prepare($operation);
        $statement -> bind_param($var_types, ...$parameters);
        $statement -> execute();
        $results = $statement -> get_result();
    } else {
        $results = $db -> query($operation);
    }

    if ($db -> errno != 0) {
        SendResponse([
            "hiba" => "Hiba adótott az adatbázis művelet végrehajtásakor"
        ], 500);
        exit;
    }

    if ($results -> num_rows == 0) {
        return "Nincs találat!";
    }

    return $results -> fetch_all(MYSQLI_ASSOC);
}

// SQL adatmódosítás
function ModifyData($operation, $var_types = null, $parameters = null) {
    global $db_config;
    
    $db = new mysqli(
        $db_config["db_host"], 
        $db_config["db_modifier_username"], 
        $db_config["db_modifier_password"], 
        $db_config["db_name"]
    );

    if ($db -> connect_errno != 0) {
        SendResponse([
            "hiba" => "Nem sikerült kapcsolódni az adatbázishoz"
        ], 500);
        exit;
    }

    if (!is_null($var_types) && !is_null($parameters)) {
        $statement = $db -> prepare($operation);
        $statement -> bind_param($var_types, ...$parameters);
        $statement -> execute();
    } else {
        $db -> query($operation);
    }

    if ($db -> errno != 0) {
        SendResponse([
            "hiba" => "Hiba adótott az adatbázis művelet végrehajtásakor"
        ], 500);
        exit;
    }

    return $db -> affected_rows > 0 ? "Sikeres művelet!" : "Sikertelen művelet!";
}

// Válasz elküldő
function SendResponse($response, $status = 200) {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    http_response_code($status);
}

// HTTP kérés metódus vizsgálata
function CheckMethod($method) {
    if ($_SERVER["REQUEST_METHOD"] != $method) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Hibás metódus"
        ], 400);
        return false;
    } else {
        return true;
    }
}

// POST-tal érkezett adatok ellenőrzése
function PostDataCheck($to_check, $send_response = true) {
    global $data;
    foreach ($to_check as $tc) {
        if (empty($data[$tc]) && $data[$tc] != 0 && $data[$tc] != false) {
            if ($send_response) {
                SendResponse([
                    "sikeres" => false,
                    "uzenet" => "Hiányos adatok"
                ], 400);
            }
            return false;
        }
    }
    return true;
}

// Bejelentkezés ellenőrzése
function LoginCheck() {
    session_start();
    if (!isset($_SESSION["user_id"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nincs bejelentkezve"
        ], 401);
        return false;
    }
    return true;
}

// Admin bejelentkezés ellenőrzése
function AdminLoginCheck() {
    session_start();
    if (!isset($_SESSION["admin_id"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs admin bejelentkezve"
        ], 401);
        return false;
    }
    return true;
}

?>