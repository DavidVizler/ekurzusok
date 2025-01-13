<?php

function RemoveCourseMember() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["user_id", "course_id"])) {
        return;
    }

    global $data;
    $owner_user_id = $_SESSION["user_id"];
    $delete_user_id = $data["user_id"];
    $course_id = $data["course_id"];

    // Tulajdonosa-e a felhasználó a kurzusnak
    $sql_statement = "SELECT  `FelhasznaloID` FROM `kurzus` WHERE `KurzusID` = ?;";
    $course_owner_check = DataQuery($sql_statement, "i", [$course_id]);
    if ($course_owner_check[0]["FelhasznaloID"] != $owner_user_id) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a kurzusnak"
        ], 403);
        return;
    }

    // Nem-e önmagát akarja eltávolítani a felhasználó a saját kurzusából
    if ($owner_user_id == $delete_user_id) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem távolíthatj el önmagát a kurzusból"
        ], 403);
        return;
    }

    // Kurzus tag eltávolítása
    $sql_statement = "DELETE FROM `kurzustag` WHERE `FelhasznaloID` = ? AND `KurzusID` = ?;";
    $result = ModifyData($sql_statement, "ii", [$delete_user_id, $course_id]);
    
    // Eredmény vizsgálata
    if ($result == "Sikeres művelet!") { 
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Felhasználó eltávolítva a kurzusból"
        ]);
    } else if ($result == "Sikertelen művelet!") {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem sikerült eltávolítani a felhasználót a kurzusból (előfordulhat, hogy nem tagja a kurzusnak)"
        ], 500);
    }
}

function Manage($action) {
    switch ($action) {
        case "remove":
            RemoveCourseMember();
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