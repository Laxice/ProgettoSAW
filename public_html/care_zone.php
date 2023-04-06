<!DOCTYPE html>
<html lang="it">
<head>
    <title>Zona di Cura</title>
    <?php include "nav.php";?>
</head>

<body>
<?php
    include "connection.php";
        function showCharacter($char){
            ?>
            <div class="container">
                <div class="col-md-9 box cardr<?php echo $char["rarita"];?>">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="d-inline"><?php echo $char["nome"] . '</div><div class="d-inline float-end">';
                            for($i=0;$i<$char["rarita"];$i++) echo "★";?></div><br>
                            <img src="images/chars/<?php echo $char["id_personaggio"]; ?>.png" alt="<?php echo $char["nome"]; ?>" width="100%">
                        </div>
                        <div class="col my-auto">
                        <?php
                            echo "<br>Descrizione:<br>".$char["descrizione"];
                            echo "<div id='lv'>Livello: ".$char["livello"].'
                            </div><div class="progress">
                            <div class="progress-bar" id="barraesp" role="progressbar" style="width: '.$char["esp"].'%" aria-valuenow='.$char["esp"].' aria-valuemin="0" aria-valuemax="100">'.$char["esp"].'%</div>
                            </div>';
                            echo "<div id='affetto'>Affetto: ".$char["affetto"].'
                            </div><div class="progress">
                            <div class="progress-bar" id="barraffetto" role="progressbar" style="width: '.$char["esp_affetto"].'%" aria-valuenow='.$char["esp_affetto"].' aria-valuemin="0" aria-valuemax="100">'.$char["esp_affetto"].'%</div>
                            </div><br>';?>
                            <div class="container">
                                <div class="col-md-6 d-inline">
                                    <button class="btn btn-primary" id="cuddle">Coccola</button>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal" class="btn btn-primary" id="food">Nutri</button>
                                    <a href="collection.php" class="btn btn-primary float-end">Torna alla collezione</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        function getCharacters($id){
            $con = connection();
            $stmt=mysqli_prepare($con,"Select p.id_personaggio,p.nome,p.descrizione,p.rarita,pc.livello,pc.affetto,pc.esp,pc.esp_affetto,u.coccole from Personaggi_Collezionati pc inner join Personaggi p on pc.id_personaggio = p.id_personaggio left outer join Utenti u on u.id_utente = pc.id_utente where pc.id_utente = ? and p.id_personaggio = ?");
            if ( !$stmt ) {                
                error_log(__FILE__.": ".__LINE__." ".mysqli_error($con). PHP_EOL,3,"../error.log");
                mysqli_close($con);
                die("Errore, riprovare!");
            }
            mysqli_stmt_bind_param($stmt,'ii',$_SESSION["id_utente"], $id);
            mysqli_stmt_execute($stmt);
            $res=mysqli_stmt_get_result($stmt);
            if ( !$res ) {
                error_log(__FILE__.": ".__LINE__." ".mysqli_stmt_error($stmt). PHP_EOL,3,"../error.log");
                mysqli_close($con);
                mysqli_stmt_close($stmt); 
                die("Errore, riprovare!");
            }
            $row=mysqli_fetch_assoc($res);
            if($row){
                mysqli_stmt_close($stmt); 
                mysqli_close($con);
                return($row);
            }
            else if(!$row)
                echo "Non hai quel personaggio!";
            else
                echo "Errore, riprovare più tardi!";
            mysqli_stmt_close($stmt); 
            mysqli_close($con);
            
        }

        if(empty($_SESSION["id_utente"])){
            echo "Non puoi vedere la collezione, non sei loggato!";
            header("Location: login.php");
        }else{
            if(!isset($_POST['carezone'])) {
                header("Location: collection.php");
                die();
            }
            showNav();
            showCharacter(getCharacters($_POST["carezone"]));
        }
    ?>
<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ModalLabel">Inventario Cibi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script>
$id = <?php echo json_encode($_POST["carezone"]); ?>;

$(document).ready(function(){
        $.get('food.php', function(data) {
                var list = $("<div id='list'></div>");
                list.append("<div class='row fw-bolder'><div class='col-md-2'></div><div class='col-md-5'>Nome</div> <div class='col-md-2'>Quantità</div><div class='col-md-3'></div>")
            $.each( JSON.parse(data), function( index, item ){  
                if(item.quantita != 0)
                    list.append("<div class='row' id='div"+item.id_cibo+"'><div class='col-md-2'><img src=images/foods/"+item.id_cibo+".png alt='Icona di Cibo' width='75px'></div><div class='col-md-5'>"+ item.nome + "</div> <div class='col-md-2'id='q"+item.id_cibo+"'>" + item.quantita+ "</div><div class='col-md-3'><button type='submit' class='btn btn-primary' name='feed"+item.id_cibo+"' id='feed' value="+item.id_cibo+">Dai da Mangiare</div>");
            });
        $('.modal-body').append(list);
    });

    $(document).on("click", "#cuddle", function(){
        $.post('care.php',{id_personaggio: $id},function(data){
                var res = JSON.parse(data);
                if(res.error!=null)
                    alert(res.error);
                else{
                    $('#barraesp').css("width", res.esp + "%").text(res.esp + "%");
                    $('#barraesp').val(res.esp);
                    $('#lv').text("Livello: "+res.livello);
                }
            });
        });
});

$(document).on("click", "#feed", function(){
    $.post('care.php',{id_cibo: $(this).val(),id_personaggio: $id},function(data){
            var res = JSON.parse(data);
            if(res.error!=null)
                alert(res.error);
            else{
                $('#barraffetto').css("width", res.esp_affetto + "%").text(res.esp_affetto + "%");
                $('#barraffetto').val(res.esp_affetto);
                $('#affetto').text("Affetto: "+res.affetto);
                if(res.quantita != 0) $('#q'+res.id).text(res.quantita);
                else $('#div'+res.id).remove();
            }
        });
});



</script>
</body>
</html>