<?php

// SQL lekérdezés
function DataQuery($operation, $var_types = null, $parameters = null) {
    $db = new mysqli("localhost", "root", "", "ekurzusok");
    if ($db -> connect_errno != 0) {
        return $db -> connect_error;
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
        return $db -> error;
    }

    if ($results -> num_rows == 0) {
        return "Nincs találat!";
    }

    return $results -> fetch_all(MYSQLI_ASSOC);
}

// SQL adatmódosítás
function ModifyData($operation, $var_types = null, $parameters = null) {
    $db = new mysqli("localhost", "root", "", "ekurzusok");

    if ($db -> connect_errno != 0) {
        return $db -> connect_error;
    }

    if (!is_null($var_types) && !is_null($parameters)) {
        $statement = $db -> prepare($operation);
        $statement -> bind_param($var_types, ...$parameters);
        $statement -> execute();
    } else {
        $db -> query($operation);
    }

    if ($db -> errno != 0) {
        return $db -> error;
    }

    return $db -> affected_rows > 0 ? "Sikeres művelet!" : "Sikertelen művelet!";
}

// Válasz elküldő
function SetResponse($response, $status = 200) {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    http_response_code($status);
}

// HTTP kérés metódus vizsgálata
function CheckMethod($method) {
    if ($_SERVER["REQUEST_METHOD"] != $method) {
        SetResponse([
            "sikeres" => false,
            "uzenet" => "Hibás metódus"
        ], 400);
        return false;
    } else {
        return true;
    }
}

// POST-tal érkezett adatok és bejelentkezés ellenőrzése
function PostDataCheck($to_check) {
    global $data;
    foreach ($to_check as $tc) {
        if (empty($data[$tc])) {
            SetResponse([
                "sikeres" => false,
                "uzenet" => "Hiányos adatok"
            ], 400);
            return false;
        }
    }
    return true;
}

// Bejelentkezés ellenőrzése
function LoginCheck() {
    global $response;
    if (!isset($_SESSION["user_id"])) {
        SetResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nincs bejelentkezve"
        ], 401);
        return false;
    }
    return true;
}

?>