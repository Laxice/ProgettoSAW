<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Gatcha</title>
    <?php include "nav.php";?>
</head>

<body>

<?php
    include "connection.php";
    include "utils.php";

    function getRandomCharacter($con,$stars){
        // prendiamo un personaggio casuale con x stelle
        $stmt=mysqli_prepare($con,"Select * from Personaggi where rarita = ? order by rand() limit 1");
        if ( !$stmt ) {
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
            mysqli_rollback($con);
            mysqli_close($con);
            return false;
        }
        $stars = getRandomRarity();
        mysqli_stmt_bind_param($stmt,'i',$stars);
        mysqli_stmt_execute($stmt);
        $res=mysqli_stmt_get_result($stmt);
        if ( !$res ) {
            error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
            mysqli_rollback($con);
            mysqli_close($con);
            return false;
        }
        $row=mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        return $row;
    }
    function addCharacterToCollectionCustom($con,$char){
        $stmt=mysqli_prepare($con,"Insert into Personaggi_Collezionati(id_personaggio,id_utente) values(?,?)");
        if ( !$stmt ) {
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
            return false;
        }
        mysqli_stmt_bind_param($stmt,'ii',$char["id_personaggio"],$_SESSION['id_utente']);
        mysqli_stmt_execute($stmt);
        $rows= mysqli_affected_rows($con);
        ?>
        <div class="col-md-3 mx-auto">
            <div class="card cardr<?php echo $char["rarita"];?>">
            <div class="card-header">
                <div class="row ">
                    <div class="col-6 fw-bolder">
                    <?php
                        echo $char["nome"]."</div><div class='col-6 text-end'>";
                        for($i=0;$i<$char["rarita"];$i++) echo "★";
                    ?>
                    </div>
                </div>
            </div>
            <img src="images/chars/<?php echo $char["id_personaggio"]; ?>.png" class="card-img-top" alt="<?php echo $char["nome"]; ?>">
            <div class="card-body">
                <p class="card-text"><?php echo $char["descrizione"]."<br>";
        if($rows!=1){
            //diamo del cibo come ricompensa alternativa!!
            if(!addFoodToCollection($con,$char["id_cibo"],$char["rarita"])){
                echo "<br>C'è stato un errore nella ricezione della ricompensa alternativa, ops";
                return false;
            }else{
                echo "<br>Mi spiace, è un doppione, ecco a te del cibo come consolazione";
                echo "<img style='width:45%' src='images/foods/".$char["id_cibo"].".png'>";
            }
        }else{
            echo "<br>Nuovo!!!";
        }
        ?></p>
                <form action="care_zone.php" method="POST" name="carezone">
                    <button class="btn btn-primary" name="carezone" id="carezone" value="<?php echo $char["id_personaggio"]; ?>">Prenditene Cura</button>
                </form>
            </div>
            </div>
        </div>
        <?php
        mysqli_stmt_close($stmt);
        return true;
    }

    function getRandomRarity(){
        $r = rand(1,100);
        //001-050 1s
        //051-080 2s
        //081-090 3s
        //091-098 4s
        //099-100 5s
        if($r <=50){
            return 1;
        }else if($r <= 80){
            return 2;
        }else if($r <= 90){
            return 3;
        }else if($r <= 98){
            return 4;
        }else {
            return 5;
        }
    }


    function gatcha($many,$money){
        //controlliamo che il giocatore abbia abbastanza monete nella sessione
        if($_SESSION["monete"]<$money){
            showNav();
            die("<h1>Non puoi fare questa azione, sei troppo povero!!</h1>");
        }else{
            $_SESSION["monete"]-=$money;
            showNav();
        }
        //controlliamo che il giocatore abbia abbastanza monete nel db (potrebbe avere l'account aperto da più dispositivi)
        $con=connection();
        mysqli_begin_transaction($con);
        $stmt=mysqli_prepare($con,"Select * from Utenti where id_utente = ?");
        if ( !$stmt ) {
            error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
            mysqli_rollback($con);
            mysqli_close($con);
            $_SESSION["monete"]+=$money;
            die("Errore, riprovare!");
        }
        mysqli_stmt_bind_param($stmt,'i',$_SESSION["id_utente"]);
        mysqli_stmt_execute($stmt);
        $res=mysqli_stmt_get_result($stmt);
        if ( !$res ) {
            error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
            mysqli_rollback($con);
            mysqli_close($con);
            $_SESSION["monete"]+=$money;
            die("Errore, riprovare!");
        }
        $row= mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt); 
        if($row["monete"]<$money){
            mysqli_rollback($con);// sto facenod un rollback perchè siamo finiti in un errore, di fatto non servirebbe
            mysqli_close($con);
            $_SESSION["monete"]+=$money;
            die("<h1>Non puoi fare questa azione, sei troppo povero!!</h1>");
        }
        //Facciamo un controllo esclusivamente per il completamento delle missioni
        if($row["evocazioni"]+$many>=10 && $row["evocazioni"]+$many<=19){
            completeMission($con,10,$_SESSION["id_utente"]);
        }
        //se non è troppo povero cominciamo lo scambio!!
        $stmt=mysqli_prepare($con,"Update Utenti set monete = monete - ?, evocazioni = evocazioni + ? where id_utente = ?");
        mysqli_stmt_bind_param($stmt,'iii',$money,$many,$_SESSION["id_utente"]);
        $res=mysqli_stmt_execute($stmt);
        if ( !$res ) {
            error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
            mysqli_rollback($con);
            $_SESSION["monete"]+=$money;
            mysqli_close($con);
            die("Errore, riprovare!");
        }
        $rows = mysqli_affected_rows($con);
        mysqli_stmt_close($stmt); 
        if($rows!=1){
            mysqli_rollback($con);
            $_SESSION["monete"]+=$money;
            mysqli_close($con);
            die("Errore, riprovare!");
        }
        echo "<div class='container-fluid'><div class='row box'>";
        // ora diamo al giocatore i personaggi!!
        for($i=0;$i<$many;$i++){
            $character = getRandomCharacter($con,getRandomRarity());
            if(!addCharacterToCollectionCustom($con,$character)){
                mysqli_rollback($con);
                $_SESSION["monete"]+=$money;
                mysqli_close($con);
                die("Errore, riprovare!");
            }
        }
        mysqli_commit($con);
        mysqli_close($con);
        $_SESSION["evocazione"]=true;
        echo "</div>";
        showForm();
        echo "</div>";
    }

    function showForm(){
		?>
            <form class="row" method="POST" name="gatcha">
                <fieldset class="col-md-6 box text-center">
                    <legend>Gatcha!</legend>
                    <button class="btn btn-primary mt-2" id="submit1" name="submit1" value="submit1">1 Personaggio x 10 Monete</button>
                    <button class="btn btn-primary mt-2" id="submit10" name="submit10" value="submit10">11 Personaggi x 100 Monete</button>
                </fieldset>
            </form>
        <?php
	}

    
    if(empty($_SESSION["nome"])){
        echo "Non sei loggato, loggati!";
        header("Location: login.php");
    }else{
        if(!isset($_POST['submit1']) && !isset($_POST['submit10'])){
            showNav();
            echo "<div class='container-fluid'>";
            showForm();
            echo "</div>";
        }else{
            
            if(isset($_POST['submit1'])){
                gatcha(1,10);
            }
            if(isset($_POST['submit10'])){
                gatcha(11,100);
            }
        }
        
    }
	
?>

</body>
</html>