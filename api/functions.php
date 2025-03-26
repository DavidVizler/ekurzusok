<?php

// PHP hibakijelzés konfiguráció
//error_reporting(0);
ini_set('display_errors', '1');

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

    return $db -> affected_rows;
}

// Válasz elküldő
function SendResponse($response, $status = 200) {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    if ($status != 200) {
        http_response_code($status);
    }
}

// HTTP kérés metódus vizsgálata
function CheckMethod($method) {
    if ($_SERVER["REQUEST_METHOD"] != $method) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Hibás metódus"
        ], 405);
        return false;
    } else {
        return true;
    }
}

/*
function PostDataCheck($to_check, $send_response = true) {
    global $data;
    if (is_null($data)) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Hiányos adatok"
        ], 400);
        return false;
    }

    foreach ($to_check as $tc) {
        if (!isset($data[$tc]) || (empty($data[$tc]) && $data[$tc] !== 0 && $data[$tc] !== false)) {
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
*/

function SendInvalidDataRespone($send_success, $key, $value) {
    if (is_string($value)) {
        $print_value = "'{$value}'";
    } else if (is_null($value)) {
        $print_value = "NULL";
    } else if (is_numeric($value)) {
        $print_value = $value;
    } else if (is_bool($value)) {
        $print_value = $value ? "true" : "false";
    } else {
        $print_value = "[object]";
    }

    if ($send_success) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Érvénytelen adat ({$key} : {$print_value})"
        ], 400);
    } else {
        SendResponse([
            "uzenet" => "Érvénytelen adat ({$key} : {$print_value})"
        ], 400);
    }
}

/* POST-tal érkezett adatok ellenőrzése

    b -> bool (true vagy false)
    s -> string (nem üres)
    e -> empty string (lehet üres)
    i -> integer number (csak egész szám)
    n -> nullable integer (egész szám vagy NULL)
    d -> datetime (yyyy-MM-dd hh:mm:ss vagy NULL)

*/
function PostDataCheck($to_check, $data_types, $send_response = true, $send_success = true) {
    // POST-tal érkezett adatok
    global $data;

    // Vannak adatok?
    if (is_null($data)) {
        if ($send_response && $send_success) {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Hiányos adatok"
            ], 400);
        } else if ($send_response) {
            SendResponse([
                "uzenet" => "Hiányos adatok"
            ], 400);
        }
        return false;
    }

    // Adatok és típusok számának ellenőrzése
    if (count($to_check) != strlen($data_types)) {
        http_response_code(500);
        return false;
    }

    // Adatok ellenőrzése
    for ($i = 0; $i < count($to_check); $i++) {
        // Létezik-e
        if (!array_key_exists($to_check[$i], $data)) {
            if ($send_response && $send_success) {
                SendResponse([
                    "sikeres" => false,
                    "uzenet" => "Hiányos adat: {$to_check[$i]}"
                ], 400);
            } else if ($send_response) {
                SendResponse([
                    "uzenet" => "Hiányos adat: {$to_check[$i]}"
                ], 400);
            }
            return false;
        }

        $tc = $data[$to_check[$i]];
        $key = $to_check[$i];

        // Megfelelő típusú-e
        switch ($data_types[$i]) {
            case "b":
                if (!is_bool($tc)) {
                    if ($send_response) SendInvalidDataRespone($send_success, $to_check[$i], $tc);
                    return false;
                }
                break;
            case "s":
                if (!is_string($tc) || $tc == "") {
                    if ($send_response) SendInvalidDataRespone($send_success, $key, $tc);
                    return false;
                }
                break;
            case "e":
                if (!is_string($tc)) {
                    if ($send_response) SendInvalidDataRespone($send_success, $key, $tc);
                    return false;
                }
                break;
            case "i":
                if (!is_int($tc)) {
                    if ($send_response) SendInvalidDataRespone($send_success, $key, $tc);
                    return false;
                }
                break;
            case "n":
                if (!is_int($tc) && !is_null($tc)) {
                    if ($send_response) SendInvalidDataRespone($send_success, $key, $tc);
                    return false;
                }
                break;
            case "d":
                if (!is_null($tc)) {
                    $date_format = 'Y-m-d\TH:i:s.v\Z'; // ISO 8601
                    $date = DateTime::createFromFormat($date_format, $tc);
                    if (!$date || $date->format($date_format) !== $tc) {
                        if ($send_response) SendInvalidDataRespone($send_success, $key, $tc);
                        return false;
                    }
                    break;
                }
        }
    }

    return true;

}

// Bejelentkezés ellenőrzése
function LoginCheck($send_response = true) {
    session_start();
    if (!isset($_SESSION["user_id"])) {
        if ($send_response) {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "A felhasználó nincs bejelentkezve"
            ], 401);
        }
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

// Fájl feltöltés
function FileUpload($id, $attach_to) {
    for ($i = 0; $i < count($_FILES["files"]["name"]); $i++) {
        $file_name = $_FILES["files"]["name"][$i];
        $file_size = $_FILES["files"]["size"][$i] / 1000; // Byte -> KB

        // Fájl ID meghatározása
        $sql_statement = "SELECT MAX(file_id) AS max_id FROM files;";
        $files = DataQuery($sql_statement)[0]["max_id"];
        if (is_null($files)) {
            $file_id = 1;
        } else {
            $file_id = $files + 1;
        }
    
        // Fájl eltárolása
        if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], "../files/" . $file_id)) {
            // Fájl adatok feltöltése adatbázisba
            if ($attach_to == "content") {
                $sql_statement = "INSERT INTO files (file_id, content_id, submission_id, name, size) VALUES (?, ?, NULL, ?, ?);";
            } else if ($attach_to == "submission") {
                $sql_statement = "INSERT INTO files (file_id, content_id, submission_id, name, size) VALUES (?, NULL, ?, ?, ?);";
            }
    
            ModifyData($sql_statement, "iisi", [$file_id, $id, $file_name, $file_size]);
        }
    }
}

?>