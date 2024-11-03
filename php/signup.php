<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/eKurzusok.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/signup.css">
    <title>Regisztráció</title>
</head>
<body>
    <div class="signup_div">
        <div class="form_div">
            <form method="post">
                <h1>Regisztráció</h1>
                <label for="lastname">Vezetéknév</label><br>
                <input type="text" id="lastname" placeholder="Vezetéknév"><br>

                <label for="firstname">Keresztnév</label><br>
                <input type="text" id="firstname" placeholder="Keresztnév"><br>

                <label for="email">Email cím</label><br>
                <input type="email" id="email" placeholder="pelda@gmail.com"><br>

                <label for="password">Jelszó</label><br>
                <input type="password" id="password" placeholder="Jelszó"><br>

                <label for="confirm-password">Jelszó megerősítése</label><br>
                <input type="password" id="confirm-password" placeholder="Jelszó megerősítése"><br>

                <input type="submit" name="signup_submit" id="signup_button" value="Regisztráció">
            </form>
        </div>
    </div>
</body>
</html>