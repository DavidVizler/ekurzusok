<?php

function DataQuery($operation, $var_types = null, $parameters = null) {
    $db = new mysqli("localhost", "root", "", "ekurzusok");
    if ($db -> connect_errno != 0) {
        return $db -> connect_error;
    }

    if (!is_null($var_types) && !is_null($parameters)) {
        $statement = $db -> prepare($operation);
        $statement -> bind_param($var_types, ...$parameters);
        $statement -> execute();
        $results = $statement -> get_result();
    } else {
        $results = $db -> query($operation);
    }

    if ($db -> errno != 0) {
        return $db -> error;
    }

    if ($results -> num_rows == 0) {
        return "Nincs találat!";
    }

    return $results -> fetch_all(MYSQLI_ASSOC);
}

function ModifyData($operation, $var_types = null, $parameters = null) {
    $db = new mysqli("localhost", "root", "", "ekurzusok");

    if ($db -> connect_errno != 0) {
        return $db -> connect_error;
    }

    if (!is_null($var_types) && !is_null($parameters)) {
        $statement = $db -> prepare($operation);
        $statement -> bind_param($var_types, ...$parameters);
        $statement -> execute();
    } else {
        $db -> query($operation);
    }

    if ($db -> errno != 0) {
        return $db -> error;
    }

    return $db -> affected_rows > 0 ? "Sikeres művelet!" : "Sikertelen művelet!";
}

?>