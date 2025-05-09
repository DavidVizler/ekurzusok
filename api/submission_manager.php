<?php

function SubmitSubmission() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["content_id"], "i")) {
        return;
    }

    global $data;
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;
    $content_id = $data["content_id"];

    // Ellenőrzés, hogy a felhasználó nem tanár-e a kurzusban
    $sql_statement = "SELECT m.role, c.course_id, c.deadline FROM memberships m
    INNER JOIN content c ON m.course_id = c.course_id
    WHERE c.content_id = ? AND m.user_id = ?;";
    $membership_data = DataQuery($sql_statement, "ii", [$content_id, $user_id]);

    if (count($membership_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    if ($membership_data[0]["role"] != 1) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tanuló a kurzusban"
        ], 403);
        return;
    }

    // Nem-e határidő után akarja a diák beadni a munkáját
    if (!is_null($membership_data[0]["deadline"])) {
        $deadline = $membership_data[0]["deadline"];
        $now = new DateTime('now', new DateTimeZone('Europe/Budapest'));
        if ($now->format('Y-m-d H:i:s') > $deadline) {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Határidő után nem lehet beadni munkát"
            ], 403);
            return;
        }
    }

    // Nincs-e archviálva a kurzus
    $course_id = $membership_data[0]["course_id"];
    $sql_statement = "SELECT archived FROM courses WHERE course_id = ?;";
    $archived = DataQuery($sql_statement, "i", [$course_id]);
    if ($archived[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    $sql_statement = "SELECT submission_id, submitted FROM submissions WHERE user_id = ? AND content_id = ?;";
    $submission_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    // Van-e már beadandó
    if (count($submission_data) == 0) {
        // Ha nincs, akkor üres leadása
        $sql_statement = "INSERT INTO submissions (submission_id, user_id, content_id, submitted, rating) VALUES (NULL, ?, ?, NOW(), NULL);";
        $results = ModifyData($sql_statement, "ii", [$user_id, $content_id]);
    } else {
        // Be van-e már adva
        if (is_null($submission_data[0]["submitted"])) {
            $sql_statement = "UPDATE submissions SET submitted = NOW() WHERE submission_id = ?;";
            $results = ModifyData($sql_statement, "i", [$submission_data[0]["submission_id"]]);
        } else {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Már le van adva a feladat"
            ], 400);
            return;
        }
    }

    if ($results) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Feladat sikeresen leadva"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Feladat leadása sikertelen"
        ], 400);
    }
}

function UnsubmitSubmission() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["content_id"], "i")) {
        return;
    }

    global $data;
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;
    $content_id = $data["content_id"];

    $sql_statement = "SELECT c.course_id, c.deadline FROM memberships m
    INNER JOIN content c ON m.course_id = c.course_id
    WHERE c.content_id = ? AND m.user_id = ?;";
    $membership_data = DataQuery($sql_statement, "ii", [$content_id, $user_id]);

    // Kurzus nincs-e archiválva
    $course_id = $membership_data[0]["course_id"];
    $sql_statement = "SELECT archived FROM courses WHERE course_id = ?;";
    $archived = DataQuery($sql_statement, "i", [$course_id]);
    if ($archived[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    // Nem járt-e le a határidő
    if (!is_null($membership_data[0]["deadline"])) {
        $deadline = $membership_data[0]["deadline"];
        $now = new DateTime('now', new DateTimeZone('Europe/Budapest'));
        if ($now->format('Y-m-d H:i:s') > $deadline) {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Határidő után nem lehet visszavonni a beadást"
            ], 403);
            return;
        }
    }

    $sql_statement = "SELECT submission_id, submitted FROM submissions WHERE user_id = ? AND content_id = ?;";
    $submission_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    // Van-e beadandó
    if (count($submission_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs beadandó"
        ], 403);
    }

    // Be van-e már adva
    if (is_null($submission_data[0]["submitted"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A feladat nincs leadva"
        ], 403);
        return;
    }

    $submission_id = $submission_data[0]["submission_id"];

    $sql_statement = "UPDATE submissions SET submitted = NULL WHERE submission_id = ?;";
    $results = ModifyData($sql_statement, "i", [$submission_id]);

    if ($results) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Leadás sikeresen visszavonva"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Leadás visszavonása sikertelen"
        ], 400);
    }
}

function AttachSubmissionFiles() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }
    
    if (!PostDataCheck(["content_id"], "s", true, true, true)) {
        return;
    }

    // Érkeztek-e fájlok
    if (!array_key_exists("files", $_FILES)) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem érkeztek fájlok"
        ], 400);
        return;
    }

    $content_id = $_POST["content_id"];
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;

    // Ellenőrzés, hogy a felhasználó nem tanár-e a kurzusban
    $sql_statement = "SELECT m.role, c.course_id FROM memberships m
    INNER JOIN content c ON m.course_id = c.course_id
    WHERE c.content_id = ? AND m.user_id = ?;";
    $membership_data = DataQuery($sql_statement, "ii", [$content_id, $user_id]);

    if (count($membership_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    if ($membership_data[0]["role"] != 1) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tanuló a kurzusban"
        ], 403);
        return;
    }

    $course_id = $membership_data[0]["course_id"];
    $sql_statement = "SELECT archived FROM courses WHERE course_id = ?;";
    $archived = DataQuery($sql_statement, "i", [$course_id]);
    if ($archived[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    // Ellenőrzés, hogy van-e beadandó
    $sql_statement = "SELECT submission_id, submitted FROM submissions WHERE user_id = ? AND content_id = ?;";
    $submission_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    if (count($submission_data) == 0) {
        // Ha nincs, akkor létrehozás
        $sql_statement = "INSERT INTO submissions (submission_id, user_id, content_id, submitted, rating) VALUES (NULL,  ?, ?, NULL, NULL);";
        ModifyData($sql_statement, "ii", [$user_id, $content_id]);

        $sql_statement = "SELECT submission_id, submitted FROM submissions WHERE user_id = ? AND content_id = ?;";
        $submission_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);  
    } 

    if (!is_null($submission_data[0]["submitted"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A feladat már le van adva"
        ], 403);
        return;
    }

    $submission_id = $submission_data[0]["submission_id"];
    
    $results = FileUpload($submission_id, "submission");

    if ($results) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Fájlok sikeresen feltöltve"
        ], 201);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Fájlok feltöltése sikertelene"
        ], 400);
    }
}

