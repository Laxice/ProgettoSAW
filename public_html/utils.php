<?php
function addFoodToCollection($con,$id_cibo,$quantita){
    //Ci sono 2 casi da gestire:
    //  Il giocatore ha già il cibo nella collezione
    //  Il giocatore non ha il cibo
    $stmt= mysqli_prepare($con,"SELECT id_cibo from Cibi_Collezionati where id_cibo=? and id_utente=?");
    if(!$stmt){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con),3,"../error.log");
        return false;
    }
    mysqli_stmt_bind_param($stmt,"ii",$id_cibo,$_SESSION["id_utente"]);
    mysqli_stmt_execute($stmt);
    $res=mysqli_stmt_get_result($stmt);
    if(!$res){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con),3,"../error.log");
        mysqli_stmt_close($stmt);
        return false;
    }
    mysqli_stmt_close($stmt);
    if(mysqli_num_rows($res)==0){
        //il giocatore non ha il cibo nella collezione (va fatta una insert)
        $stmt=mysqli_prepare($con,"INSERT into Cibi_Collezionati (quantita,id_utente, id_cibo) values (?,?,?)");
        if(!$stmt){
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con),3,"../error.log");
            return false;
        }
    }else{
        //il giocatore ha il cibo nella collezione (va fatta una update)
        $stmt=mysqli_prepare($con,"UPDATE Cibi_Collezionati set quantita=quantita+? where id_utente=? and id_cibo=?");
        if(!$stmt){
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con),3,"../error.log");
            return false;
        }

    }
    mysqli_stmt_bind_param($stmt,"iii",$quantita,$_SESSION["id_utente"],$id_cibo);
    mysqli_stmt_execute($stmt);
    if(mysqli_affected_rows($con)<=0){
        error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
        mysqli_stmt_close($stmt);
        return false;
    }
    return true;
}

function addFavouriteFoodToCollection($con,$id_personaggio){
    $stmt = mysqli_prepare($con,"Select id_cibo,rarita from Personaggi where id_personaggio=?");
    if ( !$stmt ) {
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        return false;
    }
    mysqli_stmt_bind_param($stmt,'i',$id_personaggio);
    mysqli_stmt_execute($stmt);
    $res=mysqli_stmt_get_result($stmt);
    if(!$res){
        error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($con). PHP_EOL,3,"../error.log");
        mysqli_stmt_close($stmt);
        return false;
    }
    $row=mysqli_fetch_assoc($res);
    if(!addFoodToCollection($con,$row["id_cibo"],$row["rarita"])){
        mysqli_stmt_close($stmt);
        return false;
    }
    mysqli_stmt_close($stmt);
    return true;
}

function addCharacterToCollection($con,$id_personaggio){
    $stmt=mysqli_prepare($con,"Insert into Personaggi_Collezionati(id_personaggio,id_utente) values(?,?)");
    if ( !$stmt ) {
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        return false;
    }
    mysqli_stmt_bind_param($stmt,'ii',$id_personaggio,$_SESSION['id_utente']);
    mysqli_stmt_execute($stmt);
    $rows= mysqli_affected_rows($con);
    if($rows==0){
        //diamo del cibo come ricompensa alternativa!!
        if(!addFavouriteFoodToCollection($con,$id_personaggio)){
            mysqli_stmt_close($stmt);
            return false;
        }
    }else if($rows==-1){
        if(mysqli_errno($con)==1062){
            //il personaggio è un duplicato, diamo ricompensa alternativa.
            if(!addFavouriteFoodToCollection($con,$id_personaggio)){
                mysqli_stmt_close($stmt);
                return false;
            }
        }else{
            //ci sono stati degli errori nell'inserimento!!
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con)." ".mysqli_errno($con). PHP_EOL,3,"../error.log");
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    mysqli_stmt_close($stmt);
    return true;
}
function completeMission($con,$id_missione,$id_utente){
    //Quando vengono compiute azioni che triggherano il completamento di una missione specifica bisogna chiamare questa funzione
    $closeCon=false;
    if(!isset($con)){
        include "connection.php";
        $con= connection();
        $closeCon = true;
    }
    $stmt= mysqli_prepare($con,"INSERT into Missioni_Utente(id_utente,id_missione) values(?,?)");
    if(!$stmt){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con)." ".mysqli_errno($con). PHP_EOL,3,"../error.log");
        if($closeCon)mysqli_close($con);
        return false;
    }
    mysqli_stmt_bind_param($stmt,"ii",$id_utente,$id_missione);
    mysqli_stmt_execute($stmt);
    $rows=mysqli_affected_rows($con);
    if($rows<=0){
        error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt)." ".mysqli_errno($con). PHP_EOL,3,"../error.log");
        mysqli_stmt_close($stmt);
        if($closeCon)mysqli_close($con);
        return false;
    }
    mysqli_stmt_close($stmt);
    if($closeCon)mysqli_close($con);
    return true;
}

?>