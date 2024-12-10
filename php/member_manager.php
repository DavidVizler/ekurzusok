<?php

switch ($action) {
    case "add":
        AddMember();
        break;
    default:
        $response = [
            "sikeres" => false,
            "uzenet" => "Hibás műveletmegadás"
        ];
        header("bad request", true, 400);
        break;
}

function AddMember() {
    global $data;
    global $response;
    if (PostDataCheck(["code"], true)) {
        $code = $data["code"];
        $user_id = $_SESSION["user_id"];

        // Ellenőrzés, hogy van-e kurzus az adott kóddal
        $sql_course_id_query = "SELECT `KurzusID` FROM `kurzus` WHERE `Kod` = ?;";
        $course_id = DataQuery($sql_course_id_query, "s", [$code]);

        if (is_array($course_id)) {
            // Ellenőrzés, hogy a felhasználó nincs-e benne a kurzusban
            $sql_user_in_course_query = "SELECT `ID` FROM `kurzustag` WHERE `FelhasznaloID` = ? AND `KurzusID` = ?;";
            $user_in_course = DataQuery($sql_user_in_course_query, "ii", [$user_id, $course_id]);
            
            if (!is_array($user_in_course)) {
                $sql_member_add = "INSERT INTO `kurzustag` (`ID`, `FelhasznaloID`, `KurzusID`, `Tanar`) VALUES (NULL, ?, ?, '0');";
                $result = ModifyData($sql_member_add, "ii", [$user_id, $course_id[0]["KurzusID"]]);
                
                if ($result == "Sikeres művelet!") { 
                    $response = [
                        "sikeres" => true,
                        "uzenet" => "Felhasználó felvéve a kurzusba"
                    ];
                    header("created", true, 201);
                } else if ($result == "Sikertelen művelet!") {
                    $response = [
                        "sikeres" => false,
                        "uzenet" => "Nem sikerült felvenni a felhasználót a kurzusba"
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
                    "uzenet" => "A felhasználó már tagja a kurzusnak"
                ];
            }
        } else {
            $response = [
                "sikeres" => false,
                "uzenet" => "Nincs kurzus a megadott kóddal"
            ];
        }
    } 
}

?>