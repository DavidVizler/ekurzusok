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
    if (count($user_data) == 0) {
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

    if (!PostDataCheck(["email", "lastname", "firstname", "password"])) {
        return;
    }

    global $data;
    
    $email = $data["email"];
    $lastname = $data["lastname"];
    $firstname = $data["firstname"];
    $password = $data["password"];
    
    // Ellenőrzés, hogy nincs-e már felhasználó regisztrálva azonos e-mail címmel
    $sql_statement = "SELECT email FROM users WHERE email = ?";
    $email_check = DataQuery($sql_statement, "s", [$email]);
    if (count($email_check) > 0) {
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

function ModifyUserData() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["password"])) {
        return;
    }

    global $data;
    $user_id = $_SESSION["user_id"];
    $password = $data["password"];

    // Jelszó ellenőrzése
    $sql_statement = "SELECT password FROM users WHERE user_id = ?";
    $password_check = DataQuery($sql_statement, "i", [$user_id]);

    if (count($password_check) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A bejelentkezett felhasználói fiók már nem létezik"
        ], 410);
        return;
    }

    if (!password_verify($password, $password_check[0]["password"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Helytelen jelszó"
        ]);
        return;
    }

    // Érkezett adatok ellenőrzése
    $email = $data["email"] ?? NULL;
    $lastname = $data["lastname"] ?? NULL;
    $firstname = $data["firstname"] ?? NULL;
    $new_password = $data["new_password"] ?? NULL;

    // Lekérdezés összerakása
    $sql_statement = "UPDATE users SET ";
    $new_data = [];

    if (isset($email)) {
        $sql_statement .= "email = ?";
        array_push($new_data, $email);
    }

    if (isset($firstname)) {
        if (count($new_data) > 0) {
            $sql_statement .= ", ";
        }
        $sql_statement .= "firstname = ?";
        array_push($new_data, $firstname);
    }

    if (isset($lastname)) {
        if (count($new_data) > 0) {
            $sql_statement .= ", ";
        }
        $sql_statement .= "lastname = ?";
        array_push($new_data, $lastname);
    }

    if (isset($new_password)) {
        if ($password == $new_password && count($new_data) == 0) {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Az új jelszó megegyezik a régi jelszóval"
            ]);
            return;
        }

        if (count($new_data) > 0) {
            $sql_statement .= ", ";
        }
        $sql_statement .= "password = ?";
        array_push($new_data, password_hash($new_password, PASSWORD_DEFAULT));
    }

    // Ha semmi sem változik, akkor nincs adatbázis művelet
    if (count($new_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nem érkezett változtatandó adat"
        ]);
        return;
    }

    // Where záradék hozzáadása
    $sql_statement .= " WHERE user_id = ?;";
    $new_data_types = str_repeat("s", count($new_data)) . "i";
    array_push($new_data, $user_id);

    $result = ModifyData($sql_statement, $new_data_types, $new_data);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Sikeres adatmódosítás"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Sikertelen adatmódosítás"
        ]);
    }
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
    if (count($hashed_password) == 0) {
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
        case "modify-data":
            ModifyUserData();
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