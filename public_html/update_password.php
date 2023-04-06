<!DOCTYPE html>
<html lang="it">
<head>
    <title>Cambia Password</title>
    <?php include "nav.php";?>
</head>

<body>

<?php

	function showForm($error){
        showNav();
        ?>
        <br>
        <div class="container">
            <div class="card bg-light col-sm-11 col-md-8 mx-auto">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-11 col-md-8 mx-auto">
                        <?php if (!empty($error)) {?>
                            <div class="alert alert-danger" role="alert"><?php echo $error;?></div>
                        <?php }?>
                        <form method="POST" name="login">
                            <div class="mb-3">
                                <label for="old_pass" class="form-label">Password Vecchia</label>
                                <input type="password" class="form-control" id="old_pass" name="old_pass"  placeholder="*********">
                            </div>
                            <div class="mb-3">
                                <label for="new_pass" class="form-label">Password Nuova</label>
                                <input type="password" class="form-control" id="new_pass" name="new_pass"  placeholder="*********">
                            </div>
                            <div class="mb-3">
                                <label for="conf" class="form-label">Conferma Password Nuova</label>
                                <input type="password" class="form-control" id="conf" name="conf"  placeholder="*********">
                            </div>
                            <button class="btn btn-primary" id="submit" name="submit" value="submit">Invia</button>
                            <a href="show_profile.php" class="btn btn-primary">Annulla</a>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    if(empty($_SESSION["id_utente"])){
        echo "Non puoi modificare la tua password, non sei loggato!";
        header("Location: homepage.php");
        die();
    }

    if(!isset($_POST['submit'])){
        showForm('');
        die();
    }

    if(empty($_POST['old_pass'])||empty($_POST['new_pass'])||empty($_POST['conf'])) {
        showForm("Errore: Per favore compila tutti i campi del form");
        die();
    }

    if($_POST['new_pass']!=$_POST['conf']){
        showForm("Errore: La nuova password e la conferma non corrispondono");
        die();
    }

    include "connection.php";
    // bisogna lanciare query sul db per verificare che la vecchia password sia corretta
    $con=connection();
    $stmt=mysqli_prepare($con,"Select * from Utenti where id_utente = ?");
    if ( !$stmt ) {
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        mysqli_close($con);
        showForm("Errore: riprovare");
        die();
    }
    mysqli_stmt_bind_param($stmt,'i',$_SESSION["id_utente"]);
    mysqli_stmt_execute($stmt);
    $res=mysqli_stmt_get_result($stmt);
    if ( !$res ) {
        error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
        mysqli_stmt_close($stmt);
        mysqli_close($con);
        showForm("Errore: riprovare");
        die();
    }
    $rows = mysqli_num_rows($res);
    if($rows==1){
        //controlliamo la password
        $row = mysqli_fetch_assoc($res);
        if(password_verify($_POST["old_pass"],$row["password"])){
            //la vecchia password è corretta, cambiamola con quella nuova
            $stmt = mysqli_prepare($con,"Update Utenti set password = ? where id_utente = ?");
            if ( !$stmt ) {
                error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                mysqli_close($con);
                showForm("Errore: riprovare più tardi");
                die();
            }
            $passw=password_hash($_POST["new_pass"],PASSWORD_DEFAULT);
			mysqli_stmt_bind_param($stmt,'si',$passw,$_SESSION["id_utente"]);
            mysqli_stmt_execute($stmt);
			$rows = mysqli_affected_rows($con);
			if($rows<1){
				error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
				mysqli_stmt_close($stmt); 
				mysqli_close($con);
				showForm("Errore: riprovare più tardi");
                die();
			}
            mysqli_stmt_close($stmt); 
            mysqli_close($con);
            header("Location: show_profile.php");
            die();
        }else{
            showForm("Errore: password errata");
        }
    }else{
        showForm("Errore: riprovare");
    }
    mysqli_close($con);
?>

</body>
</html>