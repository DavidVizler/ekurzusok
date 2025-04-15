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
    echo "Hiányos adat: file_id!";
    exit;
}

if (!array_key_exists("attached_to", $_GET)) {
    echo "Hiányos adat: attached_to!";
    exit;
}

if (!array_key_exists("id", $_GET)) {
    echo "Hiányos adat: id!";
    exit;
}

$file_id = $_GET["file_id"];
$attached_to = $_GET["attached_to"];
$id = $_GET["id"];
$user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;

if ($attached_to == "content") {
    // Benne van-e a felhasználó a kurzusban
    $sql_statement = "SELECT * FROM memberships m
    INNER JOIN courses c ON m.course_id = c.course_id
    INNER JOIN content t ON t.course_id = c.course_id
    WHERE t.content_id = ? AND m.user_id = ?;";
    $membership_data = DataQuery($sql_statement, "ii", [$id, $user_id]);
    if (count($membership_data) == 0) {
        echo "A felhasználó nem tagja a kurzusnak, ahol a fájl közzé van téve!";
        exit;
    }
} else if ($attached_to == "submission") {
    // A felhasználó tulajdonosa-e a beadandónak
    $sql_statement = "SELECT user_id FROM submissions WHERE submission_id = ?;";
    $submission_data = DataQuery($sql_statement, "i", [$id]);

    if (count($submission_data) == 0) {
        echo "Nincs beadandó ilyen ID-val";
        exit;
    }

    // Ha a felhasználó nem tulajdonosa a tartalomnak, akkor tanár-e a kurzusban
    if ($submission_data[0]["user_id"] != $user_id) {
        $sql_statement = "SELECT c.course_id FROM content c
        INNER JOIN submissions s ON c.content_id = s.content_id
        WHERE s.submission_id = ?;";
        $course_data = DataQuery($sql_statement, "i", [$id]);

        $course_id = $course_data[0]["course_id"];

        $sql_statement = "SELECT role FROM memberships
        WHERE course_id = ? AND user_id = ?";
        $membership_data = DataQuery($sql_statement, "ii", [$course_id, $user_id]);

        if (count($membership_data) == 0) {
            echo "A felhasználó nem tagja a kurzusnak";
            exit;
        }

        if ($membership_data[0]["role"] == 1) {
            echo "A felhasználó nem tanár a kurzusban!";
            exit;
        }
    }
} else {
    echo "Helytelen érték! (attached_to : {$attached_to})";
    exit;
}

if ($attached_to == "submission") {
    $sql_statement = "SELECT s.user_id, f.name FROM files f 
    INNER JOIN submissions s ON f.submission_id = s.submission_id 
    WHERE f.file_id = ? AND s.submission_id = ?;";
} else {
    $sql_statement = "SELECT c.user_id, f.name FROM files f 
    INNER JOIN content c ON f.content_id = c.content_id
    WHERE f.file_id = ? AND c.content_id = ?;";
}

$file_data = DataQuery($sql_statement, "ii", [$file_id, $id]);

if (count($file_data) == 0) {
    echo "Nincs fájl ilyen ID-val!";
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
