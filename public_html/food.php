<?php
    session_start();
    include "connection.php";
    error_reporting(E_ERROR | E_PARSE);
        $con = connection();
        $list = new \stdClass();

        if(empty($_SESSION["id_utente"])){
            $list->error="Sessione scaduta o non valida";
            die(json_encode($list));
        }

        $stmt= mysqli_prepare($con,"SELECT cc.id_cibo, quantita, nome FROM Cibi_Collezionati cc join Cibi c on cc.id_cibo = c.id_cibo WHERE id_utente = ? ");
        if(!$stmt){
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
            mysqli_close($con);
            $list->error="Errore nella query";
            die("Errore, riprovare piu tardi!");
        }
        mysqli_stmt_bind_param($stmt,"i",$_SESSION["id_utente"]);
        mysqli_stmt_execute($stmt);
        $res=mysqli_stmt_get_result($stmt);
        if(!$res){
            error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
            mysqli_close($con);
            mysqli_stmt_close($stmt);
            $list->error="Errore nella query";
            die("Errore, riprovare piu tardi!");
        }
        while($row=mysqli_fetch_assoc($res)){
            $search_results[]=$row;
        }
        $list=$search_results;
        echo json_encode($list);
        mysqli_stmt_close($stmt);
        mysqli_close($con);

?>