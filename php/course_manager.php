<?php

include "./sql_functions.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $url = explode("/", $_SERVER["REQUEST_URI"]);
    $endpoint = end($url);

    function GenerateCourseCode() {
        $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $code = "";

        for ($i = 0; $i < 10; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $code;
    }

    function Create() {
        $data = json_decode(file_get_contents("php://input"), true);

        // Érkezett adatok ellenőrzése
        if (!empty($data["owner_id"]) && !empty($data["name"]) && !empty($data["desc"]) && !empty($data["design"])) {
            $new_course_data = [
                $data["owner_id"],
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
                ModifyData($sql_add_course_owner, "ii", [$data["owner_id"], DataQuery("SELECT MAX(`KurzusID`) AS newid FROM `kurzus`")[0]["newid"]]);

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
        } else {
            $response = [
                "sikeres" => false,
                "uzenet" => "Hiányos adatok!"
            ];
            header("bad request", true, 400);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    function Delete() {
        $data = json_decode(file_get_contents("php://input"), true);

        // Érkezett adatok ellenőrzése
        if (!empty($data["id"]) && !empty($data["userid"]) && !empty($data["password"])) {
            $id = $data["id"];
            $userid = $data["userid"];
            $password = $data["password"];

            // Titkosított jelszó lekérdezése
            $sql_user_password_check_query = "SELECT `Jelszo` FROM `felhasznalo` WHERE `FelhasznaloID` = ?;";
            $user_password = DataQuery($sql_user_password_check_query, "i", [$userid]);

            // Ha van az ID-hez felhasználó, akkor jelszó összehasonlítása
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
        } else {
            $response = ["torles" => "hianyos adatok"];
            header("bad request", true, 400);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    switch ($endpoint) {
        case "create":
            Create();
            break;
        case "delete":
            Delete();
            break;
        default:
            header("not found", true, 404);
            break;
    }
} else {
    $response = [
        "sikeres" => false,
        "uzenet" => "Nem megfelelő metódus"
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    header("bad request", true, 400);
}

?>