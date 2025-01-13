<?php

function CourseMembersQuery() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["course_id"])) {
        return;
    }

    global $data;
    $user_id = $_SESSION["user_id"];
    $course_id = $data["course_id"];

    // Benne van-e a felhasználó a kurzusban
    $sql_statement = "SELECT `ID` FROM `kurzustag` WHERE `KurzusID` = ? AND `FelhasznaloID` = ?;";
    $user_in_course_check = DataQuery($sql_statement, "ii", [$course_id, $user_id]);
    if (!is_array($user_in_course_check)) {
        SendResponse([
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    // Ha tulajdonos, akkor a tagok ID-ja is lekérdezésre kerül, hogy el tudja őket távolítani a kurzusból
    $sql_statement = "SELECT `kurzus`.`FelhasznaloID` FROM `kurzus` WHERE `kurzus`.`KurzusID` = ?;";
    $course_owner_check = DataQuery($sql_statement, "i", [$course_id]);
    if ($course_owner_check[0]["FelhasznaloID"] == $user_id) {
        $sql_statement = "SELECT `felhasznalo`.`FelhasznaloID` AS user_id, `felhasznalo`.`VezetekNev` AS lastname, `felhasznalo`.`KeresztNev` AS firstname 
        FROM `felhasznalo` INNER JOIN `kurzustag` ON `felhasznalo`.`FelhasznaloID` = `kurzustag`.`FelhasznaloID`
        WHERE `kurzustag`.`KurzusID` = ?;";
    } else {
        $sql_statement = "SELECT `felhasznalo`.`VezetekNev` AS lastname, `felhasznalo`.`KeresztNev` AS firstname 
        FROM `felhasznalo` INNER JOIN `kurzustag` ON `felhasznalo`.`FelhasznaloID` = `kurzustag`.`FelhasznaloID`
        WHERE `kurzustag`.`KurzusID` = ?;";
    }

    // Kurzus tagok lekérdezése
    $course_members = DataQuery($sql_statement, "i", [$course_id]);
    if (is_array($course_members)) {
        SendResponse($course_members);
    } else {
        SendResponse([
            "uzenet" => "Nincs kurzus ilyen ID-val"
        ]);
    }
}

function Manage($action) {
    switch ($action) {
        case "course-members":
            CourseMembersQuery();
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