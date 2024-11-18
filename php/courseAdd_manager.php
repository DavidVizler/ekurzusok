<?php
    include './sql_fuggvenyek.php';

    function createCourse(){
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST["createButton"])) {
                $kurzusNev = $_POST["KurzusNev"];
                $oktatok = $_POST["OktatoNeve"];
                $leiras = $_POST["Leiras"];
                $design = $_POST["DesignSelect"];

                if (!empty($kurzusNev) && !empty($oktatok) && !empty($leiras) && !empty($design)) {
                    $kurzusKod = GenerateCourseCode();
                    $courseAdd_sql = "INSERT INTO kurzus(FelhasznaloID, KurzusNev, Oktatok, Kod, Leiras, Design) VALUES(13,'{$kurzusNev}','{$oktatok}', '{$kurzusKod}', '{$leiras}', {$design})";
                    $courseAdd = AdatModositas($courseAdd_sql);
                }
            }
        }

    }

    function selectCardData(){
        if($_SERVER["REQUEST_METHOD"] == "GET"){
            $cardDatas_sql = "SELECT KurzusID, KurzusNev, Oktatok, Kod, Leiras, Design FROM kurzus";
            $cardDatas = AdatLekerdezes($cardDatas_sql);
            if(is_array($cardDatas)){
                echo json_encode($cardDatas,JSON_UNESCAPED_UNICODE);
            }else{
                header("BAD REQUEST", true, 400);
                echo json_encode(["valasz" => "Nincsenek találatok!"],JSON_UNESCAPED_UNICODE);
            }
        }else{
            header("BAD REQUEST",true, 400);
            echo json_encode(["valasz" => "Hibás metódus!"],JSON_UNESCAPED_UNICODE);
        }
    }
    selectCardData();

    function GenerateCourseCode(){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = strlen($characters);
        $code = '';
        for($i = 0; $i < 10; $i++){
            $code .= $characters[random_int(0, $length - 1)];
        }
        return $code;
    }

    createCourse();
?>