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
<body onload="setRowNumber(
<?php echo $_GET["row-num"]; 
?>
)">
    <?php

        include "../api/functions.php";
        include "./admin_api.php";
        
        session_start();

        function DisplayInContentScroller($table, $rows) {
            if (isset($_GET["page-num"])) {
                $page_num = $_GET["page-num"];
            } else {
                $page_num = 1;
            }

            $select25 = "";
            $select50 = "";
            $select100 = "";

            if (isset($_GET["row-num"])) {
                switch ($_GET["row-num"]) {
                    case "50":
                        $select50 = "selected";
                        break;
                    case "100":
                        $select100 = "selected";
                        break;
                    default:
                        $select25 = "selected";
                        break;
                }
            } else {
                $select25 = "selected";
            }

            $no_next = "";
            $no_prev = "";

            if (($page_num)*$_GET["row-num"] > $rows) {
                $no_next = "disabled";
            }

            if ($page_num == 1) {
                $no_prev = "disabled";
            }

            $content = "
                <div id='page-control'>
                    <form method='GET'>
                        <label for='page-num'>Oldal:</label>
                        <button id='page-prev' onclick='prevPage()' {$no_prev}><</button>
                        <input type='number' name='page-num' id='page-num' value='{$page_num}' hidden>
                        {$page_num}
                        <button id='page-next' onclick='nextPage()' {$no_next}>></button>
                        <label for='row-num'>Sorok oldalanként:</label>
                        <select name='row-num' id='row-num' onchange='this.form.submit()'>
                            <option value='25'>25</option>
                            <option value='50'>50</option>
                            <option value='100'>100</option>
                        </select>
                    </form>
                </div>
                <div id='content'>
                    {$table}
                </div>
            ";

            echo $content;
        }

        // Bejelentkezés
        if (!isset($_SESSION["admin_user_id"])) {
            echo <<<HTML
                <div class="admin-login-container">
                    <form method='post'>
                        <label for="username">Felhasználónév: </label><br>
                        <input type="text" name="username" class="admin-login"><br>
                        <label for="password">Jelszó: </label><br>
                        <input type="password" name="password" class="admin-login"><br>
                        <div style="text-align: center; width: max; margin: 20px;">
                            <input type="submit" value="Bejelentkezés" name="admin_login">
                        </div>
                    </form>
                </div>
            HTML;

            if (isset($_POST["admin_login"])) {
                if (empty($_POST["username"]) || empty($_POST["password"])) {
                    echo "<div id='admin-login-result'>Hiányos adatok!</div>";
                } else {
                    $sql_statement = "SELECT `AdminPasswd`, `AdminID` FROM `admin` WHERE `AdminUsername` = ?;";
                    $admin_data = DataQuery($sql_statement, "s", [$_POST["username"]]);

                    if (is_array($admin_data)) {
                        if (password_verify($_POST["password"], $admin_data[0]["AdminPasswd"])) {
                            $_SESSION["admin_user_id"] = $admin_data[0]["AdminID"];
                            header("Refresh:0");
                        } else {
                            echo "<div id='admin-login-result'>Sikertelen bejelentkezés!</div>";
                        }
                    } else {
                        echo "<div id='admin-login-result'>Sikertelen bejelentkezés!</div>";
                    }
                }
            }

            exit;
        }

    ?>
