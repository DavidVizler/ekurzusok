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
        <li class="navitem active" id="nav-home"><a onclick="listStatistics()" href="./index.php"><b>eKurzusok Admin</b></a></li>
        <li class="navitem" id="nav-users"><a onclick="listUsers()" href="./index.php?fetch=users">Felhasználók</a></li>
        <li class="navitem" id="nav-courses"><a onclick="listCourses()" href="./index.php?fetch=courses">Kurzusok</a></li>
    </ul>

    <div id="content">
        <?php
            include "../php/sql_fuggvenyek.php";

            if (isset($_REQUEST["fetch"]) && !empty($_REQUEST["fetch"])) {
                $fetch = $_REQUEST["fetch"];

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
                                    <form method='POST' action='javascript:;' onsubmit=\"deleteUser({$user["FelhasznaloID"]}, '{$user["VezetekNev"]}', '{$user["KeresztNev"]}', '{$user["Email"]}')\">
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
                    $sql_courses_query = "SELECT `kurzus`.`KurzusID`, `kurzus`.`KurzusNev`, `kurzus`.`Kod`, `kurzus`.`Leiras`, 
                    `kurzus`.`Design`, `kurzus`.`Archivalt`, `felhasznalo`.`Email`, `felhasznalo`.`VezetekNev`, `felhasznalo`.`KeresztNev`
                    FROM `kurzus` INNER JOIN `felhasznalo` ON `kurzus`.`FelhasznaloID` = `felhasznalo`.`FelhasznaloID`";
                    $courses = AdatLekerdezes($sql_courses_query);

                    if (is_array($courses)) {   
                        echo "<table><thead>
                            <tr>
                                <th>ID</th>
                                <th>Név</th>
                                <th>Leirás</th>
                                <th>Kód</th>
                                <th>Design</th>
                                <th>Tulajdonos</th>
                            </tr>
                        </thead>
                        <tbody>";

                        foreach ($courses as $course) {
                            echo "<tr>
                                <td>{$course["KurzusID"]}</td>
                                <td>{$course["KurzusNev"]}</td>
                                <td>{$course["Leiras"]}</td>
                                <td>{$course["Kod"]}</td>
                                <td>{$course["Design"]}</td>            
                                <td>{$course["VezetekNev"]} {$course["KeresztNev"]} ({$course["Email"]})</td>
                            </tr>";
                        }

                        echo "</tbody></table>";
                    } else {
                        echo "Nincsenek kurzusok az adatbázisban!";
                    }

                    // TODO:
                    // Kurzus törlése
                }

                switch ($fetch) {
                    case "users":
                        echo "<script type='text/javascript'>listUsers()</script>";
                        FetchUsers();
                        break;
                    case "courses":
                        echo "<script type='text/javascript'>listCourses()</script>";
                        FetchCourses();
                        break;
                    default:
                        echo "<script type='text/javascript'>listStatistics()</script>";
                        echo "eKurzusok Adminisztrációs Felület";
                        break;

                    // TODO:
                    // Statisztikák megjelenítése a főoldalon
                    // pl. felhasználók száma, kurzusok száma, leadott feladatok száma stb.
                }
            } else {
                echo "eKurzusok Adminisztrációs Felület";
            }
        ?>
    </div>

   
</body>
</html>