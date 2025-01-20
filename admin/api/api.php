<?php

include "../../api/functions.php";

function AdminGetUsers() {
    if (!AdminLoginCheck()) {
        return;
    }

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
    $sql_statement = "SELECT `felhasznalo`.`FelhasznaloID` AS id, `felhasznalo`.`Email` AS email, `felhasznalo`.`VezetekNev` AS lastname, 
    `felhasznalo`.`KeresztNev` AS firstname, COUNT(`kurzustag`.`ID`) AS courses 
    FROM `felhasznalo` 
    LEFT JOIN `kurzustag` ON `felhasznalo`.`FelhasznaloID` = `kurzustag`.`FelhasznaloID` 
    GROUP BY `felhasznalo`.`FelhasznaloID`
    LIMIT ? OFFSET ?;";
    $users = DataQuery($sql_statement, "ii", [$limit, $offset]);

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
    LIMIT ? OFFSET ?;";
    $user_own_course_count = DataQuery($sql_statement, "ii", [$limit, $offset]);

    SendResponse([$users, $user_own_course_count]);
}

function AdminGetCourses() {
    if (!AdminLoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["rows", "page"])) {
        return;
    }

    global $data;
    $limit = $data["rows"];
    $offset = $limit*($data["page"]-1);

    // ID, név, kód, leirás, archivált, tulajdonos adatok, tagok száma
    $sql_statement = "SELECT `kurzus`.`KurzusID` AS 'id', `kurzus`.`KurzusNev` AS 'name', `kurzus`.`Kod` AS 'code', `kurzus`.`Leiras` AS 'desc', `kurzus`.`Archivalt` AS 'archived', 
    `felhasznalo`.`FelhasznaloID` AS 'owner_id', `felhasznalo`.`VezetekNev` AS 'owner_lastname', `felhasznalo`.`KeresztNev` AS 'owner_firstname', `felhasznalo`.`Email` AS 'owner_email', 
    COUNT(`kurzustag`.`ID`) AS 'members_count'
    FROM `kurzus`
    INNER JOIN `felhasznalo` ON `kurzus`.`FelhasznaloID` = `felhasznalo`.`FelhasznaloID`
    INNER JOIN `kurzustag` ON `kurzus`.`KurzusID` = `kurzustag`.`KurzusID` 
    GROUP BY `kurzus`.`KurzusID` 
    LIMIT ? OFFSET ?;";
    $courses = DataQuery($sql_statement, "ii", [$limit, $offset]);

    if (!is_array($courses)) {
        SendResponse([
            "uzenet" => "Nincsenek kurzusok az adatbázisban"
        ]);
        return;
    }

    // Tanárok száma
    $sql_statement = "SELECT COUNT(`kurzustag`.`ID`) AS 'teachers_count'
    FROM `kurzus`
    INNER JOIN `kurzustag` ON `kurzus`.`KurzusID` = `kurzustag`.`KurzusID`
    WHERE `kurzustag`.`Tanar` = 1
    GROUP BY `kurzus`.`KurzusID`
    LIMIT ? OFFSET ?;";
    $teachers_count = DataQuery($sql_statement, "ii", [$limit, $offset]);

    SendResponse([$courses, $teachers_count]);

}

function AdminLogin() {
    if (!PostDataCheck(["username", "password"])) {
        return;
    }
    global $data;

    $sql_statement = "SELECT `AdminJelszo`, `AdminID` FROM `admin` WHERE `FelhasznaloNev` = ?;";
    $admin_data = DataQuery($sql_statement, "s", [$data["username"]]);

    if (!is_array($admin_data)) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Helytelen felhasználónév vagy jelszó"
        ]);
        return;
    }

    if (!password_verify($data["password"], $admin_data[0]["AdminJelszo"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Helytelen felhasználónév vagy jelszó"
        ]);
        return;
    }  

    session_start();
    $_SESSION["admin_id"] = $admin_data[0]["AdminID"];
    SendResponse([
        "sikeres" => true,
        "uzenet" => "Admin bejelentkezve"
    ]);
}

function AdminRegister() {
    
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
}

$action = end(explode("/", $_SERVER["REQUEST_URI"]));
switch($action) {
    case "get-users":
        AdminGetUsers();
        break;
    case "get-courses":
        AdminGetCourses();
        break;
    case "login":
        AdminLogin();
        break;
    default:
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Hibás műveletmegadás"
        ], 400);
        break;
}

?>