function RemoveFileFromSubmission() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["file_id", "content_id"], "ii")) {
        return;
    }

    global $data;
    $file_id = $data["file_id"];
    $content_id = $data["content_id"];
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;

    // Ellenőrzés, hogy van-e beadandó
    $sql_statement = "SELECT submission_id, submitted FROM submissions WHERE user_id = ? AND content_id = ?;";
    $submission_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    if (count($submission_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs beadandó vagy nem a felhasználóé"
        ], 403);
        return;
    } 

    if (!is_null($submission_data[0]["submitted"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A feladat már le van adva"
        ], 403);
        return;
    }

    $sql_statement = "SELECT c.archived FROM courses c
    INNER JOIN content t ON c.course_id = t.course_id
    WHERE t.content_id = ?;";
    $archived = DataQuery($sql_statement, "i", [$content_id]);
    if ($archived[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    $submission_id = $submission_data[0]["submission_id"];

    // A felhasználó-e a beadandó tulajdonosa
    $sql_statement = "SELECT s.user_id FROM submissions s
    INNER JOIN files f ON s.submission_id = f.submission_id
    WHERE s.submission_id = ? AND f.file_id = ?;";
    $file_data = DataQuery($sql_statement, "ii", [$submission_id, $file_id]);

    if (count($file_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs fájl vagy tartalom ilyen ID-val"
        ], 404);
        return;
    }

    if ($file_data[0]["user_id"] != $user_id) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tulajdonosa a tartalomnak"
        ], 403);
        return;
    }

    $sql_statement = "DELETE FROM files WHERE file_id = ?;";
    $result = ModifyData($sql_statement, "i", [$file_id]);

    unlink("../files/" . $file_id);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Sikeres törlés"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Sikertelen törlés"
        ], 400);
    }
}

function RateSubmission() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }
    
    if (!PostDataCheck(["submission_id", "points"], "in")) {
        return;
    }

    global $data;
    $user_id = decrypt($_COOKIE["user_id"], getenv("COOKIE_KEY"));;
    $submission_id = $data["submission_id"];
    $points = $data["points"];

    // Feladat adatainak lekérdezése
    $sql_statement = "SELECT t.max_points, c.course_id FROM content t
    INNER JOIN courses c ON t.course_id = c.course_id
    INNER JOIN submissions s ON t.content_id = s.content_id
    WHERE s.submission_id = ?;";
    $content_data = DataQuery($sql_statement, "i", [$submission_id]);

    if (count($content_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs beadandó ilyen ID-val"
        ], 404);
        return;
    }

    $course_id = $content_data[0]["course_id"];
    $sql_statement = "SELECT archived FROM courses WHERE course_id = ?;";
    $archived = DataQuery($sql_statement, "i", [$course_id]);
    if ($archived[0]["archived"]) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A kurzus archiválva van"
        ], 403);
        return;
    }

    if (is_null($content_data[0]["max_points"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs a feladatnak elérhető pontszám beállítva"
        ], 403);
        return;
    }

    if ($content_data[0]["max_points"] < $points) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Az értékelés nem lehet magasabb, mint a ponthatár"
        ], 400);
        return;
    }

    $course_id = $content_data[0]["course_id"];

    // Jogosultságkezelés
    $sql_statement = "SELECT role FROM memberships WHERE course_id = ? AND user_id = ?;";
    $membership_data = DataQuery($sql_statement, "ii", [$course_id, $user_id]);
    if (count($membership_data) == 0) {
        SendResponse([
            "uzenet" => "A felhasználó nem tagja a kurzusnak"
        ], 403);
        return;
    }

    if ($membership_data[0]["role"] == 1) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó nem tanár a kurzusban"
        ], 403);
        return;
    }

    $sql_statement = "UPDATE submissions SET rating = ? WHERE submission_id = ?;";
    $results = ModifyData($sql_statement, "ii", [$points, $submission_id]);

    if ($results) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Beadandó sikeresen értékelve"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Beadandó értékelése sikertelen"
        ], 400);
    }
}

function Manage($action) {
    switch ($action) {
        case "upload-files":
            AttachSubmissionFiles();
            break;
        case "submit":
            SubmitSubmission();
            break;
        case "unsubmit":
            UnsubmitSubmission();
            break;
        case "remove-file":
            RemoveFileFromSubmission();
            break;
        case "rate":
            RateSubmission();
            break;
        default:
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Hibás műveletmegadás: {$action}"
            ], 400);
            break;
    }
}

?>