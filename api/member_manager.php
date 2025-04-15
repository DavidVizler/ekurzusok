<?php

function AddCourseMember() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["code"], "s")) {
        return;
    }

    global $data;
    $code = $data["code"];
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;

    // Kurzus lekérdezése kód alapján
    $sql_statement = "SELECT course_id, archived FROM courses WHERE code = ?;";
    $course_check = DataQuery($sql_statement, "s", [$code]);

    if (count($course_check) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs kurzus a megadott kóddal"
        ], 404);
        return;
    }

    if ($course_check[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    $course_id = $course_check[0]["course_id"];

    // Nincs-e már benne a felhasználó a kurzusban
    $sql_statement = "SELECT membership_id FROM memberships WHERE course_id = ? AND user_id = ?;";
    $joined_check = DataQuery($sql_statement, "ii", [$course_id, $user_id]);

    if (count($joined_check) > 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó már tagja a kurzusnak"
        ], 400);
        return;
    }

    // Felhasználó hozzáadása a kurzushoz
    $sql_statement = "INSERT INTO memberships (membership_id, user_id, course_id, role) VALUES (NULL, ?, ?, 1)";
    $result = ModifyData($sql_statement, "ii", [$user_id, $course_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "A felhasználó felvéve a kurzusba"
        ], 201);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem sikerült felvenni a felhasználót a kurzusba"
        ], 400);
    }
}

function RemoveCourseMember() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["user_id", "course_id"], "ii")) {
        return;
    }

    global $data;
    $owner_user_id = $_COOKIE["user_id"];
    $delete_user_id = $data["user_id"];
    $course_id = $data["course_id"];

    // Tulajdonosa-e a felhasználó a kurzusnak
    $sql_statement = "SELECT role FROM memberships WHERE course_id = ? AND user_id = ?;";
    $course_owner_check = DataQuery($sql_statement, "ii", [$course_id, $delete_user_id]);
    if (count($course_owner_check) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    if (!$course_owner_check[0]["role"] == 3) {
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
    $sql_statement = "DELETE FROM memberships WHERE user_id = ? AND course_id = ?;";
    $result = ModifyData($sql_statement, "ii", [$delete_user_id, $course_id]);
    
    // Eredmény vizsgálata
    if ($result) { 
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Felhasználó eltávolítva a kurzusból"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem sikerült eltávolítani a felhasználót a kurzusból (előfordulhat, hogy nem tagja a kurzusnak)"
        ], 400);
    }
}

function LeaveCourse() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["course_id"], "i")) {
        return;
    }

    global $data;
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;
    $course_id = $data["course_id"];

    // Tagja-e a felhasználó a kurzusnak
    $sql_statement = "SELECT role FROM memberships WHERE course_id = ? AND user_id = ?;";
    $course_owner_check = DataQuery($sql_statement, "ii", [$course_id, $user_id]);
    if (count($course_owner_check) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    // Tulajdonos-e
    if ($course_owner_check[0]["role"] == 3) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A tulajdonos nem léphet ki a kurzusból"
        ], 403);
        return;
    }

    // Kurzus tag eltávolítása
    $sql_statement = "DELETE FROM memberships WHERE user_id = ? AND course_id = ?;";
    $result = ModifyData($sql_statement, "ii", [$user_id, $course_id]);
    
    // Eredmény vizsgálata
    if ($result) { 
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Felhasználó eltávolítva a kurzusból"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem sikerült eltávolítani a felhasználót a kurzusból"
        ], 400);
    }
}

function ChangeMemberTeacherRole() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["course_id", "user_id"], "ii")) {
        return;
    }

    global $data;
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;
    $target_user_id = $data["user_id"];
    $course_id = $data["course_id"];

    // Nem-e a saját státuszát akarja módosítani
    if ($user_id == $target_user_id) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A tulajdonos nem módosíthatja saját tanári státuszát"
        ], 403);
        return;
    }

    // Tagja-e a felhasználó a kurzusnak
    $sql_statement = "SELECT role FROM memberships WHERE course_id = ? AND user_id = ?;";
    $owner_data = DataQuery($sql_statement, "ii", [$course_id, $user_id]);
    if (count($owner_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A bejelentkezett felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    // Tulajdonos-e
    if ($owner_data[0]["role"] != 3) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A bejelentkezett felhasználó nem tulajdonosa a kurzusnak"
        ], 403);
        return;
    }

    $sql_statement = "SELECT archived FROM courses WHERE course_id = ?;";
    $archived_check = DataQuery($sql_statement, "i", [$course_id]);

    if ($archived_check[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    $sql_statement = "SELECT role FROM memberships WHERE user_id = ? AND course_id = ?;";
    $member_data = DataQuery($sql_statement, "ii", [$target_user_id, $course_id]);

    if (count($member_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A módosítandó státuszú felhasználó nem tagja a kurzusnak"
        ], 400);
        return;
    }

    // Tanári státusz módosítása az ellentétére
    $new_status = $member_data[0]["role"] == 1 ? 2 : 1;
    $modification = $new_status == 2 ? "Tanárrá előléptetés " : "Tanári státusz elvétele ";

    $sql_statement = "UPDATE memberships SET role = ? WHERE user_id = ?";
    $result = ModifyData($sql_statement, "ii", [$new_status, $target_user_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "{$modification} sikeres"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "{$modification} sikertelen"
        ], 400);
    }
}

function Manage($action) {
    switch ($action) {
        case "add":
            AddCourseMember();
            break;
        case "remove":
            RemoveCourseMember();
            break;
        case "leave":
            LeaveCourse();
            break;
        case "teacher":
            ChangeMemberTeacherRole();
            break;
        default:
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Hibás műveletmegadás: {$action}"
            ], 400);
            break;
    }
}

?>