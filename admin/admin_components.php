<?php

function LoginForm() {
    echo <<<HTML
        <div class="admin-login-container">
            <form method='post' id="admin-login-form">
                <h1>eKurzusok<br>Adminisztrációs<br>Felület</h1>
                <label for="username">Felhasználónév: </label><br>
                <input type="text" id="username" class="admin-login"><br>
                <label for="password">Jelszó: </label><br>
                <input type="password" id="password" class="admin-login"><br>
                <div style="text-align: center; width: max; margin: 20px;">
                    <input type="submit" value="Bejelentkezés">
                </div>
                <div id="modal-container"></div>
            </form>
        </div>
    HTML;
}

function NavBar($rows = null) {
    if (is_null($rows)) {
        $rows = 25;
    }
    echo <<<HTML
        <div id="navbar-container">
            <div id="navbar">
                <div id="navitem-logo"><span id="nav-logo">eKurzusok</span></div>
                    <div id="navbar-center">
                        <div class="navitem" id="nav-home"><a href=".">Főoldal</a></div>
                        <div class="navitem" id="nav-users"><a href="users?page=1&rows={$rows}">Felhasználók</a></div>
                        <div class="navitem" id="nav-courses"><a href="courses?page=1&rows={$rows}">Kurzusok</a></div>
                    </div>
                <div class="navitem" id="logout-container"><form method="post"><input id="logout-button" type="submit" value="Kijelentkezés" name="logout"></form></div>
            </div>
        </div>
    HTML;
}

function PageManager($page, $rows, $data_type, $id = null) {
    switch ($data_type) {
        case "users":
            $sql_statement = "SELECT COUNT(user_id) AS count FROM users";
            $row_word = "felhasználó van az adatbázisban";
            $order_by_options = <<<HTML
                <option value="user_id">Felhasználó ID</option>
                <option value="lastname">Vezetéknév</option>
                <option value="firstname">Keresztnév</option>
                <option value="email">Email</option>
                <option value="courses">Kurzusok</option>
                <option value="own_courses">Saját kurzusok</option>
            HTML;
            break;
        case "courses":
            $sql_statement = "SELECT COUNT(course_id) AS count FROM courses";
            $row_word = "kurzus van az adatbázisban";
            $order_by_options = <<<HTML
                <option value="course_id">Kurzus ID</option>
                <option value="name">Név</option>
                <option value="owner">Tulajdonos</option>
                <option value="members">Tagok száma</option>
            HTML;
            break;
        case "course-info":
            $sql_statement = "SELECT COUNT(membership_id) AS count FROM memberships WHERE course_id = ?";
            $row_word = "tagja van a kurzusnak";
            $order_by_options = <<<HTML
                <option value="user_id">Felhasználó ID</option>
                <option value="membership_id">Tagság ID</option>
                <option value="lastname">Vezetéknév</option>
                <option value="firstname">Keresztnév</option>
                <option value="email">Email</option>
            HTML;
            break;
        case "user-info":
            $sql_statement = "SELECT COUNT(membership_id) AS count FROM memberships WHERE user_id = ?";
            $row_word = "kurzusnak tagja a felhasználó";
            $order_by_options = <<<HTML
                <option value="course_id">Kurzus ID</option>
                <option value="membership_id">Tagság ID</option>
                <option value="name">Kurzus név</option>
            HTML;
            break;
        default:
            break;
    }
    
    if (is_null($id)) {
        $count = DataQuery($sql_statement)[0]["count"];
        $id_input = "";
    } else {
        $count = DataQuery($sql_statement, "i", [$id])[0]["count"];
        $id_input = "<input type='number' name='id' value={$id} hidden>";
    }

    $no_prev = $page == 1 ? " disabled" : "";
    $page_count = ceil($count/$rows);
    $no_next = $page_count == $page ? " disabled" : "";

    echo " 
        <div id='info'></div>
        <div id='page-control'>
            <span id='count'></span>
            <form method='GET' id='page-form'>
                <label for='page-num'>Oldal:</label>
                <input type='button' id='page-prev' onclick='prevPage()'{$no_prev} value='<'>
                <input type='number' name='page' id='page' value='{$page}' onkeypress='manualPageTurn(event)'> / {$page_count}
                <input type='button' id='page-next' onclick='nextPage()'{$no_next} value='>'>
                <label for='rows'>Sorok oldalanként:</label>
                <select name='rows' id='rows' onchange='this.form.submit()'>
                    <option value='25'>25</option>
                    <option value='50'>50</option>
                    <option value='100'>100</option>
                    <option value='200'>200</option>
                </select>
                <label for='orderby'>Rendezés:</label>
                <select name='orderby' id='orderby' onchange='this.form.submit()'>
                    {$order_by_options}
                </select>
                <span class='row-count-span'>Összesen {$count} {$row_word}</span>
                {$id_input}
            </form>
        </div>";
}

function UsersTable() {
    echo <<<HTML
    <div id="content">
        <table>
            <thead>
                <tr>
                    <th><div>ID</div></th>
                    <th><div>Email</div></th>
                    <th><div>Vezetéknév</div></th>
                    <th><div>Keresztnév</div></th>
                    <th><div>Kurzusok</div></th>
                    <th><div>Műveletek</div></th>
                </tr>
            </thead>
            <tbody id="table-content">
            </tbody>
        </table>
        <div id="modal-container"></div>
    </div>
    HTML;
}

