<!DOCTYPE html>
<html lang="it">
    <head>
        <title>Missioni</title>
        <?php include "nav.php";?>
    </head>
    <body>
        <?php
            function resetByDate($con){
                if(strtotime($_SESSION["ultimo_accesso"])!=strtotime(date("d-m-Y"))){
                    //se siamo qui vuol dire che l'ultimo accesso risale a un giorno diverso da oggi e quindi dobbiamo resettare le missioni giornaliere
                    $query="delete from Missioni_Utente where id_utente = ? and id_missione in( select id_missioni from Missioni where categoria in('giornaliera'";
                    //controlliamo se bisogna resettare anche quelle settimanali
                    if(date("W",strtotime($_SESSION["ultimo_accesso"]))!=date("W")){
                        $query.=",'settimanale'";
                    }
                    $query.="))";
                    $stmt=mysqli_prepare($con,$query);
                    if(!$stmt){
                        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");  
                        mysqli_close($con);
                        die("Errore, riprovare!");
                    }
                    mysqli_stmt_bind_param($stmt,"i",$_SESSION["id_utente"]);
                    mysqli_stmt_execute($stmt);
                    $rows = mysqli_affected_rows($con);
                    if($rows==-1){
                        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");  
                        mysqli_stmt_close($stmt); 
                        mysqli_close($con);
                        die("Errore, riprovare!");
                    }
                    mysqli_stmt_close($stmt);
                    
                }
            }

            function insertMission($con,$stmt,$id){
                //può ricevere lo statement dall'esterno a scopo di ottimizzazione
                if(!isset($stmt)){
                    $stmt = mysqli_prepare($con,"Insert into Missioni_Utente(id_utente,id_missione) values(?,?)");
                }
                mysqli_stmt_bind_param($stmt,"ii",$_SESSION["id_utente"],$id);
                mysqli_stmt_execute($stmt);
                return mysqli_affected_rows($con);
            }

            function checkDailies($con){
                $stmt=mysqli_prepare($con,"Insert into Missioni_Utente(id_utente,id_missione) values(?,?)");
                if(!$stmt){
                    error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");  
                    mysqli_close($con);
                    die("Errore, riprovare!");
                }
                if(strtotime($_SESSION["ultimo_accesso"])!=strtotime(date("d-m-Y")) || isset($_SESSION["login"]) ){
                    insertMission($con,$stmt,1);
                    $_SESSION["ultimo_accesso"]=date("d-m-Y");
                    unset($_SESSION["login"]);
                }
                if(isset($_SESSION["evocazione"])){
                    insertMission($con,$stmt,2);
                    unset($_SESSION["evocazione"]);
                }
                if(isset($_SESSION["pasto"])){
                    insertMission($con,$stmt,3);
                    unset($_SESSION["pasto"]);
                }
                if(isset($_SESSION["coccola"])){
                    insertMission($con,$stmt,4);
                    unset($_SESSION["coccola"]);
                }
                $stmt2 = mysqli_prepare($con,"SELECT (select count(*)-1 from Missioni where categoria = 'giornaliera') = (select count(*) from Missioni_Utente, Missioni where id_missione=id_missioni and categoria = 'giornaliera' and id_utente= ?) as cond");
                if(!$stmt2){
                    error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");  
                    mysqli_stmt_close($stmt); 
                    mysqli_close($con);
                    die("Errore, riprovare!");
                }
                mysqli_stmt_bind_param($stmt2,"i",$_SESSION["id_utente"]);
                mysqli_stmt_execute($stmt2);
                $res= mysqli_stmt_get_result($stmt2);
                if(!$res){
                    error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");  
                    mysqli_stmt_close($stmt); 
                    mysqli_close($con);
                    die("Errore, riprovare!");
                }
                $row = mysqli_fetch_assoc($res);
                if($row["cond"]){
                    //tutte le altre missioni giornaliere sono state completate, completiamo anche questa e aggiorniamo l'utente
                    mysqli_begin_transaction($con);
                    $rows = insertMission($con,$stmt,5);
                    if($rows<=0){
                        mysqli_rollback($con);
                        mysqli_stmt_close($stmt);
                        die();
                    }
                    $stmt2=mysqli_prepare($con,"Update Utenti set completamento_giornaliere = completamento_giornaliere + 1 where id_utente = ?");
                    if(!$stmt2){
                        mysqli_rollback($con);
                        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");  
                        mysqli_stmt_close($stmt); 
                        mysqli_close($con);
                        die("Errore, riprovare!");
                    }
                    mysqli_stmt_bind_param($stmt2,"i",$_SESSION["id_utente"]);
                    mysqli_stmt_execute($stmt2);
                    $rows = mysqli_affected_rows($con);
                    mysqli_stmt_close($stmt2);
                    if($rows<=0){
                        mysqli_rollback($con);
                        die();
                    }
                    mysqli_commit($con);
                }
                mysqli_stmt_close($stmt);
            }
            function checkWeeklies($con){
                //se completamento_giornaliere è 3,5 o 7 allora assegna il completamento della rispettiva missione
                $stmt=mysqli_prepare($con,"SELECT completamento_giornaliere as comp from Utenti where id_utente = ?");
                if(!$stmt){
                    error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                    mysqli_close($con);
                    die("Errore, riprovare!");
                }
                mysqli_stmt_bind_param($stmt,"i",$_SESSION["id_utente"]);
                mysqli_stmt_execute($stmt);
                $res= mysqli_stmt_get_result($stmt);
                if(!$res){
                    mysqli_stmt_close($stmt); 
                    mysqli_close($con);
                    error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                    die("Errore, riprovare più tardi");
                }
                $row = mysqli_fetch_assoc($res);
                switch($row["comp"]){
                    case 3:insertMission($con,null,6);break;
                    case 5:insertMission($con,null,7);break;
                    case 7:insertMission($con,null,8);break;
                    default:break;
                }
            }


            if(empty($_SESSION["id_utente"])){
                echo "Non puoi accedere qui, se non sei loggato!!!";
                header("Location: login.php");
                die();
            }
            showNav();
            include "connection.php";
            $con = connection();
            resetByDate($con);
            checkDailies($con);
            checkWeeklies($con);
            $stmt = mysqli_prepare($con,"SELECT * from Missioni m join Missioni_Utente mu on m.id_missioni = mu.id_missione where mu.id_utente = ? UNION select *,0 as id_missione, 0 as id_utente, 0 as data_completamento, false as ricevuto from Missioni where id_missioni not in (select id_missione from Missioni_Utente mu where mu.id_utente = ?)");
            if(!$stmt){
                error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");  
                mysqli_close($con);
                die("Errore, riprovare!");
            }
            mysqli_stmt_bind_param($stmt,"ii",$_SESSION["id_utente"],$_SESSION["id_utente"]);
            mysqli_stmt_execute($stmt);
            $res=mysqli_stmt_get_result($stmt);
            if(!$res){
                error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");  
                mysqli_stmt_close($stmt); 
                mysqli_close($con);
                die("Errore, riprovare!");
            }
            $giornaliere='<h2>Missioni giornaliere</h2>';
            $settimanali='<h2>Missioni settimanali</h2>';
            $generali='<h2>Missioni generali</h2>';
            while($row = mysqli_fetch_assoc($res)){
                $temp="<div class='row my-2'> <div class='col-8'>".$row["descrizione"]."</div>";
                if($row["data_completamento"]!=0){
                    if($row["ricevuto"])
                        $temp.="<div class='col-4'>Ricompensa Ricevuta</div>";
                    else
                        $temp.="<div class='col-4'><button class='btn btn-success btn-ricompensa' value='".$row["id_missione"]."' >Ricevi Ricompensa</button></div>";
                }else{
                    $temp.="<div class='col-4'>NON COMPLETATA</div>";
                }
                $temp.="</div>";
                if($row["categoria"]=="giornaliera")$giornaliere.=$temp;
                else if($row["categoria"]=="settimanale")$settimanali.=$temp;
                else $generali.=$temp;
            }
            echo "<div class='col-md-6 box'>".$giornaliere.$settimanali.$generali."</div>";

            
        ?>
        <script>
            $(document).ready(function(){
                
                $(".btn-ricompensa").click(function(data){
                    var parent= this;
                    $.post("mission_reward.php", {id: $(parent).val()},function(data){
                        var res = JSON.parse(data);
                        if(res.error!=null){
                            alert(res.error);
                        }else{
                            box= $(parent).parent();
                            $(box).append("<div class='col-4'>Ricompensa Ricevuta</div>");
                            modalContent="Complimenti hai ottenuto: "+res.quantita;
                            if(res.categoria == "monete"){
                                modalContent+=" monete";
                                //aggiorna le monete sulla navbar
                                $("#monete").text(parseInt($("#monete").text().slice(0,-1))+res.quantita+"$");
                            }else if(res.categoria == "cibo"){
                                modalContent+="<img src='images/foods/"+res.id_ricompensa+".png' class='img-ricompensa'>";
                            }else if(res.categoria == "personaggio"){
                                modalContent+="<img src='images/chars/"+res.id_ricompensa+".png' class='img-ricompensa'>";
                            }
                            $(".box").append(
                                '<div class="modal modal-dialog-centered" id="modal'+$(parent).val()+'" tabindex="-1">'+
                                    '<div class="modal-dialog">'+
                                        '<div class="modal-content">'+
                                        '<div class="modal-header">'+
                                            '<h5 class="modal-title">Ricompensa Ricevuta!</h5>'+
                                            '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'+
                                        '</div>'+
                                        '<div class="modal-body">'+
                                        modalContent+
                                        '</div>'+
                                        '<div class="modal-footer">'+
                                            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Evviva!</button>'+
                                        '</div>'+
                                        '</div>'+
                                    '</div>'+
                                '</div>'
                            );
                            var options = {backdrop:true,keyboard:true,focus:true};
                            var myModal = new bootstrap.Modal( $("#modal"+$(parent).val()), options);
                            myModal.toggle();
                            $(parent).remove();
                        }
                    });
                });
            });
        </script>
    </body>
</html>