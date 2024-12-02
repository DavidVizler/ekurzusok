<?php

include "./sql_functions.php";

// Metódus ellenőrzése
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function Signup() {
        $adatok = json_decode(file_get_contents("php://input"), true);
        if (!empty($adatok["email"]) && !empty($adatok["lastname"]) && !empty($adatok["firstname"]) && !empty($adatok["password"])) {

            $email = $adatok["email"];
            $lastname = $adatok["lastname"];
            $firstname = $adatok["firstname"];
            $password = $adatok["password"];
            
            // Ellenőrzés, hogy nincs-e már felhasználó regisztrálva azonos e-mail címmel
            $sql_regisztracio_email_lekerdezes = "SELECT `Email` FROM `felhasznalo` WHERE `Email` = '{$email}'";
            $email_ellenorzes = DataQuery($sql_regisztracio_email_lekerdezes);
            if (!is_array($email_ellenorzes)) {
                // Jelszó titkosítása
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql_felhasznalo_feltoltes = "INSERT INTO `felhasznalo` (`FelhasznaloID`, `Email`, `VezetekNev`, `KeresztNev`, `Jelszo`) 
                VALUES (NULL, '{$email}', '{$lastname}', '{$firstname}', '{$hashed_password}');";

                $eredmeny = ModifyData($sql_felhasznalo_feltoltes);

                if ($eredmeny == "Sikeres művelet!") { 
                    $valasz = [
                        "sikeres" => true,
                        "uzenet" => "Felhasználó regisztrálva"
                    ];
                    header("created", true, 201);
                } else if ($eredmeny == "Sikertelen művelet!") { // Nincs módosított sor az adatbázisban ($db->affected_rows = 0)
                    $valasz = [
                        "sikeres" => false,
                        "uzenet" => "Nem sikerült feltölteni a felhasználót"
                            ];
                    header("internal server error", true, 500);
                } else { // SQL hiba (uzenet = $db->error vagy $db->connect_error)
                    $valasz = [
                        "sikeres" => false,
                        "uzenet" => $eredmeny
                    ];
                    header("internal server error", true, 500);
                }  
            } else {
                $valasz = [
                    "sikeres" => false,
                    "uzenet" => "E-mail cím már regisztrálva van"
                ];
            }
        } else {
            $valasz = [
                "sikeres" => false,
                "uzenet" => "Hiányos adatok!"
            ];
            header("bad request", true, 400);
        }

        echo json_encode($valasz, JSON_UNESCAPED_UNICODE);
    }

    function Login() {
        $adatok = json_decode(file_get_contents("php://input"), true);
        if (!empty($adatok["email"]) && !empty($adatok["password"])) {
            $email = $adatok["email"];
            $password = $adatok["password"];

            $sql_user_data_query = "SELECT `FelhasznaloID`, `Jelszo` FROM `felhasznalo` WHERE `Email` = ?;";
            $user_data = DataQuery($sql_user_data_query, "s", [$email]);

            // Ellenőrzés, hogy van-e felhasználó az adott e-mail címmel
            if (is_array($user_data)) {
                // Jelszó ellenőrzése
                if (password_verify($password, $user_data[0]["Jelszo"])) {
                    // Session elindítása
                    session_start();
                    $_SESSION["user_id"] = $user_data[0]["FelhasznaloID"];

                    $valasz = [
                        "sikeres" => true,
                        "uzenet" => "Sikeres bejelentkezés"
                    ];          
                } else {
                    $valasz = [
                        "sikeres" => false,
                        "uzenet" => "Email vagy jelszó nem megfelelő"
                    ];
                }
            } else {
                $valasz = [
                    "sikeres" => false,
                    "uzenet" => "Email vagy jelszó nem megfelelő"
                ];
            }
        } else {
            $valasz = [
                "sikeres" => false,
                "uzenet" => "Hiányos adatok!"
            ];
            header("bad request", true, 400);
        }
        
        echo json_encode($valasz, JSON_UNESCAPED_UNICODE);
    }

    function Delete() {
        $adatok = json_decode(file_get_contents("php://input"), true);

        // Érkezett adatok ellenőrzése
        if (!empty($adatok["id"]) && !empty($adatok["password"])) {
            $id = $adatok["id"];
            $password = $adatok["password"];

            // Titkosított jelszó lekérdezése
            $sql_felhasznalo_jelszo_ellenorzes = "SELECT `Jelszo` FROM `felhasznalo` WHERE `FelhasznaloID` = {$id};";
            $jelszo = DataQuery($sql_felhasznalo_jelszo_ellenorzes);

            // Ha van az ID-hez felhasználó, akkor jelszó összehasonlítása
            if (is_array($jelszo)) {
                if ($password == $jelszo[0]["Jelszo"]) {
                    $sql_felhasznalo_torles = "DELETE FROM `felhasznalo` WHERE `FelhasznaloID` = {$id};";
                    $eredmeny = ModifyData($sql_felhasznalo_torles);
                    
                    if ($eredmeny == "Sikeres művelet!") { 
                        $valasz = [
                            "sikeres" => true,
                            "uzenet" => "Felhasználó törölve"
                        ];
                    } else if ($eredmeny == "Sikertelen művelet!") { // Nincs módosított sor az adatbázisban ($db->affected_rows = 0)
                        $valasz = [
                            "sikeres" => false,
                            "uzenet" => "Nem sikerült törölni a felhasználót"
                                ];
                        header("internal server error", true, 500);
                    } else { // SQL hiba (uzenet = $db->error vagy $db->connect_error)
                        $valasz = [
                            "sikeres" => false,
                            "uzenet" => $eredmeny
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
