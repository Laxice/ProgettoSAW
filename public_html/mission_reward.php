<?php
    //pagina che si occupa di dare all'utente la ricompensa per la missione completata per la quale ha cliccato "Ricevi ricompensa"
    include "connection.php";
    include "utils.php";
    error_reporting(E_ERROR | E_PARSE);
    $response = new \stdClass();
    session_start();
    if(empty($_SESSION["id_utente"])){
        $response->error="Sessione scaduta o non valida";
        die(json_encode($response));
    }
    if(empty($_POST["id"])){
        $response->error="Richiesta malformata";
        die(json_encode($response));
    }

    $con = connection();
    $stmt= mysqli_prepare($con,"SELECT * from Missioni_Utente where id_missione=? and id_utente=?");
    if(!$stmt){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        mysqli_close($con);
        $response->error="Problema nel recupero dei dati";
        die(json_encode($response));
    }
    mysqli_stmt_bind_param($stmt,"ii",$_POST["id"],$_SESSION["id_utente"]);
    mysqli_stmt_execute($stmt);
    $res=mysqli_stmt_get_result($stmt);
    if(!$res){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        mysqli_stmt_close($stmt);
        mysqli_close($con);
        $response->error="Problema nel recupero dei dati";
        die(json_encode($response));
    }
    if(mysqli_num_rows($res)==0){
        mysqli_stmt_close($stmt);
        mysqli_close($con);
        $response->error="Missione non completata";
        die(json_encode($response));
    }else{
        $row=mysqli_fetch_assoc($res);
        if($row["ricevuto"]){
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            $response->error="Ricompensa già ricevuta";
            die(json_encode($response));
        }else{
            //l'utente non ha nacora ricevuto la ricompensa ma ha effettivamente completato la missione
            mysqli_begin_transaction($con);
            //cambio lo stato della ricompensa a ricevuta
            $stmt = mysqli_prepare($con,"UPDATE Missioni_Utente set ricevuto=True where id_missione = ? and id_utente = ?");
            if(!$stmt){
                error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                mysqli_rollback($con);
                mysqli_close($con);
                $response->error="Problema nella ricompensa";
                die(json_encode($response));
            }
            mysqli_stmt_bind_param($stmt,"ii",$_POST["id"],$_SESSION["id_utente"]);
            mysqli_stmt_execute($stmt);
            if(mysqli_affected_rows($con)<=0){
                error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                mysqli_rollback($con);
                mysqli_stmt_close($stmt);
                mysqli_close($con);
                $response->error="Problema nella ricompensa";
                die(json_encode($response));
            }
            //vado a leggere sulle missioni qual è effettivamente la ricompensa da dare all'utente
            $stmt= mysqli_prepare($con,"SELECT categoria_ricompensa, quantita_ricompensa, coalesce(id_cibo,id_personaggio) as id_ricompensa from Missioni where id_missioni = ?");
            if(!$stmt){
                error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                mysqli_rollback($con);
                mysqli_close($con);
                $response->error="Problema nella ricompensa";
                die(json_encode($response));
            }
            mysqli_stmt_bind_param($stmt,"i",$_POST["id"]);
            mysqli_stmt_execute($stmt);
            $res=mysqli_stmt_get_result($stmt);
            if(!$res){
                error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                mysqli_rollback($con);
                mysqli_stmt_close($stmt);
                mysqli_close($con);
                $response->error="Problema nella ricompensa";
                die(json_encode($response));
            }
            $row=mysqli_fetch_assoc($res);
            if($row["categoria_ricompensa"]=="monete"){
                //aggiungi le monete all'utente (anche nella sessione)
                $stmt = mysqli_prepare($con,"Update Utenti set monete=monete+? where id_utente=?");
                if(!$stmt){
                    error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                    mysqli_rollback($con);
                    mysqli_close($con);
                    $response->error="Problema nella ricompensa";
                    die(json_encode($response));
                }
                mysqli_stmt_bind_param($stmt,"ii",$row["quantita_ricompensa"],$_SESSION["id_utente"]);
                mysqli_stmt_execute($stmt);
                if(mysqli_affected_rows($con)<=0){
                    error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                    mysqli_rollback($con);
                    mysqli_stmt_close($stmt);
                    mysqli_close($con);
                    $response->error="Problema nella ricompensa";
                    die(json_encode($response));
                }
                $_SESSION["monete"]+=$row["quantita_ricompensa"];//in questo modo la sessione rimane aggiornata su quante monete il giocatore possiede
            }else if($row["categoria_ricompensa"]=="cibo"){
                if(!addFoodToCollection($con,$row["id_ricompensa"],$row["quantita_ricompensa"])){
                    error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                    mysqli_rollback($con);
                    mysqli_stmt_close($stmt);
                    mysqli_close($con);
                    $response->error="Problema nella ricompensa";
                    die(json_encode($response));
                }
            }else if($row["categoria_ricompensa"]=="personaggio"){
                //aggiungi il personaggio all'utente (attraverso la funzione in utils)
                if(!addCharacterToCollection($con,$row["id_ricompensa"])){
                    error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                    mysqli_rollback($con);
                    mysqli_stmt_close($stmt);
                    mysqli_close($con);
                    $response->error="Problema nella ricompensa";
                    die(json_encode($response));
                }
            }else{
                error_log(__FILE__.": ".__LINE__." categoria ricompensa sconosciuta". PHP_EOL,3,"../error.log");
                mysqli_rollback($con);
                mysqli_stmt_close($stmt);
                mysqli_close($con);
                $response->error="Problema nella ricompensa";
                die(json_encode($response));
            }
            $response->categoria=$row["categoria_ricompensa"];
            $response->quantita=$row["quantita_ricompensa"];
            $response->id_ricompensa=$row["id_ricompensa"];
            echo json_encode($response);
            mysqli_commit($con);
            mysqli_close($con);
        }
    }

?>