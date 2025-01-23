<?php

function Login() {
    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["email", "password"])) {
        return;
    }

    global $data;
    $email = $data["email"];
    $password = $data["password"];

    // Ellenőrzés, hogy van-e felhasználó az adott e-mail címmel
    $sql_statement = "SELECT user_id, password FROM users WHERE email = ?;";
    $user_data = DataQuery($sql_statement, "s", [$email]);
    if (!is_array($user_data)) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "E-mail vagy jelszó nem megfelelő"
        ]);
        return;
    }

    // Jelszó ellenőrzése
    if (!password_verify($password, $user_data[0]["password"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "E-mail vagy jelszó nem megfelelő"
        ]);
        return;
    }

    // Session elindítása
    session_start();
    $_SESSION["user_id"] = $user_data[0]["user_id"];
    SendResponse([
        "sikeres" => true,
        "uzenet" => "Sikeres bejelentkezés"
    ]);
}

function Signup() {
    if (!CheckMethod("POST")) {
        return;
    }

    global $data;
    if (!PostDataCheck(["email", "lastname", "firstname", "password"])) {
        return;
    }

    $email = $data["email"];
    $lastname = $data["lastname"];
    $firstname = $data["firstname"];
    $password = $data["password"];
    
    // Ellenőrzés, hogy nincs-e már felhasználó regisztrálva azonos e-mail címmel
    $sql_statement = "SELECT email FROM users WHERE email = ?";
    $email_check = DataQuery($sql_statement, "s", [$email]);
    if (is_array($email_check)) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "E-mail cím már regisztrálva van"
        ]);
        return;
    }

    // Jelszó titkosítása
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Felhasználó feltöltése
    $sql_statement= "INSERT INTO users (user_id, email, firstname, lastname, password) 
    VALUES (NULL, ?, ?, ?, ?);";
    $result = ModifyData($sql_statement, "ssss", [$email, $firstname, $lastname, $hashed_password]);

    // Eredmény vizsgálata
    if ($result) { 
        $sql_statement = "SELECT user_id FROM users WHERE email = ?;";
        $user_id = DataQuery($sql_statement, "s", [$email]);
        session_start();
        $_SESSION["user_id"] = $user_id[0]["user_id"];
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Felhasználó regisztrálva és bejelentkezve"
        ], 201);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem sikerült regisztrálni a felhasználót"
        ]);
    }
}

function Logout() {
    session_start();
    if (!LoginCheck()) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs felhasználó bejelentkezve"
        ], 401);
        return;
    }
    session_unset();
    session_destroy();
    SendResponse([
        "sikeres" => true,
        "uzenet" => "Felhasználó kijelentkezve"
    ]);
}

function DeleteUser() {
    session_start();
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    global $data;
    if (!PostDataCheck(["password"])) {
        return;
    }

    $password = $data["password"];
    $id = $_SESSION["user_id"];

    // Van-e ilyen felhasználó
    $sql_statement = "SELECT password FROM users WHERE user_id = ?;";
    $hashed_password = DataQuery($sql_statement, "i", [$id]);
    if (!is_array($hashed_password)) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A felhasználó már törölve van"
        ]);
        return;
    }

    // Jelszó ellenőrzése
    if (!password_verify($password, $hashed_password[0]["password"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Helytelen jelszó"
        ]);
        return;
    }

    // Felhasználó törlése
    $sql_statement = "DELETE FROM users WHERE user_id = ?;";
    $result = ModifyData($sql_statement, "i", [$id]);
    
    // Eredmény vizsgálata
    if ($result) { 
        session_unset();
        session_destroy();
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Felhasználó törölve"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem sikerült törölni a felhasználót"
        ]);
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
        case "modify":
            //ModifyUserData();
            break;
        case "delete":
            DeleteUser();
            break;
        default:
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Hibás műveletmegadás"
            ], 400);
            break;
    }
}

?>