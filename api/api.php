<?php

include "./functions.php";

// URL lekérdezése
$url = array_filter(explode("/", $_SERVER["REQUEST_URI"]));

// Endpoint kezelés
$field_endpoint = $url[count($url)-1];
$action_endpoint = $url[count($url)];

// Érkezett adatok eltárolása
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
}

// Műveletkezelés
if (in_array($field_endpoint,  ["user", "course", "member", "content", "assignment", "query"])) {
    include "./{$field_endpoint}_manager.php";
    Manage($action_endpoint);
} else {
    SendResponse([
        "sikeres" => "false",
        "uzenet" => "Hibás műveletmegadás"
    ], 400);
}

?>