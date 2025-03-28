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
    
    if (!isset($_POST["content_id"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Hiányos adatok: content_id"
        ], 400);
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

    // Nincsenek-e túl nagy fájlok
    // Max fájl méret --> php.ini --> upload_max_filesize 
    for ($i = 0; $i < count($_FILES["files"]["name"]); $i++) {
        if ($_FILES["files"]["error"][$i]) {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "A(z) '{$_FILES["files"]["name"][$i]}' túl nagy méretű"
            ], 413);
            return;
        }
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

function Manage($action) {
    switch ($action) {
        case "upload-files":
            AttachSubmissionFiles();
            break;
        case "submit":
            SubmitSubmission();
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