<?php

include "../api/functions.php";

if (!LoginCheck(false)) {
    echo "A felhasználó nincs bejelentkezve!";
    exit;
}

if (!CheckMethod("GET")) {
    echo "Helytelen metódus!";
    exit;
}

if (!array_key_exists("file_id", $_GET)) {
    echo "Hiányos adat: file_id";
    http_response_code(400);
    exit;
}

if (!array_key_exists("attached_to", $_GET)) {
    echo "Hiányos adat: attached_to!";
    http_response_code(400);
    exit;
}

if (!array_key_exists("id", $_GET)) {
    echo "Hiányos adat: id!";
    http_response_code(400);
    exit;
}

$file_id = $_GET["file_id"];
$attached_to = $_GET["attached_to"];
$id = $_GET["id"];
$user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;

// TODO: felhasználó tagja-e a kurzusnak vagy tulajdonosa-e a beadandónak vagy a feladatnak

if ($attached_to == "submission") {
    $sql_statement = "SELECT s.user_id, f.name FROM files f 
    INNER JOIN submissions s ON f.submission_id = s.submission_id 
    WHERE f.file_id = ? AND s.submission_id = ?;";
} else if ($attached_to == "content") {
    $sql_statement = "SELECT c.user_id, f.name FROM files f 
    INNER JOIN content c ON f.content_id = c.content_id
    WHERE f.file_id = ? AND c.content_id = ?;";
} else {
    echo "Helytelen érték! (attached_to : {$attached_to})";
    http_response_code(400);
    exit;
}

$file_data = DataQuery($sql_statement, "ii", [$file_id, $id]);

if (count($file_data) == 0) {
    echo "Nincs fájl ilyen ID-val!";
    http_response_code(400);
    exit;
}

$file_name = $file_data[0]["name"];
$file_path = "../files/" . $file_id;

// Fájl letöltése
header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename={$file_name}");
header("Expires: 0");
header("Cache-Control: must-revalidate");
header("Pragma: public");
header("Content-Length: " . filesize($file_path));

ob_clean();
flush();

readfile($file_path);

?>
