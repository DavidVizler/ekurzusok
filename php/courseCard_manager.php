<?php
    include './sql_fuggvenyek.php';

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
?>