function CoursesTable() {
    echo <<<HTML
    <div id="content">
        <table>
            <thead>
                <tr>
                    <th><div>ID</div></th>
                    <th><div>Név</div></th>
                    <th><div>Kód</div></th>
                    <th><div>Archivált</div></th>
                    <th><div>Tulajdonos</div></th>
                    <th><div>Tagok</div></th>
                    <th><div>Műveletek</div></th>
                </tr>
            </thead>
            <tbody id="table-content">
            </tbody>
        </table>
        <div id="modal-container"></div>
    </div>
    HTML;
}

function CourseInfoTable() {
    echo <<<HTML
    <div id="content">
        <table>
            <thead>
                <tr>
                    <th><div>ID</div></th>
                    <th><div>Tagság ID</div></th>
                    <th><div>Vezetéknév</div></th>
                    <th><div>Keresztnév</div></th>
                    <th><div>Email</div></th>
                    <th><div>Rang</div></th>
                    <th><div>Több infó</div></th>
                    <th><div>Műveletek</div></th>
                </tr>
            </thead>
            <tbody id="table-content">
            </tbody>
        </table>
        <div id="modal-container"></div>
    </div>
    HTML;
}

function UserInfoTable() {
    echo <<<HTML
    <div id="content">
        <table>
            <thead>
                <tr>
                    <th><div>ID</div></th>
                    <th><div>Tagság ID</div></th>
                    <th><div>Név</div></th>
                    <th><div>Kód</div></th>
                    <th><div>Archivált</div></th>              
                    <th><div>Rang</div></th>              
                    <th><div>Több infó</div></th>
                    <th><div>Műveletek</div></th>
                </tr>
            </thead>
            <tbody id="table-content">
            </tbody>
        </table>
        <div id="modal-container"></div>
    </div>
    HTML;
}

function UserDataForm($id) {
    $sql_statement = "SELECT email, firstname, lastname FROM users WHERE user_id = ?";
    $user_data = DataQuery($sql_statement, "i", [$id]);

    if (count($user_data) == 0) {
        echo "<div id='modify-form'><h1>Nem található felhasználó az adatbázisban a köveztkező azonosítóval: <b>{$id}</b></h1></div>";
        return;
    }

    echo <<<HTML
        <div id="modify-form-div">
            <h1>Felhasználó adatmódosítás (ID: {$id})</h1>
            <form method="POST" id="modify-form">
                <input hidden type="number" id="user_id" value={$id}>
                <table>
                    <tbody>
                        <tr>
                            <td><label for="email">Email cím:</label></td>
                            <td><input class="modify-input" type="text" id="email" value="{$user_data[0]['email']}"></td>
                        </tr>
                        <tr>
                            <td><label for="lastname">Vezetéknév:</label></td>
                            <td><input class="modify-input" type="text" id="lastname" value="{$user_data[0]['lastname']}"></td>
                        </tr>
                        <tr>
                            <td><label for="firstname">Keresztnév:</label></td>
                            <td><input class="modify-input" type="text" id="firstname" value="{$user_data[0]['firstname']}"></td>
                        </tr>    
                    </tbody>
                </table>
                <div id="modify-submit">
                    <input type="submit" value="Módosítás">
                    <button type="button" id="passwd-reset" onclick="confirmModal('password', {$id}, ['{$user_data[0]['lastname']}', '{$user_data[0]['firstname']}'])">
                        Jelszó visszaállítása
                    </button>
                </div>
                <div id="modal-container"></div>
            </form>
        </div>
    HTML;
}

function MainPage() {
    $sql_statement = "SELECT COUNT(user_id) AS count FROM users";
    $user_count = DataQuery($sql_statement)[0]["count"];

    $sql_statement = "SELECT COUNT(course_id) AS count FROM courses";
    $course_count = DataQuery($sql_statement)[0]["count"];

    $sql_statement = "SELECT AVG(user_memberships.course_count) AS average_courses 
    FROM (SELECT COUNT(membership_id) AS course_count FROM memberships GROUP BY user_id) AS user_memberships;";
    $average_courses = round(DataQuery($sql_statement)[0]["average_courses"]);

    $sql_statement = "SELECT AVG(user_memberships.course_count) AS average_courses 
    FROM (SELECT COUNT(membership_id) AS course_count FROM memberships WHERE role=3 GROUP BY user_id) AS user_memberships;";
    $average_own_courses = round(DataQuery($sql_statement)[0]["average_courses"]);

    $sql_statement = "SELECT COUNT(user_memberships.course_count) AS owner_count
    FROM (SELECT COUNT(membership_id) AS course_count FROM memberships WHERE role=3 GROUP BY user_id HAVING course_count > 0) AS user_memberships;";
    $owner_percent = round(DataQuery($sql_statement)[0]["owner_count"]/$user_count*100, 2);

    $sql_statement = "SELECT AVG(user_memberships.member_count) AS average_members 
    FROM (SELECT COUNT(membership_id) AS member_count FROM memberships GROUP BY course_id) AS user_memberships;";
    $average_members = round(DataQuery($sql_statement)[0]["average_members"]);

    echo "<div id='admin-stats'>
    <h1>Statisztikák</h1>
    Összesen <b>{$user_count}</b> felhasználó és <b>{$course_count}</b> kurzus van az adatbázisban.<br>
    A felhasználók <b>{$owner_percent}%</b>-nak van saját kurzusa.<br>
    Egy felhasználó átlagosan <b>{$average_courses}</b> kurzusnak tagja.<br>
    Azon felhasználók, akik legalább egy kurzusnak tulajdonosai, átlagosan <b>{$average_own_courses}</b> saját kurzussal rendelkezik.<br>
    Egy kurzusnak átlagosan <b>{$average_members}</b> tagja van.
    </div>";
}

?>