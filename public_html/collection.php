<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Collezione</title>
    <?php include "nav.php";?>
</head>
    <body>
    <?php
    include "connection.php";
        function showCharacter($char){
            ?>
            <div class="col-md-3">
            <div class="card cardr<?php echo $char["rarita"];?>">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6 fw-bolder">
                        <?php
                                echo $char["nome"]."</div><div class='col-6 text-end'>";
                                for($i=0;$i<$char["rarita"];$i++) echo "★";
                        ?>
                        </div>
                    </div>
                </div>
            <img src="images/chars/<?php echo $char["id_personaggio"]; ?>.png" class="card-img-top" alt="<?php echo $char["nome"]; ?> ">
            <div class="card-body">
                <div class="card-text"><?php
                    //echo "<br>".$char["descrizione"]."";
                    echo "Livello: ".$char["livello"].'
                    <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: '.$char["esp"].'%" aria-valuenow='.$char["esp"].' aria-valuemin="0" aria-valuemax="100">'.$char["esp"].'%</div>
                    </div>';
                    echo "Affetto: ".$char["affetto"].'
                    <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: '.$char["esp_affetto"].'%" aria-valuenow='.$char["esp_affetto"].' aria-valuemin="0" aria-valuemax="100">'.$char["esp_affetto"].'%</div>
                    </div><br>';?>
                    <form action="care_zone.php" method="POST" name="carezone">
                        <button class="btn btn-primary" name="carezone" id="carezone" value="<?php echo $char["id_personaggio"]; ?>">Prenditene Cura</button>
                    </form>
                </div>
                </div>
            </div>
            </div>
            <?php
        }
        function getCharacters(){
            $con = connection();
            $stmt=mysqli_prepare($con,"Select p.id_personaggio,p.nome,p.descrizione,p.rarita,pc.livello,pc.affetto,pc.esp,pc.esp_affetto from Personaggi_Collezionati pc ,Personaggi p where pc.id_utente = ? and p.id_personaggio=pc.id_personaggio");
            if ( !$stmt ) {
                error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                mysqli_close($con);
                die("Errore, riprovare!");
            }
            mysqli_stmt_bind_param($stmt,'i',$_SESSION["id_utente"]);
            mysqli_stmt_execute($stmt);
            $res=mysqli_stmt_get_result($stmt);
            if ( !$res ) {
                error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
                mysqli_stmt_close($stmt); 
                mysqli_close($con);
                die("Errore, riprovare!");
            }
            while($row= mysqli_fetch_assoc($res)){
                showCharacter($row);
            }
            mysqli_stmt_close($stmt); 
            mysqli_close($con);
        }


        
        if(empty($_SESSION["nome"])){
            echo "Non puoi vedere la collezione, non sei loggato!";
            header("Location: login.php");
        }else{
            showNav();
            ?>
        <div class="container-fluid">
            <div class="row box">
            <?php
            getCharacters();
            ?>
            </div>
        </div>
            <?php
        }
    ?>
    </body>
</html>