<?php

include "./sql_fuggvenyek.php";

$url = explode("/", $_SERVER["REQUEST_URI"]);
$url_vege = end($url);

function Signup() {
    $adatok = json_decode(file_get_contents("php://input"), true);
    $email = $adatok["email"];
    $lastname = $adatok["lastname"];
    $firstname = $adatok["firstname"];
    $password = $adatok["password"];

    // Ellenőrzés, hogy nincs-e már felhasználó regisztrálva azonos e-mail címmel
    $sql_regisztracio_email_lekerdezes = "SELECT `Email` FROM `felhasznalo` WHERE `Email` = '{$email}'";
    $email_ellenorzes = AdatLekerdezes($sql_regisztracio_email_lekerdezes);
    if (!is_array($email_ellenorzes)) {
        // Jelszó titkosítása
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql_felhasznalo_feltoltes = "INSERT INTO `felhasznalo` (`FelhasznaloID`, `Email`, `VezetekNev`, `KeresztNev`, `Jelszo`) 
        VALUES (NULL, '{$email}', '{$lastname}', '{$firstname}', '{$hashed_password}');";

        $eredmeny = AdatModositas($sql_felhasznalo_feltoltes);

        $valasz = ["regisztracio" => $eredmeny];
    } else {
        $valasz = ["regisztracio" => "email mar regisztralt"];
    }

    echo json_encode($valasz, JSON_UNESCAPED_UNICODE);
}

function Login() {
    $adatok = json_decode(file_get_contents("php://input"), true);
    $email = $adatok["email"];
    $password = $adatok["password"];

    $sql_jelszo_lekerdezes = "SELECT `Jelszo` FROM `felhasznalo` WHERE `Email` = '{$email}';";

    $hashed_password = AdatLekerdezes($sql_jelszo_lekerdezes);

    // Ellenőrzés, hogy van-e felhasználó az adott e-mail címmel
    if (is_array($hashed_password)) {
        // Jelszó ellenőrzése
        if (password_verify($password, $hashed_password[0]["Jelszo"])) {
            $valasz = ["bejelentkezes" => "sikeres"];
        } else {
            $valasz = ["bejelentkezes" => "sikertelen"];
        }
    } else {
        $valasz = ["bejelentkezes" => "sikertelen"];
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
        $jelszo = AdatLekerdezes($sql_felhasznalo_jelszo_ellenorzes);

        // Ha van az ID-hez felhasználó, akkor jelszó összehasonlítása
        if (is_array($jelszo)) {
            if ($password == $jelszo[0]["Jelszo"]) {
                $sql_felhasznalo_torles = "DELETE FROM `felhasznalo` WHERE `FelhasznaloID` = {$id};";
                $eredmeny = AdatModositas($sql_felhasznalo_torles);
                $valasz = ["torles" => $eredmeny];
            } else {
                $valasz = ["torles" => "sikertelen"];
            }
        } else {
            $valasz = ["torles" => "sikertelen"];
        }
    } else {
        $valasz = ["torles" => "hianyos adatok"];
        header("bad request", true, 400);
    }

    echo json_encode($valasz, JSON_UNESCAPED_UNICODE);
}

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
        break;
}

/*
TODO
Érkezett adatok ellenőrzése
Metódus ellenőrzése
*/

?>
