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
    }

    if ($content_data[0]["user_id"] == $user_id) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A feladat kiadója nem adhat be megoldást"
        ], 403);
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

function Manage($action) {
    switch ($action) {
        case "create":
            CreateContent();
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