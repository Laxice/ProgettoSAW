<!DOCTYPE html>
<html lang="it">

<head>
    <title>Login</title>
    <?php include "nav.php"; ?>
</head>

<body>

    <?php
    function resetMissioni($con, $settimanali)
    {
        $query = "delete from Missioni_Utente where id_utente = ? and id_missione in( select id_missioni from Missioni where categoria in('giornaliera'";
        //controlliamo se bisogna resettare anche quelle settimanali
        if ($settimanali) {
            $query .= ",'settimanale'";
        }
        $query .= "))";
        $stmt = mysqli_prepare($con, $query);
        if (!$stmt) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
            mysqli_close($con);
            die("Errore, riprovare!");
        }
        mysqli_stmt_bind_param($stmt, "i", $_SESSION["id_utente"]);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_affected_rows($con);
        if ($rows == -1) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            die("Errore, riprovare!");
        }
        mysqli_stmt_close($stmt);
    }

    function showForm($error)
    {
        showNav();
    ?>
        <div class="container ">
            <div class="row">
                <div class="col-md-6 box">
                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                    <?php } ?>
                    <form action="login.php" method="POST" name="login">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="email@email.com">
                        </div>
                        <div class="mb-3">
                            <label for="pass" class="form-label">Password</label>
                            <input type="password" class="form-control" id="pass" name="pass" placeholder="*********">
                        </div>
                        <button class="btn btn-primary" id="submit" name="submit" value="submit">Invia</button>
                    </form>
                    <br>
                    Non hai ancora un account?<a href="registration.php">Registrati</a>
                </div>
            </div>
        </div>
    <?php
    }

    if (!empty($_SESSION["id_utente"])) {
        header("Location: homepage.php");
        die("Non puoi fare la login, sei già loggato!");
    }
    if (!isset($_POST['submit'])) {
        showForm('');
        die();
    }
    if (empty($_POST['email']) || empty($_POST['pass'])){
        showForm("Errore: Per favore compila tutti i campi del form");
        die();
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        showForm("Errore: Per favore iserisci una mail valida");
        die();
    }
    include "connection.php";
    // bisogna lanciare query sul db per verificare
    $con = connection();
    $stmt = mysqli_prepare($con, "Select * from Utenti where email = ?");
    if (!$stmt) {
        error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
        mysqli_close($con);
        showForm("Errore: riprovare");
        die();
    }
    mysqli_stmt_bind_param($stmt, 's', $_POST["email"]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if(!$res){
        error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_stmt_error($stmt) . PHP_EOL, 3, "../error.log");
        mysqli_stmt_close($stmt);
        mysqli_close($con);
        showForm("Errore: riprovare");
        die();
    }
    $rows = mysqli_num_rows($res);
    
    mysqli_stmt_close($stmt);
    //se c'è esattamente una riga allora la login è valida
    if ($rows == 1) {
        //controlliamo la password
        $row = mysqli_fetch_assoc($res);
        if (password_verify($_POST["pass"], $row["password"])) {
            //la login è avvenuta con successo, salvo dati utili nella sessione
            $_SESSION['id_utente'] = $row['id_utente'];
            $_SESSION['email'] = htmlentities($row['email']);
            $_SESSION['nome'] = htmlentities($row['nome']);
            $_SESSION['cognome'] = htmlentities($row['cognome']);
            $_SESSION['immagine_profilo'] = $row['immagine_profilo'];
            $_SESSION['monete'] = $row['monete'];
            $_SESSION['username'] = htmlentities($row['username']);
            //segno l'avvenuto login, serve per valutare le missioni in maniera corretta
            $_SESSION['login'] = true;
            //Ora che abbiamo fatto l'accesso dobbiamo controllare se non lo abbiamo ancora fatto oggi
            $_SESSION['ultimo_accesso'] = $row["ultimo_accesso"];
            if (strtotime($row["ultimo_accesso"]) != strtotime(date("d-m-Y"))) {
                //aggiorniamo dati utii per le missioni giornaliere e settimanali
                $query = "Update Utenti set ultimo_accesso = ?";
                if (date("W", strtotime($_SESSION["ultimo_accesso"])) != date("W")) {
                    $query .= ", completamento_giornaliere = 0";
                    resetMissioni($con, true);
                } else {
                    resetMissioni($con, false);
                }
                $query .= " where id_utente = ?";
                $stmt = mysqli_prepare($con, $query);
                if (!$stmt) {
                    error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
                    mysqli_close($con);
                    showForm("Errore nel server, riprovare più tardi");
                    die();
                }
                $date = date("Y-m-d");
                mysqli_stmt_bind_param($stmt, "si", $date, $row["id_utente"]);
                mysqli_stmt_execute($stmt);

                $rows = mysqli_affected_rows($con);
                if ($rows <= 0) {
                    error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
                    mysqli_stmt_close($stmt);
                    mysqli_close($con);
                    showForm("Errore nel server, riprovare più tardi");
                    die();
                }
                mysqli_stmt_close($stmt);
                mysqli_close($con);
            }
            header("Location: homepage.php");
        } else {
            showForm("Errore: credenziali non valide");
        }
    } else {
        showForm("Errore: credenziali non valide");
    }
    mysqli_close($con);


    ?>

</body>
</html>