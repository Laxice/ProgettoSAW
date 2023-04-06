<!DOCTYPE html>
<html lang="it">
<head>
    <title>Mostra Profilo</title>
    <?php
    include "nav.php";
    ?>
</head>
<body>
<?php
    function showBadSearch(){
        ?>
        <div class="card bg-light col-6">
            <h1>Nessun Risultato</h1><br>
            <h3>Mi spiace, la tua ricerca non ha portato alcun risultato</h3><br>
        </div>
        <?php
    }

    function showCibo($con){
        $stmt = mysqli_prepare($con,"SELECT nome,descrizione,exp_affetto from Cibi where id_cibo = ?");
        if(!$stmt){
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");      
            mysqli_close($con);
            die("Errore durante la ricerca delle informazioni, riprovare più tardi");
        }
        mysqli_stmt_bind_param($stmt,"i",$_GET["id"]);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if(!$res){
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");      
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            die("Errore durante la ricerca delle informazioni, riprovare più tardi");
        }
        $row= mysqli_fetch_assoc($res);
        if($row){
        ?>
        <div class="card bg-light col-6">
            <h1><?php echo $row["nome"];?></h1><br>
            <img src="images/foods/<?php echo $_GET["id"]?>.png" alt="<?php echo $row["nome"];?> in un piatto" width="50%"><br>
            <h3><?php echo $row["descrizione"];?></h3><br>
            <h4><?php echo "Assegna ".$row["exp_affetto"]." punti affetto quando lo dai a un personaggio";?></h4>
        </div>
        <?php
        }else{
            showBadSearch();
        }
    }
    function showPersonaggio($con){
        $stmt = mysqli_prepare($con,"SELECT nome,descrizione,rarita,id_cibo from Personaggi where id_personaggio = ?");
        if(!$stmt){
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");      
            mysqli_close($con);
            die("Errore durante la ricerca delle informazioni, riprovare più tardi");
        }
        mysqli_stmt_bind_param($stmt,"i",$_GET["id"]);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if(!$res){
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");      
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            die("Errore durante la ricerca delle informazioni, riprovare più tardi");
        }
        $row= mysqli_fetch_assoc($res);
        if($row){
        ?>
        <div class="card bg-light col-6">
            <h1><?php echo $row["nome"];?></h1><br>
            <h2><?php for($i=0;$i<$row["rarita"];$i++) echo "★";?></h2><br>
            <img src="images/chars/<?php echo $_GET["id"]?>.png" alt="<?php echo $row["nome"];?>" width="50%"><br>
            <h3><?php echo $row["descrizione"];?></h3><br>
            <h4>Cibo preferito:</h4>
            <a href="search_result.php?categoria=Cibo&id=<?php echo $row["id_cibo"]?>"><img src="images/foods/<?php echo $row["id_cibo"]?>.png" 
            alt="Cibo preferito di <?php echo $row["nome"];?>" width="20%"></a>
        </div>
        <?php
        }else{
            showBadSearch();
        }
    }
    function showUtente($con){
        $stmt = mysqli_prepare($con,"SELECT username,immagine_profilo,ultimo_accesso,pasti,coccole from Utenti where id_utente = ?");
        if(!$stmt){
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");      
            mysqli_close($con);
            die("Errore durante la ricerca delle informazioni, riprovare più tardi");
        }
        mysqli_stmt_bind_param($stmt,"i",$_GET["id"]);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if(!$res){
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");      
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            die("Errore durante la ricerca delle informazioni, riprovare più tardi");
        }
        $row= mysqli_fetch_assoc($res);
        if($row){
        ?>
        <div class="card bg-light col-6">
            <h1><?php echo $row["username"];?></h1><br>
            <img src="images/icons/<?php echo $row["immagine_profilo"]?>.png" alt="Profilo di <?php echo $row["username"];?>" width="50%"><br>
            <h3>Ultimo accesso: <?php echo $row["ultimo_accesso"];?><br>
                Coccole fatte: <?php echo $row["coccole"];?><br>
                Cibo dato da mangiare: <?php echo $row["pasti"];?>
            </h3>
        </div>
        <?php
        }else{
            showBadSearch();
        }
    }
    if(!isset($_GET["categoria"],$_GET["id"])){
        echo "Informazioni mancanti";
        header("Location: homepage.php");
        die();
    }
    if(($_GET["categoria"]!="Cibo" && $_GET["categoria"]!="Personaggio") && empty($_SESSION["id_utente"])){
        echo "Non puoi accedere a questa pagina, non sei loggato!";
        header("Location: homepage.php");
        die();
    }

    showNav();
    //a seconda di cosa abbiamo ricevuto dalla get dobbiamo cercare sul db
    include "connection.php";
    $con = connection();
    ?>
    <div class="container-fluid">
        <br>
        <div class="row justify-content-center">
    <?php
    if($_GET["categoria"]=="Cibo"){
        showCibo($con);
    }else if($_GET["categoria"]=="Personaggio"){
        showPersonaggio($con);
    }else if($_GET["categoria"]=="Utente"){
        showUtente($con);
    }else{
        showBadSearch();
    }
    mysqli_close($con);
    ?>
        </div>
    </div>
</body>
</html>