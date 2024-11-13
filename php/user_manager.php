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

        echo "Felhasználó feltöltése: " . $eredmeny;
    } else {
        echo "Már regisztráltak ezen az e-mail címen!";
    }
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
            echo "Sikeres bejelentkezés!";
        } else {
            echo "Helytelen e-mail cím vagy jelszó!";
        }
    } else {
        echo "Helytelen e-mail cím!";
    }
}

switch ($url_vege) {
    case "signup":
        Signup();
        break;
    case "login":
        Login();
        break;
    default:
        break;
}

?>
