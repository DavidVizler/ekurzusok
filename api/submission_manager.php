<?php

function CreateSubmission() {
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

    // Ellenőrzés, hogy a felhasználó láthatja-e a feladatot
    $sql_statement = "SELECT t.user_id FROM content t
    INNER JOIN courses c ON t.course_id = c.course_id
    INNER JOIN memberships m ON c.course_id = m.course_id
    WHERE m.user_id = ? AND content_id = ?;";
    $content_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    if (count($content_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs tartalom ilyen azonosítóval"
        ], 404);
        return;
    }

    if ($content_data[0]["user_id"] == $user_id) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A feladat kiadója nem adhat be megoldást"
        ], 403);
        return;
    }

    $sql_statement = "INSERT INTO submissions (submission_id, user_id, content_id, submitted, rating) VALUES (NULL, ?, ?, NULL, NULL);";
    $result = ModifyData($sql_statement, "ii", [$user_id, $content_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Beadandó sikeres létrehozva"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Beadandó létrehozás sikertelen"
        ]);
    }
}

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

    // Ellenőrzés, hogy van-e leadandó
    $sql_statement = "SELECT submission_id FROM submissions WHERE user_id = ? AND content_id = ?;";
    $submission_data = DataQuery($sql_statement, "ii", [$user_id, $content_id]);

    // Létrehozás, ha nincs
    if (count($submission_data) == 0) {
        $sql_statement = "INSERT INTO submissions (submission_id, user_id, content_id, submitted, rating) VALUES (NULL, ?, ?, UTC_TIMESTAMP, NULL);";
        $result = ModifyData($sql_statement, "ii", [$user_id, $content_id]);

        if ($result) {
            SendResponse([
                "sikeres" => true,
                "uzenet" => "Feladat megjelölve készként"
            ]);
        } else {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Beadandó létrehozás sikertelen"
            ]);
        }
    } else {
        $submission_id = $submission_data[0]["submission_id"];
        $sql_statement = "UPDATE submissions SET submitted = UTC_TIMESTAMP() WHERE submission_id = ?;";
        $result = ModifyData($sql_statement, "i", [$submission_id]);

        if ($result) {
            SendResponse([
                "sikeres" => true,
                "uzenet" => "Megoldás sikeresen leadva"
            ]);
        } else {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Megoldás leadása sikertelen"
            ]);
        }
    }
}

function AttachSubmissionFile($submission_id, $file_name, $file_size) {
    if (!LoginCheck(false)) {
        return;
    }

    $user_id = $_SESSION["user_id"];

    // Ellenőrzés, hogy a felghasználóé-e a beadandó
    $sql_statement = "SELECT user_id FROM submissions WHERE submission_id = ?;";
    $submission_data = DataQuery($sql_statement, "i", [$submission_id]);

    if (count($submission_data) == 0) {
        // Nincs ilyen beadandó
    }

    if ($submission_data[0]["user_id"] != $user_id) {
        // Nem a felhasználóé a beadandó
    }

    $sql_statement = "INSERT INTO files (file_id, content_id, submission_id, name, size) VALUES (NULL, NULL, ?, ?, ?);";
    $result = ModifyData($sql_statement, "isi", [$submission_id, $file_name, $file_size]);

    // Fejlesztés alatt
}

function Manage($action) {
    switch ($action) {
        case "create":
            CreateContent();
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