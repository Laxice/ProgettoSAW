<?php
    error_reporting(E_ERROR | E_PARSE);
    session_start();
    if(!isset($_GET["q"])){
        $response->error="Manca la stringa di ricerca";
        die(json_encode($response));
    }
    include "connection.php";
    $con = connection();
    $stmt= mysqli_prepare($con,"SET @q := CONCAT('%',?,'%')");
    if(!$stmt){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        mysqli_close($con);
        $response->error="Problema nella ricerca";
        die(json_encode($response));
    }
    mysqli_stmt_bind_param($stmt,"s",$_GET["q"]);
    mysqli_stmt_execute($stmt);
    $response = new \stdClass();
    $query="SELECT nome, id_personaggio as id, 'Personaggio' as categoria from Personaggi where nome like @q "
        ."UNION SELECT nome, id_cibo as id,'Cibo' as categoria from Cibi where nome like @q ";
    if(isset($_SESSION["id_utente"])){
        //se è autenticato, può cercare anche altre cose
        $query.="UNION SELECT username, id_utente as id,'Utente' as categoria from Utenti where username like @q ";
        //."UNION SELECT descrizione, id_missioni as id,'Missione' as categoria from Missioni where descrizione like @q ";
    }
    $stmt = mysqli_prepare($con,$query);
    if(!$stmt){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        mysqli_close($con);
        $response->error="Problema nella ricerca";
        die(json_encode($response));
    }
    
    mysqli_stmt_execute($stmt);
    $res=mysqli_stmt_get_result($stmt);
    if(!$res){
        error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
        mysqli_close($con);
        mysqli_stmt_close($stmt);
        $response->error="Problema nella ricerca";
        die(json_encode($response));
    }
    $search_results=array();
    while($row=mysqli_fetch_assoc($res)){
        $search_results[]=$row;
        //echo json_encode($row);
    }
    $response->search_results=$search_results;
    echo json_encode($response);
    mysqli_stmt_close($stmt);
    mysqli_close($con);
       
?>