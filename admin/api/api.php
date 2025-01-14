<?php

function AdminGetUserCount() {
    if (!CheckMethod("GET")) {
        return;
    }

    $sql_statement = "SELECT COUNT(`FelhasznaloID`) AS user_count FROM `felhasznalo`";
    $user_count = DataQuery($sql_statement);
    if (is_array($user_count)) {
        SendResponse($user_count);
    } else {
        SendResponse([
            "uzenet" => "Hiba történt"
        ], 500);
    }
}

function AdminGetUsers() {
    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["rows", "page"])) {
        return;
    }

    global $data;
    $limit = $data["rows"];
    $offset = $limit*($data["page"]-1);

    // ID, e-mail, vezetéknév, keresztnév, titkosított jelszó, kurzusok
    $sql_statement = "SELECT `felhasznalo`.`FelhasznaloID`, `felhasznalo`.`Email`, `felhasznalo`.`VezetekNev`, 
    `felhasznalo`.`KeresztNev`, `felhasznalo`.`Jelszo`, COUNT(`kurzustag`.`ID`) AS courses 
    FROM `felhasznalo` 
    LEFT JOIN `kurzustag` ON `felhasznalo`.`FelhasznaloID` = `kurzustag`.`FelhasznaloID` 
    GROUP BY `felhasznalo`.`FelhasznaloID`
    LIMIT {$limit} OFFSET {$offset};";
    $users = DataQuery($sql_statement);

    if (!is_array($users)) {
        SendResponse([
            "uzenet" => "Nincsenek felhasználók az adatbázisban"
        ]);
        return;
    }

    // Saját kurzusok száma
    $sql_statement = "SELECT `felhasznalo`.`FelhasznaloID`, COUNT(`kurzus`.`KurzusID`) AS courses 
    FROM `felhasznalo`
    LEFT JOIN `kurzus` ON `felhasznalo`.`FelhasznaloID` = `kurzus`.`FelhasznaloID` 
    GROUP BY `felhasznalo`.`FelhasznaloID`
    LIMIT {$limit} OFFSET {$offset};";
    $user_own_course_count = DataQuery($sql_statement);

    SendResponse([$users, $user_own_course_count]);
}

function Manage($action) {
    session_start();
    if (!isset($_SESSION["admin_id"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs admin bejelentkezve"
        ], 401);
    }

    switch($action) {
        case "get-users":
            AdminGetUsers();
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