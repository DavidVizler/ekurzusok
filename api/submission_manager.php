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

    $sql_statement = "SELECT submission_id FROM submissions WHERE user_id = ? AND content_id = ?;";
    $submission_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    // TODO
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
    
    // Fájlok feltöltése
    var_dump($_FILES);

    for ($i = 0; $i < count($_FILES["files"]["name"]); $i++) {
        $file_name = $_FILES["files"]["name"][$i];
        $file_size = $_FILES["files"]["size"][$i] / 1000; // Byte -> KB
    
        $sql_statement = "SELECT MAX(file_id) AS max_id FROM files;";
        $file_id = DataQuery($sql_statement)[0]["max_id"] + 1;
    
        var_dump(move_uploaded_file($_FILES["files"]["tmp_name"][$i], "../files/" . $file_id));
    
        $sql_statement = "INSERT INTO files (file_id, content_id, submission_id, name, size) VALUES (?, NULL, ?, ?, ?);";
        ModifyData($sql_statement, "iisi", [$file_id, $submission_id, $file_name, $file_size]);
    }
}

function Manage($action) {
    switch ($action) {
        case "upload":
            AttachSubmissionFiles();
            break;
        case "submit":
            SubmitSubmission();
        default:
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Hibás műveletmegadás: {$action}"
            ], 400);
            break;
    }
}

?>