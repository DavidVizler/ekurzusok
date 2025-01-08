<?php

function Login() {
    // Metódus ellenőrzése
    if (CheckMethod("POST")) {
        global $data;
        // Érkezett adatok ellenőrzése
        if (PostDataCheck(["email", "password"])) {
            global $response;
            $email = $data["email"];
            $password = $data["password"];

            // Ellenőrzés, hogy van-e felhasználó az adott e-mail címmel
            $sql_statement = "SELECT `FelhasznaloID`, `Jelszo` FROM `felhasznalo` WHERE `Email` = ?;";
            $user_data = DataQuery($sql_statement, "s", [$email]);
            if (!is_array($user_data)) {
                SetResponse([
                    "sikeres" => false,
                    "uzenet" => "E-mail vagy jelszó nem megfelelő"
                ]);
                return;
            }

            // Jelszó ellenőrzése
            if (!password_verify($password, $user_data[0]["Jelszo"])) {
                SetResponse([
                    "sikeres" => false,
                    "uzenet" => "E-mail vagy jelszó nem megfelelő"
                ]);
                return;
            }

            // Session elindítása
            session_start();
            $_SESSION["user_id"] = $user_data[0]["FelhasznaloID"];
            SetResponse([
                "sikeres" => true,
                "uzenet" => "Sikeres bejelentkezés"
            ]);
        }
    }
}

function Manage($action) {
    switch ($action) {
        case "signup":
            Signup();
            break;
        case "login":
            Login();
            break;
        case "logout":
            Logout();
            break;
        case "delete":
            DeleteUser();
            break;
        case "delete-as-admin":
            DeleteUserAsAdmin();
            break;
        default:
            SetResponse([
                "sikeres" => false,
                "uzenet" => "Hibás műveletmegadás"
            ], 400);
            break;
    }
}

?>