<div id="site">
    <div id="navbar-container">
        <ul id="navbar">
            <li id="navitem-logo"><span id="nav-logo">eKurzusok Admin Felület</span></li>
            <li id="navitem-logo-side"></li>
            <li class="navitem active" id="nav-home"><a onclick="listStatistics()" href="./">Főoldal</a></li>
            <li class="navitem" id="nav-users"><a onclick="listUsers()" href="users">Felhasználók</a></li>
            <li class="navitem" id="nav-courses"><a onclick="listCourses()" href="courses">Kurzusok</a></li>
            <li class="navitem"><form method="post"><input id="logout-button" type="submit" value="Kijelentkezés" name="logout"></form></li>
        </ul>
    </div>

    <?php

        if (isset($_POST["logout"])) {
            session_unset();
            session_destroy();
            header("Refresh:0");
        }

        function DisplayUsers() {
            $user_data = FetchUsers();
            if (!is_array($user_data)) {
                echo "Hiba történt az adatok lekérdezése közben!";
                return;
            }

            $users = $user_data[0];
            $user_own_course_count = $user_data[1];

            if (is_array($users)) {   
                $table = "
                    <table>
                        <thead>
                            <tr>
                                <th><div>ID</div></th>
                                <th><div>Email</div></th>
                                <th><div>Vezetéknév</div></th>
                                <th><div>Keresztnév</div></th>
                                <th><div>Titkosított jelszó</div></th>
                                <th><div>Kurzusok</div></th>
                                <th><div>Eltávolítás</div></th>
                            </tr>
                        </thead>
                        <tbody>
                ";

                if (isset($_GET["page-num"])) {
                    $page = $_GET["page-num"];
                } else {
                    $page = 1;
                }

                if (isset($_GET["row-num"])) {
                    $rows = $_GET["row-num"];
                } else {
                    $rows = 25;
                }

                $start = ($page-1)*$rows;
                $end = $page*$rows;

                if ($end > count($users)) {
                    $end = count($users);
                }

                for ($i = $start; $i < $end; $i++) {
                    $table .= "
                        <tr>
                            <td>{$users[$i]["FelhasznaloID"]}</td>
                            <td>{$users[$i]["Email"]}</td>
                            <td>{$users[$i]["VezetekNev"]}</td>
                            <td>{$users[$i]["KeresztNev"]}</td>
                            <td><span class='blurred'>{$users[$i]["Jelszo"]}</span></td>
                            <td>{$users[$i]["courses"]} ({$user_own_course_count[$i]["courses"]} saját) <a href='usercourses?id={$users[$i]["FelhasznaloID"]}'>Több infó
                            </a></td>
                            <td class='torles'>
                                <form method='POST' action='javascript:;' onsubmit=\"deleteUser({$users[$i]['FelhasznaloID']}, '{$users[$i]['VezetekNev']}', 
                                '{$users[$i]['KeresztNev']}', '{$users[$i]['Email']}', '{$users[$i]['Jelszo']}')\">
                                    <input type='submit' value='Eltávolítás' name='delete_button'>
                                </form>
                            </td>
                        </tr>
                    ";
                }

                $table .= "</tbody></table>";
                
                DisplayInContentScroller($table, count($users));
            } else {
                $table = "<div style='margin: 10px;'>Nincsenek regisztrált felhasználók az adatbázisban!</div>";
            }
        }

        function FetchUserCourses() {
            if (!empty($_GET["id"]) && is_numeric($_GET["id"])) {
                $id = $_GET["id"];

                $sql_user_info_query = "SELECT `VezetekNev`, `KeresztNev`, `Email` FROM `felhasznalo` WHERE `FelhasznaloID` = {$id}";
                $user_info = DataQuery($sql_user_info_query)[0];

                echo "<div style='margin: 10px'><b>{$user_info["VezetekNev"]} {$user_info["KeresztNev"]} ({$user_info["Email"]})
                felhasználó az alábbi kurzusoknak tagja:</b>
                <br><i>A felhasználó által létrehozott kurzusok sárgával kiemelve jelennek meg.</i></div>";
                
                $sql_user_courses_query = "SELECT `kurzus`.`KurzusID`, `kurzus`.`FelhasznaloID`, 
                `kurzus`.`KurzusNev`, `kurzus`.`Kod`, `kurzus`.`Leiras`, `kurzus`.`Archivalt` 
                FROM `kurzus` INNER JOIN `kurzustag` ON `kurzus`.`KurzusID` = `kurzustag`.`KurzusID`
                WHERE `kurzustag`.`FelhasznaloID` = {$id};";
                $user_courses = DataQuery($sql_user_courses_query);

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
                        $owner = DataQuery($sql_user_course_owner_query)[0];

                        $sql_user_course_teacher_query = "SELECT `Tanar` FROM `kurzustag`
                        WHERE `FelhasznaloID` = {$id} AND `KurzusID` = {$course["KurzusID"]}";
                        $user_course_teacher = DataQuery($sql_user_course_teacher_query)[0]["Tanar"];
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
                        $course_member_count = DataQuery($sql_course_member_count_query)[0]["member_count"];

                        $sql_course_teachers_count_query = "SELECT COUNT(`ID`) AS teachers_count FROM `kurzustag` WHERE `KurzusID` = {$course['KurzusID']} AND `Tanar` = '1';";
                        $course_teachers_count = DataQuery($sql_course_teachers_count_query)[0]["teachers_count"];

                        echo "<tr{$owner_class}>
                            <td>{$course["KurzusID"]}</td>
                            <td>{$course["KurzusNev"]}</td>
                            <td>{$course["Leiras"]}</td>
                            <td>{$teacher}</td>
                            <td>{$course_member_count} ({$course_teachers_count} tanár) <a href='coursemembers?id={$course["KurzusID"]}'>Több infó</a></td>     
                            <td>{$course["Kod"]}</td>   
                            <td><a href='usercourses?id={$course["FelhasznaloID"]}'>{$owner["VezetekNev"]} {$owner["KeresztNev"]} ({$owner["Email"]})<a></td>
                        </tr>";
                    }

                    echo "</tbody></table>";
                } else {
                    echo "<div style='margin: 10px;'>A felhasználó nem tagja egy kurzusnak sem!</div>";
                }
            } else {
                header("Location: ./users");
            }
        }

        function FetchCourses() {
            $sql_courses_query = "SELECT `kurzus`.`KurzusID`, `kurzus`.`KurzusNev`, `kurzus`.`Kod`, `kurzus`.`Leiras`, `kurzus`.`Design`,  `kurzus`.`Archivalt`, 
            `felhasznalo`.`FelhasznaloID`, `felhasznalo`.`Email`, `felhasznalo`.`VezetekNev`, `felhasznalo`.`KeresztNev`, `felhasznalo`.`Jelszo`
            FROM `kurzus` INNER JOIN `felhasznalo` ON `kurzus`.`FelhasznaloID` = `felhasznalo`.`FelhasznaloID` ORDER BY `kurzus`.`KurzusID`";
            $courses = DataQuery($sql_courses_query);

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
                    $course_member_count = DataQuery($sql_course_member_count_query)[0]["member_count"];

                    $sql_course_teachers_count_query = "SELECT COUNT(`ID`) AS teachers_count FROM `kurzustag` WHERE `KurzusID` = {$course['KurzusID']} AND `Tanar` = '1';";
                    $course_teachers_count = DataQuery($sql_course_teachers_count_query)[0]["teachers_count"];

                    echo "<tr>
                        <td>{$course["KurzusID"]}</td>
                        <td>{$course["KurzusNev"]}</td>
                        <td>{$course["Leiras"]}</td>
                        <td>{$course_member_count} ({$course_teachers_count} tanár) <a href='coursemembers?id={$course["KurzusID"]}'>Több infó</a></td>    
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
                $course_info = DataQuery($sql_course_info_query)[0];

                echo "<div style='margin: 10px;'><b>{$course_info["VezetekNev"]} {$course_info["KeresztNev"]} ({$course_info["Email"]})
                felhasználó '{$course_info["KurzusNev"]}' nevű kurzusának tagjai:</b>
                <br><i>A kurzus tulajdonosa sárágval kiemelve jelenik meg.</i></div>";

                $sql_course_members_query = "SELECT `felhasznalo`.`FelhasznaloID`, `felhasznalo`.`Email`, 
                `felhasznalo`.`VezetekNev`, `felhasznalo`.`KeresztNev`, `kurzustag`.`Tanar` FROM `kurzustag`
                INNER JOIN `felhasznalo` ON `kurzustag`.`FelhasznaloID` = `felhasznalo`.`FelhasznaloID`
                WHERE `kurzustag`.`KurzusID` = {$id};";
                $course_members = DataQuery($sql_course_members_query);

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
                        $user_course_count = DataQuery($sql_user_course_count_query)[0]["count"];

                        $sql_user_own_course_count_query = "SELECT COUNT(`ID`) AS count FROM `kurzustag` 
                        INNER JOIN `kurzus` ON `kurzustag`.`KurzusID` = `kurzus`.`KurzusID`
                        WHERE `kurzustag`.`FelhasznaloID` = {$member["FelhasznaloID"]} AND `kurzus`.`FelhasznaloID` = {$member["FelhasznaloID"]};";
                        $user_own_course_count = DataQuery($sql_user_own_course_count_query)[0]["count"];

                        if ($member["Tanar"] == 1) {
                            $teacher = "Igen";
                        } else {
                            $teacher = "Nem";
                        }

                        if ($member["Email"] == $course_info["Email"]) {
                            $owner_class = " class='owner'";
                        } else {
                            $owner_class = "";
                        }

                        echo "<tr{$owner_class}>
                            <td>{$member["FelhasznaloID"]}</td>
                            <td>{$member["Email"]}</td>
                            <td>{$member["VezetekNev"]}</td>
                            <td>{$member["KeresztNev"]}</td>
                            <td>{$teacher}</td>
                            <td>{$user_course_count} ({$user_own_course_count} saját) <a href='usercourses?id={$member["FelhasznaloID"]}'>Több infó</a></td></td>
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
            $user_count = DataQuery($sql_user_count_query)[0]["user_count"];

            $sql_course_count_query = "SELECT COUNT(`KurzusID`) AS course_count FROM `kurzus`";
            $course_count = DataQuery($sql_course_count_query)[0]["course_count"];

            $sql_avarage_user_course_count_query = "SELECT AVG(tagsagok.szam) AS atlag FROM (SELECT COUNT(`kurzustag`.`ID`) AS szam 
            FROM `felhasznalo` LEFT JOIN `kurzustag` ON `felhasznalo`.`FelhasznaloID` = `kurzustag`.`FelhasznaloID` 
            GROUP BY `felhasznalo`.`FelhasznaloID`) AS tagsagok;";
            $avarage_user_course_count = round(DataQuery($sql_avarage_user_course_count_query)[0]["atlag"]);

            $sql_avarage_course_member_count_query = "SELECT AVG(tagok.szam) AS atlag FROM (SELECT COUNT(`kurzustag`.`ID`) AS szam 
            FROM `kurzus` LEFT JOIN `kurzustag` ON `kurzus`.`KurzusID` = `kurzustag`.`KurzusID` GROUP BY `kurzus`.`KurzusID`) AS tagok;";
            $avarage_course_member_count = round(DataQuery($sql_avarage_course_member_count_query)[0]["atlag"]);

            echo "<div id='stats'>
            Felhasználók száma: <b>{$user_count}</b><br>
            Kurzusok száma: <b>{$course_count}</b><br><br>
            Egy felhasználó átlagosan <b>{$avarage_user_course_count}</b> kurzusnak tagja.<br>
            Egy kurzusban átlagosan <b>{$avarage_course_member_count}</b> tag van.
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
                DisplayUsers();
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

    ?>
</div>
</body>
</html>