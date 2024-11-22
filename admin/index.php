<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKurzusok Rendszer Adminisztráció</title>
    <link rel="shortcut icon" href="../img/eKurzusok.png" type="image/x-icon">
    <link rel="stylesheet" href="./admin.css" type="text/css">
    <script src="./admin.js"></script>
</head>
<body>
    <ul id="navbar">
        <li id="navitem-logo"><span id="nav-logo">eKurzusok Admin Felület</span></li>
        <li id="navitem-logo-side"></li>
        <li class="navitem active" id="nav-home"><a onclick="listStatistics()" href="./">Főoldal</a></li>
        <li class="navitem" id="nav-users"><a onclick="listUsers()" href="users">Felhasználók</a></li>
        <li class="navitem" id="nav-courses"><a onclick="listCourses()" href="courses">Kurzusok</a></li>
    </ul>

    <div id="content">
        <?php
            include "../php/sql_fuggvenyek.php";

            function FetchUsers() {
                $sql_users_query = "SELECT * FROM `felhasznalo`";
                $users = AdatLekerdezes($sql_users_query);

                if (is_array($users)) {   
                    echo "<table><thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Vezetéknév</th>
                            <th>Keresztnév</th>
                            <th>Titkosított jelszó</th>
                            <th>Kurzusok</th>
                            <th>Eltávolítás</th>
                        </tr>
                    </thead>
                    <tbody>";

                    foreach ($users as $user) {
                        $sql_user_course_count_query = "SELECT COUNT(`ID`) AS count FROM `kurzustag` WHERE `FelhasznaloID` = {$user["FelhasznaloID"]};";
                        $user_course_count = AdatLekerdezes($sql_user_course_count_query)[0]["count"];

                        echo "<tr>
                            <td>{$user["FelhasznaloID"]}</td>
                            <td>{$user["Email"]}</td>
                            <td>{$user["VezetekNev"]}</td>
                            <td>{$user["KeresztNev"]}</td>
                            <td><span class='blurred'>{$user["Jelszo"]}</span></td>
                            <td>{$user_course_count} <a href='usercourses?id={$user["FelhasznaloID"]}'>[Több infó]</a></td>
                            <td class='torles'>
                                <form method='POST' action='javascript:;' onsubmit=\"deleteUser({$user["FelhasznaloID"]}, '{$user["VezetekNev"]}', '{$user["KeresztNev"]}', '{$user["Email"]}', '{$user["Jelszo"]}')\">
                                    <input type='submit' value='Eltávolítás' name='delete_button'>
                                </form>
                            </td>
                        </tr>";
                    }

                    echo "</tbody></table>";
                } else {
                    echo "<div style='margin: 10px;'>Nincsenek regisztrált felhasználók az adatbázisban!</div>";
                }
            }

            function FetchUserCourses() {
                if (!empty($_GET["id"]) && is_numeric($_GET["id"])) {
                    $id = $_GET["id"];

                    $sql_user_info_query = "SELECT `VezetekNev`, `KeresztNev`, `Email` FROM `felhasznalo` WHERE `FelhasznaloID` = {$id}";
                    $user_info = AdatLekerdezes($sql_user_info_query)[0];

                    echo "<div style='margin: 10px'>{$user_info["VezetekNev"]} {$user_info["KeresztNev"]} ({$user_info["Email"]})
                    felhasználó az alábbi kurzusoknak tagja:</div>";
                    
                    $sql_user_courses_query = "SELECT `kurzus`.`KurzusID`, `kurzus`.`FelhasznaloID`, 
                    `kurzus`.`KurzusNev`, `kurzus`.`Kod`, `kurzus`.`Leiras`, `kurzus`.`Archivalt` 
                    FROM `kurzus` INNER JOIN `kurzustag` ON `kurzus`.`KurzusID` = `kurzustag`.`KurzusID`
                    WHERE `kurzustag`.`FelhasznaloID` = {$id};";
                    $user_courses = AdatLekerdezes($sql_user_courses_query);

                    if (is_array($user_courses)) {   
                        echo "<table><thead>
                            <tr>
                                <th>ID</th>
                                <th>Név</th>
                                <th>Leirás</th>
                                <th>Tanár</th>
                                <th>Tagok száma</th>
                                <th>Kód</th>
                                <th>Tulajdonos</th>
                            </tr>
                        </thead>
                        <tbody>";

                        foreach ($user_courses as $course) {
                            $sql_user_course_owner_query = "SELECT `VezetekNev`, `KeresztNev`, `Email` FROM `felhasznalo`
                            WHERE `FelhasznaloID` = {$course["FelhasznaloID"]};";
                            $owner = AdatLekerdezes($sql_user_course_owner_query)[0];

                            $sql_user_course_teacher_query = "SELECT `Tanar` FROM `kurzustag`
                            WHERE `FelhasznaloID` = {$id} AND `KurzusID` = {$course["KurzusID"]}";
                            $user_course_teacher = AdatLekerdezes($sql_user_course_teacher_query)[0]["Tanar"];
                            if ($user_course_teacher == 1) {
                                $teacher = "Igen";
                            } else {
                                $teacher = "Nem";
                            }

                            if ($user_info["Email"] == $owner["Email"]) {
                                $owner_class = " class='owner'";
                            } else {
                                $owner_class = "";
                            }

                            $sql_course_member_count_query = "SELECT COUNT(`ID`) AS member_count FROM `kurzustag` WHERE `KurzusID` = {$course['KurzusID']};";
                            $course_member_count = AdatLekerdezes($sql_course_member_count_query)[0]["member_count"];

                            $sql_course_teachers_count_query = "SELECT COUNT(`ID`) AS teachers_count FROM `kurzustag` WHERE `KurzusID` = {$course['KurzusID']} AND `Tanar` = '1';";
                            $course_teachers_count = AdatLekerdezes($sql_course_teachers_count_query)[0]["teachers_count"];

                            echo "<tr{$owner_class}>
                                <td>{$course["KurzusID"]}</td>
                                <td>{$course["KurzusNev"]}</td>
                                <td>{$course["Leiras"]}</td>
                                <td>{$teacher}</td>
                                <td>{$course_member_count} ({$course_teachers_count} tanár)</td>     
                                <td>{$course["Kod"]}</td>   
                                <td><a href='usercourses?id={$course["FelhasznaloID"]}'>{$owner["VezetekNev"]} {$owner["KeresztNev"]} ({$owner["Email"]})<a></td>
                            </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<div style='margin: 10px;'>A felhasználó nem tagja egy kurzushoz sem!</div>";
                    }
                } else {
                    header("Location: ./users");
                }
            }

            function FetchCourses() {
                $sql_courses_query = "SELECT `kurzus`.`KurzusID`, `kurzus`.`KurzusNev`, `kurzus`.`Kod`, `kurzus`.`Leiras`, `kurzus`.`Design`,  `kurzus`.`Archivalt`, 
                `felhasznalo`.`FelhasznaloID`, `felhasznalo`.`Email`, `felhasznalo`.`VezetekNev`, `felhasznalo`.`KeresztNev`, `felhasznalo`.`Jelszo`
                FROM `kurzus` INNER JOIN `felhasznalo` ON `kurzus`.`FelhasznaloID` = `felhasznalo`.`FelhasznaloID`";
                $courses = AdatLekerdezes($sql_courses_query);

                if (is_array($courses)) {   
                    echo "<table><thead>
                        <tr>
                            <th>ID</th>
                            <th>Név</th>
                            <th>Leirás</th>
                            <th>Tagok száma</th>
                            <th>Kód</th>
                            <th>Design</th>
                            <th>Tulajdonos</th>
                            <th>Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>";

                    foreach ($courses as $course) {
                        $sql_course_member_count_query = "SELECT COUNT(`ID`) AS member_count FROM `kurzustag` WHERE `KurzusID` = {$course['KurzusID']};";
                        $course_member_count = AdatLekerdezes($sql_course_member_count_query)[0]["member_count"];

                        $sql_course_teachers_count_query = "SELECT COUNT(`ID`) AS teachers_count FROM `kurzustag` WHERE `KurzusID` = {$course['KurzusID']} AND `Tanar` = '1';";
                        $course_teachers_count = AdatLekerdezes($sql_course_teachers_count_query)[0]["teachers_count"];

                        echo "<tr>
                            <td>{$course["KurzusID"]}</td>
                            <td>{$course["KurzusNev"]}</td>
                            <td>{$course["Leiras"]}</td>
                            <td>{$course_member_count} ({$course_teachers_count} tanár) <a href='coursemembers?id={$course["KurzusID"]}'>[Több infó]</a></td>    
                            <td>{$course["Kod"]}</td>
                            <td>{$course["Design"]}</td>     
                            <td><a href='usercourses?id={$course["FelhasznaloID"]}'>{$course["VezetekNev"]} {$course["KeresztNev"]} ({$course["Email"]})</a></td>
                            <td class='torles'>
                                <form method='POST' action='javascript:;' onsubmit=\"deleteCourse('{$course["KurzusID"]}', '{$course["KurzusNev"]}', '{$course["FelhasznaloID"]}', '{$course["VezetekNev"]}', '{$course["KeresztNev"]}', '{$course["Jelszo"]}')\">
                                    <input type='submit' value='Eltávolítás' name='delete_button'>
                                </form>
                            </td>
                        </tr>";
                    }

                    echo "</tbody></table>";
                } else {
                    echo "<div style='margin: 10px;'>Nincsenek kurzusok az adatbázisban!</div>";
                }
            }

            function FetchCourseMembers() {
                if (!empty($_GET["id"]) && is_numeric($_GET["id"])) {
                    $id = $_GET["id"];
                    
                    $sql_course_info_query = "SELECT `kurzus`.`KurzusNev`, `felhasznalo`.`VezetekNev`, `felhasznalo`.`KeresztNev`, `felhasznalo`.`Email`
                    FROM `kurzus` INNER JOIN `felhasznalo` ON `kurzus`.`FelhasznaloID` = `felhasznalo`.`FelhasznaloID`
                    WHERE `kurzus`.`KurzusID` = {$id};";
                    $course_info = AdatLekerdezes($sql_course_info_query)[0];

                    echo "<div style='margin: 10px;'>{$course_info["VezetekNev"]} {$course_info["KeresztNev"]} ({$course_info["Email"]})
                    felhasználó '{$course_info["KurzusNev"]}' nevű kurzusának tagjai:</div>";

                    $sql_course_members_query = "SELECT `felhasznalo`.`FelhasznaloID`, `felhasznalo`.`Email`, 
                    `felhasznalo`.`VezetekNev`, `felhasznalo`.`KeresztNev`, `kurzustag`.`Tanar` FROM `kurzustag`
                    INNER JOIN `felhasznalo` ON `kurzustag`.`FelhasznaloID` = `felhasznalo`.`FelhasznaloID`
                    WHERE `kurzustag`.`KurzusID` = {$id};";
                    $course_members = AdatLekerdezes($sql_course_members_query);

                    if (is_array($course_members)) {
                        echo "<table><thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Vezetéknév</th>
                                <th>Keresztnév</th>
                                <th>Tanár</th>
                                <th>Kurzusok</th>
                            </tr>
                        </thead>
                        <tbody>";

                        foreach ($course_members as $member) {
                            $sql_user_course_count_query = "SELECT COUNT(`ID`) AS count FROM `kurzustag` WHERE `FelhasznaloID` = {$member["FelhasznaloID"]};";
                            $user_course_count = AdatLekerdezes($sql_user_course_count_query)[0]["count"];

                            if ($member["Tanar"] == 1) {
                                $teacher = "Igen";
                            } else {
                                $teacher = "Nem";
                            }

                            echo "<tr>
                                <td>{$member["FelhasznaloID"]}</td>
                                <td>{$member["Email"]}</td>
                                <td>{$member["VezetekNev"]}</td>
                                <td>{$member["KeresztNev"]}</td>
                                <td>{$teacher}</td>
                                <td>{$user_course_count} <a href='usercourses?id={$member["FelhasznaloID"]}'>[Több infó]</a></td></td>
                            </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "<div style='margin: 10px;'>A kurzusnak nincsenek tagjai!<br>
                        <b>Ez normális esetben nem történhet meg!<br>
                        Ajánlott az adatbázis felülvizsgálata!</b></div>";
                    }

                } else {
                    header("Location: ./users");
                }
            }

            function ListStatistics() {
                $sql_user_count_query = "SELECT COUNT(`FelhasznaloID`) AS user_count FROM `felhasznalo`";
                $user_count = AdatLekerdezes($sql_user_count_query)[0]["user_count"];

                $sql_course_count_query = "SELECT COUNT(`KurzusID`) AS course_count FROM `kurzus`";
                $course_count = AdatLekerdezes($sql_course_count_query)[0]["course_count"];

                echo "<div id='stats'>
                Felhasználók száma: <b>{$user_count}</b><br>
                Kurzusok száma: <b>{$course_count}</b><br>
                </div>";
            }

            $url = explode("/", $_SERVER["REQUEST_URI"]);
            $endpoint = explode("?", end($url))[0];

            switch ($endpoint) {
                case "":
                    echo "<script type='text/javascript'>listStatistics()</script>";
                    ListStatistics();
                    break;
                case "users":
                    echo "<script type='text/javascript'>listUsers()</script>";
                    FetchUsers();
                    break;
                case "courses":
                    echo "<script type='text/javascript'>listCourses()</script>";
                    FetchCourses();
                    break;
                case "usercourses":
                    echo "<script type='text/javascript'>listUsers()</script>";
                    FetchUserCourses();
                    break;
                case "coursemembers":
                    echo "<script type='text/javascript'>listCourses()</script>";
                    FetchCourseMembers();
                    break;
                default:
                    echo "A(z) '{$endpoint}' nevű aloldal nem található!";
                    header("not found", true, 404);
                    break;
            }

            /*
            TODO
            Statisztikák
            Kurzus tagok kilistázása
                - Tulajdonos kiemelése
            */

        ?>
    </div>

   
</body>
</html>