<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "./sql_functions.php";
    $data = json_decode(file_get_contents("php://input"), true);

    function CreateUser() {
        global $data;

        // Érkezett adatok ellenőrzése
        if (!empty($data["email"]) && !empty($data["lastname"]) && !empty($data["firstname"]) && !empty($data["password"])) {
            $email = $data["email"];
            $lastname = $data["lastname"];
            $firstname = $data["firstname"];
            $password = $data["password"];
            
            // Ellenőrzés, hogy nincs-e már felhasználó regisztrálva azonos e-mail címmel
            $sql_email_check_query = "SELECT `Email` FROM `felhasznalo` WHERE `Email` = ?";
            $email_check = DataQuery($sql_email_check_query, "s", [$email]);
            if (!is_array($email_check)) {
                // Jelszó titkosítása
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql_user_upload = "INSERT INTO `felhasznalo` (`FelhasznaloID`, `Email`, `VezetekNev`, `KeresztNev`, `Jelszo`) 
                VALUES (NULL, ?, ?, ?, ?);";

                $result = ModifyData($sql_user_upload, "ssss", [$email, $lastname, $firstname, $hashed_password]);

                if ($result == "Sikeres művelet!") { 
                    $response = [
                        "sikeres" => true,
                        "uzenet" => "Felhasználó regisztrálva"
                    ];
                    header("created", true, 201);
                } else if ($result == "Sikertelen művelet!") { // Nincs módosított sor az adatbázisban ($db->affected_rows = 0)
                    $response = [
                        "sikeres" => false,
                        "uzenet" => "Nem sikerült feltölteni a felhasználót"
                            ];
                    header("internal server error", true, 500);
                } else { // SQL hiba (uzenet = $db->error vagy $db->connect_error)
                    $response = [
                        "sikeres" => false,
                        "uzenet" => $result
                    ];
                    header("internal server error", true, 500);
                }  
            } else {
                $response = [
                    "sikeres" => false,
                    "uzenet" => "E-mail cím már regisztrálva van"
                ];
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

    function LoginUser() {
        global $data;

        // Érkezett adatok ellenőrzése
        if (!empty($data["email"]) && !empty($data["password"])) {
            $email = $data["email"];
            $password = $data["password"];

            $sql_user_data_query = "SELECT `FelhasznaloID`, `Jelszo` FROM `felhasznalo` WHERE `Email` = ?;";
            $user_data = DataQuery($sql_user_data_query, "s", [$email]);

            // Ellenőrzés, hogy van-e felhasználó az adott e-mail címmel
            if (is_array($user_data)) {
                // Jelszó ellenőrzése
                if (password_verify($password, $user_data[0]["Jelszo"])) {
                    // Session elindítása
                    session_start();
                    $_SESSION["user_id"] = $user_data[0]["FelhasznaloID"];

                    $response = [
                        "sikeres" => true,
                        "uzenet" => "Sikeres bejelentkezés"
                    ];          
                } else {
                    $response = [
                        "sikeres" => false,
                        "uzenet" => "Email vagy jelszó nem megfelelő"
                    ];
                }
            } else {
                $response = [
                    "sikeres" => false,
                    "uzenet" => "Email vagy jelszó nem megfelelő"
                ];
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

    function LogoutUser() {
        session_start();
        session_unset();
        session_destroy();

        echo json_encode([
            "sikeres" => true,
            "uzenet" => "Felhasználó kijelentkeztetve"
        ], JSON_UNESCAPED_UNICODE);
    }

    function DeleteUser() {
        global $data;
        session_start();

        // Érkezett adatok ellenőrzése
        if (isset($_SESSION["user_id"]) && isset($data["password"])) {
            $id = $data["user_id"];
            $password = $data["password"];

            // Titkosított jelszó lekérdezése
            $sql_password_check_query = "SELECT `Jelszo` FROM `felhasznalo` WHERE `FelhasznaloID` = ?;";
            $password_check = DataQuery($sql_password_check_query, "i", [$id]);

            // Ha van az ID-hez felhasználó, akkor jelszó összehasonlítása
            if (is_array($password_check)) {
                if (password_verify($password, $password_check[0]["Jelszo"])) {
                    $sql_user_delete = "DELETE FROM `felhasznalo` WHERE `FelhasznaloID` = ?;";
                    $result = ModifyData($sql_user_delete, "i", [$id]);
                    
                    if ($result == "Sikeres művelet!") { 
                        session_unset();
                        session_destroy();
                        $response = [
                            "sikeres" => true,
                            "uzenet" => "Felhasználó törölve"
                        ];
                    } else if ($result == "Sikertelen művelet!") { // Nincs módosított sor az adatbázisban ($db->affected_rows = 0)
                        $response = [
                            "sikeres" => false,
                            "uzenet" => "Nem sikerült törölni a felhasználót"
                                ];
                        header("internal server error", true, 500);
                    } else { // SQL hiba (uzenet = $db->error vagy $db->connect_error)
                        $response = [
                            "sikeres" => false,
                            "uzenet" => $result
                        ];
                        header("internal server error", true, 500);
                    }  

                } else {
                    $response = [
                        "sikeres" => false,
                        "uzenet" => "Nem megfelelő jelszó"
                    ];
                }
            } else {
                $response = [
                    "sikeres" => false,
                    "uzenet" => "A felhasználó már törölve van"
                ];
            }
        } else {
            $response = ["torles" => "hianyos adatok"];
            header("bad request", true, 400);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    function CreateCourse() {
        global $data;
        session_start();     
           
        // Érkezett adatok ellenőrzése
        if (isset($_SESSION["user_id"]) && !empty($data["name"]) && !empty($data["desc"]) && !empty($data["design"])) {
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
        } else {
            $response = [
                "sikeres" => false,
                "uzenet" => "Hiányos adatok!"
            ];
            header("bad request", true, 400);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    function AddCourseMember() {
        global $data;

        // Érkezett adatok ellenőrzése
        if (!empty($data["user_id"]) && !empty($data["course_id"])) {
            $new_data = [$data["user_id"], $data["course_id"]];

            $sql_course_member_upload = "INSERT INTO `kurzustag` (`ID`, `FelhasznaloID`, `KurzusID`, `Tanar`) VALUES (NULL, ?, ?, '0');";
            $result = ModifyData($sql_course_member_upload, "ii", $new_data);

            if ($result == "Sikeres művelet!") {
                $response = [
                    "sikeres" => true,
                    "uzenet" => "Felhasználó felvéve a kurzusba"
                ];
                header("created", true, 201);
            } else if ($result == "Sikertelen művelet!") {
                $response = [
                    "sikeres" => false,
                    "uzenez" => "Nem sikerült felvenni a felhasználót a kurzusba"
                ];
                header("internal server error", true, 500);
            } else {
                $response = [
                    "sikeres" => false,
                    "uzenez" => $result
                ];
                header("internal server error", true, 500);
            }
        } else {
            $response = [
                "sikeres" => false,
                "uzenet" => "Hiányos adatok"
            ];
            header("bad request", true, 400);
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    if (!empty($data["action"])) {
        switch ($data["action"]) {
            case "create user":
                CreateUser();
                break;
            case "login user":
                LoginUser();
                break;
            case "logout user":
                LogoutUser();
                break;
            case "modify user data":
                break;
            case "modify user password":
                break;
            case "delete user":
                DeleteUser();
                break;
            case "create course":
                CreateCourse();
                break;
            case "modify course data":
                break;
            case "delete course":
                break;
            case "add course member":
                AddCourseMember();
                break;
            case "modify course teacher":
                break;
            case "remove course member":
                break;
            case "create course content":
                break;
            case "course content upload file":
                break;
            case "course content remove file":
                break;
            case "hand in assigment":
                break;
            case "assigment upload file":
                break;
            case "assigment remove file":
                break;
            case "rate assigment":
                break;
            default:
                $response = [
                    "sikeres" => false,
                    "uzenet" => "Nem elérhető művelet"
                ];
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                header("bad request", true, 400);
                break;
        }
    }
}

    

    // Ez lesz a fő adatváltoztató műveleteket végző PHP fájl
    // user_manager és course_manager törölve lesz
    
    

    // URL végpont megállapítás
    $url = explode("/", $_SERVER["REQUEST_URI"]);
    $url_vege = end($url);

    
    

?>