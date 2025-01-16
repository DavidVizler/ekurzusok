<?php

function LoginForm() {
    echo <<<HTML
        <div class="admin-login-container">
            <form method='post'>
                <h1>eKurzusok<br>Adminisztrációs<br>Felület</h1>
                <label for="username">Felhasználónév: </label><br>
                <input type="text" name="username" class="admin-login"><br>
                <label for="password">Jelszó: </label><br>
                <input type="password" name="password" class="admin-login"><br>
                <div style="text-align: center; width: max; margin: 20px;">
                <input type="submit" value="Bejelentkezés" name="login">
                </div>
            </form>
        </div>
    HTML;
}

function NavBar() {
    echo <<<HTML
        <div id="navbar-container">
            <ul id="navbar">
                <li id="navitem-logo"><span id="nav-logo">eKurzusok Admin Felület</span></li>
                <li id="navitem-logo-side"></li>
                <li class="navitem" id="nav-home"><a href="./">Főoldal</a></li>
                <li class="navitem" id="nav-users"><a href="users">Felhasználók</a></li>
                <li class="navitem" id="nav-courses"><a href="courses">Kurzusok</a></li>
                <li class="navitem"><form method="post"><input id="logout-button" type="submit" value="Kijelentkezés" name="logout"></form></li>
            </ul>
        </div>
    HTML;
}

function PageManager($page, $rows) {
    $sql_statement = "SELECT COUNT(`FelhasznaloID`) AS count FROM `felhasznalo`";
    $user_count = DataQuery($sql_statement)[0]["count"];

    $no_prev = $page == 1 ? " disabled" : "";
    $page_count = ceil($user_count/$rows);
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
                <span class='row-count-span'>Összesen {$user_count} felhasználó van az adatbázisban</span>
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
    
}

function MainPage() {
    echo "eKurzusok Admin felület";
}

?>