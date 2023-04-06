<!DOCTYPE html>
<html lang="it">
<head>
    <title>Mostra Profilo</title>
    <?php include "nav.php";?>
</head>
<body>
<?php
    function recuperaMissioni($con){
      $stmt = mysqli_prepare($con,"SELECT * from Missioni_Utente mu join Missioni m on m.id_missioni=mu.id_missione where mu.id_utente=? and m.categoria='generale' order by mu.data_completamento desc limit 5");    
      if(!$stmt){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        return false;
      }
      mysqli_stmt_bind_param($stmt,"i",$_SESSION["id_utente"]);
      mysqli_stmt_execute($stmt);
      $res=mysqli_stmt_get_result($stmt);
      if(!$res){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        return false;
      }
      return $res;
    }

    function stampaMissioni($res){
      while($row=mysqli_fetch_assoc($res)){
        echo "<div class='row'><div class='col-8'>".$row["descrizione"]."</div><div class='col-4'>".date("d-m-Y",strtotime($row["data_completamento"]))."</div></div>";
      }
    }

    function recuperaPersonaggi($con){
      $stmt = mysqli_prepare($con,"SELECT * from Personaggi_Collezionati pc join Personaggi p on p.id_personaggio=pc.id_personaggio where pc.id_utente=? order by pc.affetto desc limit 5");    
      if(!$stmt){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        return false;
      }
      mysqli_stmt_bind_param($stmt,"i",$_SESSION["id_utente"]);
      mysqli_stmt_execute($stmt);
      $res=mysqli_stmt_get_result($stmt);
      if(!$res){
        error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
        return false;
      }
      return $res;
    }

    function stampaPersonaggi($res){
      while($row=mysqli_fetch_assoc($res)){
        echo "<div class='row'><div class='col-8'>".$row["nome"]."</div><div class='col-4'>Affetto:".$row["affetto"]."</div></div>";
      }
    }

    if(empty($_SESSION["nome"])){
        echo "Non puoi vedere il tuo profilo, non sei loggato!";
        header("Location: homepage.php");
    }else{
      //bisogna recuperare un po' di dati dal db per popolare a dovere la pagina!!(ma teniamo separati logica dal rendering)
      include "connection.php";
      $con= connection();
      $missioni=recuperaMissioni($con);
      $personaggi=recuperaPersonaggi($con);
      showNav();
        ?>
<div class="container-fluid">
    <br>
    <div class="main-body">
    
          <div class="row gutters-sm">
            <div class="col-md-4 mb-3">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex flex-column align-items-center text-center">
                    <img src=<?php echo "images/icons/".$_SESSION["immagine_profilo"].".png"?> alt="immagine profilo" class="rounded-circle" width="150">
                    <div class="mt-3">
                      <h4><?php echo $_SESSION["nome"]." ".$_SESSION["cognome"]; ?></h4>
                      <p class="text-secondary mb-1"><?php echo $_SESSION["username"];?></p>
                      <p class="text-muted font-size-sm"><?php echo $_SESSION["email"];?></p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mt-3">
                
              </div>
            </div>
            <div class="col-md-8">
              <div class="card mb-3">
                <div class="card-body">
                  <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0">Nome e Cognome</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?php echo $_SESSION["nome"]." ".$_SESSION["cognome"]; ?>
                    </div>
                  </div>
                  <hr>
                  <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0">Email</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?php echo $_SESSION["email"];?>
                    </div>
                  </div>
                  <hr>
                  <div class="row">
                    <div class="col-sm-3">
                      <h6 class="mb-0">Monete</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?php echo $_SESSION["monete"];?>
                    </div>
                  </div>
                  <hr>
                  <div class="row">
                    <div class="col-sm-12">
                      <a href="update_profile.php" class="btn btn-primary">Modifica Profilo</a>
                      <a href="update_password.php" class="btn btn-primary">Modifica Password</a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row gutters-sm">
                <div class="col-sm-6 mb-3">
                  <div class="card h-100">
                    <div class="card-body">
                      <h6 class="d-flex align-items-center mb-3"><i class="material-icons text-info mr-2">Obbiettivi</i> Completati</h6>
                        <!--Farei una query sulle missioni generali, quindi dobbiamo aggiungere degli obbiettivi random-->
                        <?php 
                          if(!$missioni){
                            echo "Ci sono stati dei problemi con il recupero delle missioni";
                          }else{
                            stampaMissioni($missioni);
                          }
                        ?>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 mb-3">
                  <div class="card h-100">
                    <div class="card-body">
                      <h6 class="d-flex align-items-center mb-3"><i class="material-icons text-info mr-2">Preferiti</i></h6>
                      <!--Qui scriverei i nomi dei 5 personaggi con l'affetto più alto, e il valore di affetto dentro un cuore(?)-->
                        <?php 
                          if(!$personaggi){
                            echo "Ci sono stati dei problemi con il recupero dei personaggi";
                          }else{
                            stampaPersonaggi($personaggi);
                          }
                        ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
    </div>
        <?php
    }
	
?>

</body>
</html>