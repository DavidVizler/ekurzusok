<?php
    include './sql_functions.php';
    
    function UserCourses(){
        if($_SERVER["REQUEST_METHOD"] == "GET") {
            session_start();

            // Be van-e jelentkezve a felhasználó
            if (isset($_SESSION["user_id"])) {
                $sql_user_courses_query = "SELECT * FROM `kurzus` INNER JOIN `kurzustag` ON `kurzus`.`KurzusID` = `kurzustag`.`KurzusID`
                WHERE `kurzustag`.`FelhasznaloID` = ?;";
                $user_courses = DataQuery($sql_user_courses_query, "i", [$_SESSION["user_id"]]);

                if(is_array($user_courses)){
                    $response = $user_courses;
                } else {
                    $response = ["valasz" => "nincs talalat"];
                }
            } else {
                header("forbidden", true, 403);
                $response = ["valasz" => "nincs bejelentkezve"];
            }
        } else {
            header("bad request", true, 400);
            $response = ["valasz" => "Nem megfelelő metódus"];
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    UserCourses();
?>