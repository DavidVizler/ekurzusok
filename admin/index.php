<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKurzusok Rendszer Adminisztráció</title>
    <link rel="stylesheet" href="./admin.css" type="text/css">
    <script src="./admin.js"></script>
</head>
<body>
    <ul id="navbar">
        <li class="navitem active" id="nav-home"><a onclick="listStatistics()" href="./"><b>eKurzusok Admin</b></a></li>
        <li class="navitem" id="nav-users"><a onclick="listUsers()" href="users">Felhasználók</a></li>
        <li class="navitem" id="nav-courses"><a onclick="listCourses()" href="courses">Kurzusok</a></li>
    </ul>

    <div id="content">
        <?php
            include "../php/sql_fuggvenyek.php";

            $url = explode("/", $_SERVER["REQUEST_URI"]);
            $endpoint = end($url);

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
                            <th>Titkosított jelszo</th>
                            <th>Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>";

                    foreach ($users as $user) {
                        echo "<tr>
                            <td>{$user["FelhasznaloID"]}</td>
                            <td>{$user["Email"]}</td>
                            <td>{$user["VezetekNev"]}</td>
                            <td>{$user["KeresztNev"]}</td>
                            <td><span class='blurred'>{$user["Jelszo"]}</span></td>
                            <td class='torles'>
                                <form method='POST' action='javascript:;' onsubmit=\"deleteUser({$user["FelhasznaloID"]}, '{$user["VezetekNev"]}', '{$user["KeresztNev"]}', '{$user["Email"]}', '{$user["Jelszo"]}')\">
                                    <input type='submit' value='Eltávolítás' name='delete_button'>
                                </form>
                            </td>
                        </tr>";
                    }

                    echo "</tbody></table>";
                } else {
                    echo "Nincsenek regisztrált felhasználók az adatbázisban!";
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
                            <th>Tanárok száma</th>
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
                            <td>{$course_member_count}</td>    
                            <td>{$course_teachers_count}</td>   
                            <td>{$course["Kod"]}</td>
                            <td>{$course["Design"]}</td>     
                            <td>{$course["VezetekNev"]} {$course["KeresztNev"]} ({$course["Email"]})</td>
                            <td class='torles'>
                                <form method='POST' action='javascript:;' onsubmit=\"deleteCourse('{$course["KurzusID"]}', '{$course["KurzusNev"]}', '{$course["FelhasznaloID"]}', '{$course["VezetekNev"]}', '{$course["KeresztNev"]}', '{$course["Jelszo"]}')\">
                                    <input type='submit' value='Eltávolítás' name='delete_button'>
                                </form>
                            </td>
                        </tr>";
                    }

                    echo "</tbody></table>";
                } else {
                    echo "Nincsenek kurzusok az adatbázisban!";
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
                default:
                    echo "A(z) '{$endpoint}' nevű aloldal nem található!";
                    header("not found", true, 404);
                    break;
            }

            /*
            TODO
            Statisztikák
            Kurzusok törlése
            Frontend fejlesztés
            */

        ?>
    </div>

   
</body>
</html>