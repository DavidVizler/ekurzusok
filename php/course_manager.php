<?php

switch ($action) {
    case "create":
        CreateCourse();
        break;
    case "modify":
        ModifyCourse();
        break;
    case "delete-as-admin":
        DeleteCourseAsAdmin();
        break;
    default:
        $response = [
            "sikeres" => false,
            "uzenet" => "Hibás műveletmegadás"
        ];
        header("bad request", true, 400);
        break;
}

function GenerateCourseCode() {
    $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $code = "";

    for ($i = 0; $i < 10; $i++) {
        $code .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $code;
}

function CreateCourse() {
    global $data;
    global $response;
    session_start();     
    if (PostDataCheck(["name", "desc", "design"], true)) {
        $new_course_data = [
            $_SESSION["user_id"],
            $data["name"],
            null,
            $data["desc"],
            $data["design"]
        ];

        // Kurzuskód generálása
        $sql_used_codes_query = "SELECT `Kod` FROM `kurzus`;";
        $used_codes = DataQuery($sql_used_codes_query);

        $code = GenerateCourseCode();

        // Újragenerálás, ha már létezik a kód
        if (is_array($used_codes)) {
            while (in_array($code, array_values($used_codes))) {
                $code = GenerateCourseCode();
            }
        }
        
        $new_course_data[2] = $code;

        $sql_course_create = "INSERT INTO `kurzus` (`KurzusID`, `FelhasznaloID`, `KurzusNev`, `Kod`, `Leiras`, `Design`, `Archivalt`) 
        VALUES (NULL, ?, ?, ?, ?, ?, 0);";
        $result = ModifyData($sql_course_create, "isssi", $new_course_data);

        if ($result == "Sikeres művelet!") {
            $sql_add_course_owner = "INSERT INTO `kurzustag` (`ID`, `FelhasznaloID`, `KurzusID`, `Tanar`) VALUES (NULL, ?, ?, '1');";
            ModifyData($sql_add_course_owner, "ii", [$_SESSION["user_id"], DataQuery("SELECT MAX(`KurzusID`) AS newid FROM `kurzus`")[0]["newid"]]);

            $response = [
                "sikeres" => true,
                "uzenet" => "Kurzus létrehozva!"
            ];
            header("created", true, 201);
        } else if ($result == "Sikertelen művelet!") {
            $response = [
                "sikeres" => false,
                "uzenet" => "Nem sikerült létrehozni a kurzust!"
            ];
            header("internal server error", true, 500);
        } else {
            $response = [
                "sikeres" => false,
                "uzenet" => $result
            ];
            header("internal server error", true, 500);
        }
    }
}

function ModifyCourse() {
    global $data;
    global $response;
    session_start();     
    if (PostDataCheck(["name", "desc", "design", "course_id"], true)) {
        $modified_course_data = [
            $data["name"],
            $data["desc"],
            $data["design"],
            $data["course_id"]
        ];

        $sql_course_modify = "UPDATE `kurzus` SET `KurzusNev` = ?, `Leiras` = ?, `Design` = ? WHERE `kurzus`.`KurzusID` = ?;";
        $result = ModifyData($sql_course_modify, "ssii", $modified_course_data);

        if ($result == "Sikeres művelet!") {
            $response = [
                "sikeres" => true,
                "uzenet" => "Kurzus módosítva!"
            ];
            header("created", true, 201);
        } else if ($result == "Sikertelen művelet!") {
            $response = [
                "sikeres" => false,
                "uzenet" => "Nem sikerült módosítani a kurzust!"
            ];
            header("internal server error", true, 500);
        } else {
            $response = [
                "sikeres" => false,
                "uzenet" => $result
            ];
            header("internal server error", true, 500);
        }
    }
}

function DeleteCourseAsAdmin() {
    global $data;
    global $response;
    if (PostDataCheck(["id", "user_id", "password"], false)) {
        $id = $data["id"];
        $userid = $data["user_id"];
        $password = $data["password"];

        // Titkosított jelszó lekérdezése
        $sql_user_password_check_query = "SELECT `Jelszo` FROM `felhasznalo` WHERE `FelhasznaloID` = ?;";
        $user_password = DataQuery($sql_user_password_check_query, "i", [$userid]);

        // Ha van az ID-hez kurzus, akkor jelszó összehasonlítása
        if (is_array($user_password)) {
            if ($password == $user_password[0]["Jelszo"]) {
                $sql_course_delete = "DELETE FROM `kurzus` WHERE `KurzusID` = ?;";
                $result = ModifyData($sql_course_delete, "i", [$id]);
                $response = ["torles" => $result];
            } else {
                $response = ["torles" => "sikertelen"];
            }
        } else {
            $response = ["torles" => "sikertelen"];
        }
    }

}

?>