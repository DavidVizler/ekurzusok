<?php

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    return "hiba";
}

if (!AdminLoginCheck()) {
    return "hiba";
}

function FetchUsers() {
    // ID, e-mail, vezetéknév, keresztnév, titkosított jelszó, kurzusok
    $sql_statement = "SELECT `felhasznalo`.`FelhasznaloID`, `felhasznalo`.`Email`, `felhasznalo`.`VezetekNev`, 
    `felhasznalo`.`KeresztNev`, `felhasznalo`.`Jelszo`, COUNT(`kurzustag`.`ID`) AS courses 
    FROM `felhasznalo` 
    LEFT JOIN `kurzustag` ON `felhasznalo`.`FelhasznaloID` = `kurzustag`.`FelhasznaloID` 
    GROUP BY `felhasznalo`.`FelhasznaloID`;";
    $users = DataQuery($sql_statement);

    // Saját kurzusok
    $sql_statement = "SELECT `felhasznalo`.`FelhasznaloID`, COUNT(`kurzus`.`KurzusID`) AS courses 
    FROM `felhasznalo`
    LEFT JOIN `kurzus` ON `felhasznalo`.`FelhasznaloID` = `kurzus`.`FelhasznaloID` 
    GROUP BY `felhasznalo`.`FelhasznaloID`;";
    $user_own_course_count = DataQuery($sql_statement);

    return [$users, $user_own_course_count];
}
?>