<?php

include "./sql_fuggvenyek.php";

$url = explode("/", $_SERVER["REQUEST_URI"]);
$url_vege = end($url);

function Delete() {
    $adatok = json_decode(file_get_contents("php://input"), true);

    // Érkezett adatok ellenőrzése
    if (!empty($adatok["id"]) && !empty($adatok["userid"]) && !empty($adatok["password"])) {
        $id = $adatok["id"];
        $userid = $adatok["userid"];
        $password = $adatok["password"];

        // Titkosított jelszó lekérdezése
        $sql_felhasznalo_jelszo_ellenorzes = "SELECT `Jelszo` FROM `felhasznalo` WHERE `FelhasznaloID` = {$userid};";
        $jelszo = AdatLekerdezes($sql_felhasznalo_jelszo_ellenorzes);

        // Ha van az ID-hez felhasználó, akkor jelszó összehasonlítása
        if (is_array($jelszo)) {
            if ($password == $jelszo[0]["Jelszo"]) {
                $sql_kurzus_torles = "DELETE FROM `kurzus` WHERE `KurzusID` = {$id};";
                $eredmeny = AdatModositas($sql_kurzus_torles);
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
    case "delete":
        Delete();
        break;
    default:
        break;
}

?>