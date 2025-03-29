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
    $user_id = $_SESSION["user_id"];
    $content_id = $data["content_id"];

    // Ellenőrzés, hogy a felhasználó nem tanár-e a kurzusban
    $sql_statement = "SELECT m.role FROM memberships m
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

    $sql_statement = "SELECT submission_id, submitted FROM submissions WHERE user_id = ? AND content_id = ?;";
    $submission_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    // Van-e már beadandó
    if (count($submission_data) == 0) {
        // Ha nincs, akkor üres leadása
        $sql_statement = "INSERT INTO submissions (submission_id, user_id, content_id, submitted, rating) VALUES (NULL, ?, ?, UTC_TIMESTAMP(), NULL);";
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
        ]);
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
    $user_id = $_SESSION["user_id"];

    // Ellenőrzés, hogy a felhasználó nem tanár-e a kurzusban
    $sql_statement = "SELECT m.role FROM memberships m
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

    // Ellenőrzés, hogy van-e beadandó
    $sql_statement = "SELECT submission_id, submitted FROM submissions WHERE user_id = ? AND content_id = ?;";
    $submission_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    if (count($submission_data) == 0) {
        // Ha nincs, akkor létrehozás
        $sql_statement = "INSERT INTO submissions (submission_id, user_id, content_id, submitted, rating) VALUES (NULL,  ?, ?, NULL, NULL);";
        ModifyData($sql_statement, "ii", [$user_id, $content_id]);

        $sql_statement = "SELECT submission_id FROM submissions WHERE user_id = ? AND content_id = ?;";
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
    $user_id = $_SESSION["user_id"];

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
        ]);
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
    $user_id = $_SESSION["user_id"];
    $submission_id = $data["submission_id"];
    $points = $data["points"];

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

    if ($content_data[0]["max_points"] < $points) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Az értékelés nem lehet magasabb, mint a ponthatár"
        ], 400);
        return;
    }

    $course_id = $content_data[0]["course_id"];

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
        ]);
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