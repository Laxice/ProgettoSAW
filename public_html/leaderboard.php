<!DOCTYPE html>
<html lang="it">
<head>
    <title>Classifica</title>
    <?php include "nav.php";?>
    <script src="//cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <link href="//cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" rel="stylesheet" crossorigin="anonymous">

</head>
<body>
<?php
    if(empty($_SESSION["id_utente"])){
        header("Location: login.php");
        die("<h1>Attenzione, non puoi accedere alla pagina se non sei loggato</h1>");
    }
    showNav();
    include "connection.php";
    $con = connection();
    $stmt = mysqli_prepare($con,"SELECT username, sum(livello * rarita) as somma_livelli, sum(affetto * rarita) as somma_affetto  from Utenti u join Personaggi_Collezionati pc on u.id_utente = pc.id_utente join Personaggi p on p.id_personaggio = pc.id_personaggio group by u.id_utente order by somma_livelli desc limit 100"); 
    if(!$stmt){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        mysqli_close($con);
        die("La prossima volta scrivi");
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if(!$res){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        mysqli_stmt_close($stmt);
        mysqli_close($con);
        die();
    }
    //while
    ?>
    <div class="container">
        <div class = "row">
            <div class="box col-md-11">
                <h1>I primi 100 giocatori</h1>
                <hr>
                <table id="classifica" class="display">
                    <thead>
                        <tr>
                            <th>Nome Utente</th>
                            <th>Somma livelli</th>
                            <th>Somma affetto</th>
                        </tr>
                    </thead>
                <?php
                while($row=mysqli_fetch_assoc($res)){
                    echo "<tr><th>".htmlentities($row["username"])."</th><th>".$row["somma_livelli"]."</th><th>".$row["somma_affetto"]."</th></tr>";
                }
                ?>
                </table>

                <script>
                    $(document).ready( function () {
                        $('#classifica').DataTable({order: [[1, 'desc']],});
                    } );
                </script>
            </div>
        </div>
    </div>
</body>
</html>