<?php

function Login() {
    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["email", "password"], "ss")) {
        return;
    }

    global $data;
    $email = $data["email"];
    $password = $data["password"];
    //$keep_login = $data["keep_login"];

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

    //$cookie_time = $keep_login ? (10 * 365 * 24 * 60 * 60) : 0;
    $keep_login = 10 * 365 * 24 * 60 * 60; // 10 év

    // Süti beállítása
    setcookie("user_id", $user_data[0]["user_id"], time() + $keep_login, "/", "", true, true);
    SendResponse([
        "sikeres" => true,
        "uzenet" => "Sikeres bejelentkezés"
    ]);
}

function Signup() {
    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["email", "lastname", "firstname", "password"], "ssss")) {
        return;
    }

    global $data;
    
    $email = $data["email"];
    $lastname = $data["lastname"];
    $firstname = $data["firstname"];
    $password = $data["password"];

    // Email cím formátumának validálása
    if (preg_match('/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $email) != 1) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Az email cím formátuma nem megfelelő"
        ]);
        return;
    }
    
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
    if (!LoginCheck()) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Nincs felhasználó bejelentkezve"
        ], 401);
        return;
    }

    setcookie("user_id", "", time() - 3600, "/");
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

    if (!PostDataCheck(["email", "firstname", "lastname", "password"], "ssss")) {
        return;
    }

    global $data;
    $user_id = $_SESSION["user_id"];
    $email = $data["email"];
    $lastname = $data["lastname"];
    $firstname = $data["firstname"] ;
    $password = $data["password"];

    // Jelszó ellenőrzése
    $sql_statement = "SELECT * FROM users WHERE user_id = ?";
    $user_data = DataQuery($sql_statement, "i", [$user_id]);

    if (count($user_data) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A bejelentkezett felhasználói fiók már nem létezik"
        ], 410);
        return;
    }

    if (!password_verify($password, $user_data[0]["password"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Helytelen jelszó"
        ]);
        return;
    }

    $sql_statement = "UPDATE users SET ";
    $new_data = [];
    
    if ($user_data[0]["email"] != $email) {

        $email_check_sql_statement = "SELECT email FROM users WHERE email = ?";
        $used_emails = DataQuery($email_check_sql_statement, "s", [$email]);
        if (count($used_emails) > 0) {
            SendResponse([
                "sikeres" => false,
                "uzenet" => "Az e-mail cím már foglalt"
            ]);
            return;
        }

        $sql_statement .= "email = ?";
        array_push($new_data, $email);
    }

    if ($user_data[0]["firstname"] != $firstname) {
        if (count($new_data) > 0) $sql_statement .= ", ";
        $sql_statement .= "firstname = ?";
        array_push($new_data, $firstname);
    }

    if ($user_data[0]["lastname"] != $lastname) {
        if (count($new_data) > 0) $sql_statement .= ", ";
        $sql_statement .= "lastname = ?";
        array_push($new_data, $lastname);
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

function ChangeUserPassword() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    if (!PostDataCheck(["old_password", "new_password"], "ss")) {
        return;
    }

    global $data;
    $old_passwd = $data["old_password"];
    $new_passwd = $data["new_password"];
    $user_id = $_SESSION["user_id"];

    // Régi jelszó ellenőrzése
    $sql_statement = "SELECT password FROM users WHERE user_id = ?";
    $passwd_check = DataQuery($sql_statement, "i", [$user_id]);

    if (count($passwd_check) == 0) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "A bejelentkezett felhasználói fiók már nem létezik"
        ], 410);
        return;
    }

    if (!password_verify($old_passwd, $passwd_check[0]["password"])) {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Helytelen jelszó"
        ]);
        return;
    }

    if ($old_passwd == $new_passwd) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Az új jelszó megegyezik a régi jelszóval"
        ]);
        return;
    }

    $passwd = password_hash($new_passwd, PASSWORD_DEFAULT);

    $sql_statement = "UPDATE users SET password = ? WHERE user_id = ?";
    $result = ModifyData($sql_statement, "si", [$passwd, $user_id]);

    if ($result) {
        SendResponse([
            "sikeres" => true,
            "uzenet" => "Sikeres jelszómódosítás"
        ]);
    } else {
        SendResponse([
            "sikeres" => false,
            "uzenet" => "Sikertelen jelszómódosítás"
        ]);
    }
}

function DeleteUser() {
    if (!LoginCheck()) {
        return;
    }

    if (!CheckMethod("POST")) {
        return;
    }

    global $data;
    if (!PostDataCheck(["password"], "s")) {
        return;
    }

    $password = $data["password"];
    $user_id = $_SESSION["user_id"];

    // Van-e ilyen felhasználó
    $sql_statement = "SELECT password FROM users WHERE user_id = ?;";
    $hashed_password = DataQuery($sql_statement, "i", [$user_id]);
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

    // Felhasználó kurzusainak törlése
    $sql_statement = "DELETE courses, memberships FROM courses
    INNER JOIN memberships ON courses.course_id = memberships.course_id
    WHERE memberships.user_id = ? AND memberships.role = 3;";
    ModifyData($sql_statement, "i", [$user_id]);

    // Felhasználó törlése
    $sql_statement = "DELETE FROM users WHERE user_id = ?";
    $result = ModifyData($sql_statement, "i", [$user_id]);

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
        case "change-password":
            ChangeUserPassword();
            break;
        case "delete":
            DeleteUser();
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