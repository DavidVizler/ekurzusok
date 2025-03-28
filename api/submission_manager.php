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
            ]);
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
    
    if (!PostDataCheck(["content_id"], "i", true, true, true)) {
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

    // Ellenőrzés, hogy van-e beadandó
    $sql_statement = "SELECT submission_id FROM submissions WHERE user_id = ? AND content_id = ?;";
    $submission_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    if (count($submission_data) == 0) {
        // Ha nincs, akkor létrehozás
        $sql_statement = "INSERT INTO submissions (submission_id, user_id, content_id, submitted, rating) VALUES (NULL,  ?, ?, NULL, NULL);";
        ModifyData($sql_statement, "ii", [$user_id, $content_id]);

        $sql_statement = "SELECT submission_id FROM submissions WHERE user_id = ? AND content_id = ?;";
        $submission_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);  
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