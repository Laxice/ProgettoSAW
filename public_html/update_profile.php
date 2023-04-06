<!DOCTYPE html>
<html lang="it">
<head>
    <title>Mofifica Profilo</title>
    <?php
 	include "nav.php";?>
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
                            <form method="POST" name="registration">
                                <label for="firstname" class="form-label">Nome e Cognome</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="firstname" name="firstname" value='<?php echo $_SESSION["nome"];?>'>
                                    <input type="text" class="form-control" id="lastname" name="lastname" value='<?php echo $_SESSION["cognome"];?>'>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input disabled type="email" class="form-control" id="email" name="email" value='<?php echo $_SESSION["email"];?>'>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Nome Utente (il nome visualizzato dagli altri giocatori)</label>
                                    <input type="username" class="form-control" id="username" name="username" value='<?php echo $_SESSION["username"];?>'>
                                </div>
                                <h6>Immagine profilo</h6>
                                <div class="row">
                                <?php
                                    for($i=0;$i<4;$i++){
                                        ?>
                                    <div class="col-6 col-sm-3 my-1">
                                        <div class="custom-control custom-checkbox image-checkbox">                                    
                                            <input type="radio"class="custom-control-input" <?php if($_SESSION["immagine_profilo"] == $i)echo "checked"; ?> id="profile_pic<?php echo $i;?>" name="profile_pic" value="<?php echo $i;?>">
                                            <label class="custom-control-label" for="profile_pic<?php echo $i;?>">
                                                <img src="images/icons/<?php echo $i;?>.png" class="img-fluid">
                                            </label>
                                        </div>
                                    </div>
                                <?php }
                                ?>
                                <br>
                                <div>
                                    <button class="btn btn-primary" id="submit" name="submit" value="submit">Invia</button>
                                    <a href="show_profile.php" class="btn btn-primary">Annulla</a>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

    if(empty($_SESSION["id_utente"])){
        echo "Non puoi modificare il tuo profilo, non sei loggato!";
        header("Location: homepage.php");
        die();
    }
    if(!isset($_POST['submit'])){
        showForm('');
        die();
    }

    if(empty($_POST['firstname'])|| empty($_POST['lastname'])){
        showForm("Errore: Devi compilare tutti i campi del form");
        die();
    }

    if(!empty($_POST['username']) && strlen($_POST['username']) > 20){
        showForm("Errore: Username troppo lungo");
        die();
    }

    if(strlen($_POST['firstname']) > 40 || strlen($_POST['lastname']) > 40){
        showForm("Errore: Nome o Cognome troppo lunghi");
        die();
    }

    $profile_pic=0;
    if(isset($_POST['profile_pic'])){
        $profile_pic=intval($_POST['profile_pic']);
        if(!is_int($profile_pic)){
            $profile_pic=0;
        }
    }
    if(empty($_POST['username']))
        $_POST['username'] = "User42";

    include "connection.php";
    $con=connection();
    $stmt=mysqli_prepare($con,"Update Utenti set nome=?,cognome=?,username=?,immagine_profilo=? where id_utente=?");
    if ( !$stmt ) {
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        mysqli_close($con);
        showForm("Errore: riprovare più tardi");
        die();
    }
    mysqli_stmt_bind_param($stmt,'sssii',$_POST["firstname"],$_POST["lastname"],$_POST["username"],
                $profile_pic,$_SESSION["id_utente"]);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_affected_rows($con);
    if($rows==1){
        //abbiamo modificato il profilo
        $_SESSION['nome']=htmlentities($_POST['firstname']);
        $_SESSION['cognome']=htmlentities($_POST['lastname']);
        $_SESSION['immagine_profilo']=$profile_pic;
        $_SESSION['username']=htmlentities($_POST['username']);
        mysqli_stmt_close($stmt); 
        mysqli_close($con);
        header("Location: show_profile.php");
    }
    else if($rows==0){
        showForm("Attenzione, non è stato modificato nessun campo"); 
        mysqli_stmt_close($stmt); 
        mysqli_close($con);
    }else{
        showForm("Attenzione, aggiornamento fallito"); 
        error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
        mysqli_stmt_close($stmt); 
        mysqli_close($con);
    }
?>
</body>
</html>