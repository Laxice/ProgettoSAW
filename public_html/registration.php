<!DOCTYPE html>
<html lang="it">
<head>
    <title>Registration</title>
	<?php include "nav.php";?>
</head>

<body>

<?php
	if(!empty($_SESSION["id_utente"])){
			echo "Non puoi fare la registrazione, sei loggato!";
			header("Location: homepage.php");
	}
	function showForm($error){
		showNav();
		?>
		<div class="container">
            <div class="row">
				<div class="col-md-6 box">
					<?php if (!empty($error)) {?>
						<div class="alert alert-danger" role="alert"><?php echo $error;?></div>
					<?php }?>
					<form method="POST" name="registration">
						<label for="firstname" class="form-label">Nome e Cognome</label>
						<div class="input-group mb-3">
							<input type="text" class="form-control" id="firstname" name="firstname" placeholder="Nome">
							<input type="text" class="form-control" id="lastname" name="lastname"  placeholder="Cognome">
						</div>
						<div class="mb-3">
							<label for="email" class="form-label">Email</label>
							<input type="email" class="form-control" id="email" name="email" placeholder="email@email.com">
						</div>
						<div class="mb-3">
							<label for="pass" class="form-label">Password</label>
							<input type="password" class="form-control" id="pass" name="pass"  placeholder="*********">
						</div>
						<div class="mb-3">
							<label for="confirm" class="form-label">Conferma Password</label>
							<input type="password" class="form-control" id="confirm" name="confirm"  placeholder="*********">
						</div>
						<div class="mb-3">
							<label for="username" class="form-label">Nome Utente (il nome che vedranno gli altri giocatori)</label>
							<input type="text" class="form-control" id="username" name="username"  placeholder="User42">
						</div>
						<button class="btn btn-primary" id="submit" name="submit" value="submit">Invia</button>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	if(!isset($_POST['submit'])){
		showForm('');
		die();
	}
	if(empty($_POST['firstname'])|| empty($_POST['lastname']) || empty($_POST['email']) || empty($_POST['pass'])|| empty($_POST['confirm'])){
		showForm("Errore: Devi compilare tutti i campi nel form");
		die();
	}
	if($_POST['pass']!=$_POST['confirm']){
		showForm("Errore: Le password non coincidono");
		die();
	}
	if(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)){
		showForm("Errore: Perfavore inserisci una mail valida");
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
	
	if(empty($_POST['username']))
		$_POST['username'] = "User42";

	include "connection.php";
	$con=connection();
	$stmt=mysqli_prepare($con,"Insert into Utenti(email,password,nome,cognome,username) values (?,?,?,?,?)");
	if ( !$stmt ) {
		error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
		mysqli_close($con);
		showForm("Errore: Operazione fallita.Riprovare più tardi");
		die();
	}
	$passw=password_hash($_POST["pass"],PASSWORD_DEFAULT);
	mysqli_stmt_bind_param($stmt,'sssss',$_POST["email"],$passw,$_POST["firstname"],
							$_POST["lastname"],$_POST["username"]);
	mysqli_stmt_execute($stmt);
	$id=mysqli_insert_id($con);
	$rows = mysqli_affected_rows($con);
	if($rows<1){
		error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
		mysqli_stmt_close($stmt); 
		mysqli_close($con);
		showForm("Errore: L'email è già in uso");
	}else{
		mysqli_stmt_close($stmt); 
		include "utils.php";
		completeMission($con,9,$id); //ignoriamo il valore di ritorno, il fallimento dell'assegnazione della missione non deve bloccare la creazione dell'utente
		mysqli_close($con);
		header("Location: login.php");
	}
?>
</body>
</html>