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

function NavBar($rows) {
    if (empty($rows)) {
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

function PageManager($page, $rows, $data_type) {
    switch ($data_type) {
        case "users":
            $sql_statement = "SELECT COUNT(`FelhasznaloID`) AS count FROM `felhasznalo`";
            $row_word = "felhasználó van az adatbázisban";
            break;
        case "courses":
            $sql_statement = "SELECT COUNT(`KurzusID`) AS count FROM `kurzus`";
            $row_word = "kurzus van az adatbázisban";
            break;
        default:
            break;
    }
    
    $count = DataQuery($sql_statement)[0]["count"];

    $no_prev = $page == 1 ? " disabled" : "";
    $page_count = ceil($count/$rows);
    $no_next = $page_count == $page ? " disabled" : "";

    echo "<div id='page-control'>
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
                <span class='row-count-span'>Összesen {$count} {$row_word}</span>
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
                    <th><div>Leírás</div></th>
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

function MainPage() {
    echo "eKurzusok Admin felület";
}

?>