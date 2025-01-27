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
            <ul id="navbar">
                <li id="navitem-logo"><span id="nav-logo">eKurzusok Admin Felület</span></li>
                <li id="navitem-logo-side"></li>
                <li class="navitem" id="nav-home"><a href=".">Főoldal</a></li>
                <li class="navitem" id="nav-users"><a href="users?page=1&rows={$rows}">Felhasználók</a></li>
                <li class="navitem" id="nav-courses"><a href="courses?page=1&rows={$rows}">Kurzusok</a></li>
                <li class="navitem"><form method="post"><input id="logout-button" type="submit" value="Kijelentkezés" name="logout"></form></li>
            </ul>
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
        <div id='info'>
        </div>
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
    echo "<div id='content'>
        <table>
            <thead>
                <tr>
                    <th><div>ID</div></th>
                    <th><div>Email</div></th>
                    <th><div>Vezetéknév</div></th>
                    <th><div>Keresztnév</div></th>
                    <th><div>Kurzusok</div></th>
                </tr>
            </thead>
            <tbody id='table-content'>
            </tbody>
        </table>
    </div>";
}

function CoursesTable() {
    echo "<div id='content'>
        <table>
            <thead>
                <tr>
                    <th><div>ID</div></th>
                    <th><div>Név</div></th>
                    <th><div>Kód</div></th>
                    <th><div>Archivált</div></th>
                    <th><div>Tulajdonos</div></th>
                    <th><div>Tagok</div></th>
                </tr>
            </thead>
            <tbody id='table-content'>
            </tbody>
        </table>
    </div>";
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
    </div>
    HTML;
}

function MainPage() {
    echo "eKurzusok Admin felület";
}

?>