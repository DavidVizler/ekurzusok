<?php

include "./sql_functions.php";

// Metódus ellenőrzése
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function Signup() {
        $data = json_decode(file_get_contents("php://input"), true);
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

    function Login() {
        $data = json_decode(file_get_contents("php://input"), true);
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

    function Delete() {
        $data = json_decode(file_get_contents("php://input"), true);

        // Érkezett adatok ellenőrzése
        if (!empty($data["id"]) && !empty($data["password"])) {
            $id = $data["id"];
            $password = $data["password"];

            // Titkosított jelszó lekérdezése
            $sql_password_check_query = "SELECT `Jelszo` FROM `felhasznalo` WHERE `FelhasznaloID` = ?;";
            $password_check = DataQuery($sql_password_check_query, "i", [$id]);

            // Ha van az ID-hez felhasználó, akkor jelszó összehasonlítása
            if (is_array($password_check)) {
                if ($password == $password_check[0]["Jelszo"]) {
                    $sql_user_delete = "DELETE FROM `felhasznalo` WHERE `FelhasznaloID` = ?;";
                    $result = ModifyData($sql_user_delete, "i", [$id]);
                    
                    if ($result == "Sikeres művelet!") { 
                        $valasz = [
                            "sikeres" => true,
                            "uzenet" => "Felhasználó törölve"
                        ];
                    } else if ($result == "Sikertelen művelet!") { // Nincs módosított sor az adatbázisban ($db->affected_rows = 0)
                        $valasz = [
                            "sikeres" => false,
                            "uzenet" => "Nem sikerült törölni a felhasználót"
                                ];
                        header("internal server error", true, 500);
                    } else { // SQL hiba (uzenet = $db->error vagy $db->connect_error)
                        $valasz = [
                            "sikeres" => false,
                            "uzenet" => $result
                        ];
                        header("internal server error", true, 500);
                    }  

                } else {
                    $valasz = [
                        "sikeres" => false,
                        "uzenet" => "Nem megfelelő jelszó"
                    ];
                }
            } else {
                $valasz = [
                    "sikeres" => false,
                    "uzenet" => "Nincs felhasználó ilyen ID-val"
                ];
            }
        } else {
            $valasz = ["torles" => "hianyos adatok"];
            header("bad request", true, 400);
        }

        echo json_encode($valasz, JSON_UNESCAPED_UNICODE);
    }

    // URL végpont megállapítás
    $url = explode("/", $_SERVER["REQUEST_URI"]);
    $url_vege = end($url);
    
    switch ($url_vege) {
        case "signup":
            Signup();
            break;
        case "login":
            Login();
            break;
        case "delete":
            Delete();
            break;
        default:
            $valasz = [
                "sikeres" => false,
                "uzenet" => "Nem megfelelő URL végpont"
            ];
            echo json_encode($valasz, JSON_UNESCAPED_UNICODE);
            header("not found", true, 404);
            break;
    }
} else {
    $valasz = [
        "sikeres" => false,
        "uzenet" => "Nem megfelelő metódus"
    ];
    echo json_encode($valasz, JSON_UNESCAPED_UNICODE);
    header("bad request", true, 400);
}

?>
