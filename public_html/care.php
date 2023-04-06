<?php
    session_start();
    include "connection.php";
    error_reporting(E_ERROR | E_PARSE);

    $con = connection();
    $list = new \stdClass();
    $exp = 10;

    if (empty($_SESSION["id_utente"])) {
        $list->error = "Sessione scaduta o non valida";
        die(json_encode($list));
    }
    if (empty($_POST["id_personaggio"])) {
        $list->error = "Richiesta malformata";
        die(json_encode($list));
    }

    if (isset($_POST['id_cibo']) && isset($_POST["id_personaggio"])) {
        $id_cibo = $_POST['id_cibo'];
        
        $stmt = mysqli_prepare($con, "SELECT affetto, esp_affetto, p.id_cibo, pasti, exp_affetto FROM Personaggi_Collezionati pc inner join Personaggi p on pc.id_personaggio=p.id_personaggio inner join Utenti u on pc.id_utente=u.id_utente inner join Cibi_Collezionati cc on cc.id_utente=u.id_utente inner join Cibi c on c.id_cibo=cc.id_cibo WHERE pc.id_utente = ? and pc.id_personaggio = ? and cc.id_cibo = ?");
        if (!$stmt) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
            mysqli_close($con);
            $list->error = "Richiesta malformata";
            die(json_encode($list));
        }
        mysqli_stmt_bind_param($stmt, "iii", $_SESSION["id_utente"], $_POST["id_personaggio"], $id_cibo);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if (!$res) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            $list->error = "Richiesta malformata";
            die(json_encode($list));
        }
        $arr = mysqli_fetch_assoc($res);

        mysqli_stmt_close($stmt);

        if ($arr["id_cibo"] == $id_cibo)
            $fav = 2;
        else
            $fav = 1;

        $arr["esp_affetto"] = $arr["esp_affetto"] + $arr["exp_affetto"] * $fav;
        if ($arr["esp_affetto"] >= 100) {
            $arr["affetto"] = floor($arr["affetto"] + $arr["esp_affetto"] / 100);
            $arr["esp_affetto"] = $arr["esp_affetto"] % 100;
        }
        $arr["pasti"] += 1;
        mysqli_begin_transaction($con);
        $stmt = mysqli_prepare($con, "UPDATE Personaggi_Collezionati pc INNER JOIN Utenti u ON pc.id_utente = u.id_utente SET affetto = ?, esp_affetto = ?, pasti = ? WHERE pc.id_utente = ? and pc.id_personaggio = ? ");
        if (!$stmt) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
            mysqli_rollback($con);
            mysqli_close($con);
            $list->error = "Richiesta malformata";
            die(json_encode($list));
        }
        mysqli_stmt_bind_param($stmt, "iiiii", $arr["affetto"], $arr["esp_affetto"], $arr["pasti"], $_SESSION["id_utente"], $_POST["id_personaggio"]);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_affected_rows($con);
        if ($rows == 2) {
            $_SESSION["pasto"] = true;
        } else {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_stmt_error($stmt) . PHP_EOL, 3, "../error.log");
            mysqli_rollback($con);
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            $list->error = "Richiesta malformata";
            die(json_encode($list));
        }

        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($con, "SELECT quantita FROM Cibi_Collezionati WHERE id_utente = ? and id_cibo = ?");
        if (!$stmt) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
            mysqli_rollback($con);
            mysqli_close($con);
            $list->error = "Richiesta malformata";
            die(json_encode($list));
        }
        mysqli_stmt_bind_param($stmt, "ii", $_SESSION["id_utente"], $id_cibo);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if (!$res) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_stmt_error($stmt) . PHP_EOL, 3, "../error.log");
            mysqli_rollback($con);
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            $list->error = "Richiesta malformata";
            die(json_encode($list));
        }
        $q = mysqli_fetch_assoc($res);

        mysqli_stmt_close($stmt);

        if ($q["quantita"] <= 0) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
            mysqli_rollback($con);
            mysqli_close($con);
            $list->error = "Quantità cibo non sufficiente";
            die(json_encode($list));
        }

        $stmt = mysqli_prepare($con, "UPDATE Cibi_Collezionati SET quantita = quantita - 1 WHERE id_utente = ? and id_cibo = ?");
        if (!$stmt) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
            mysqli_rollback($con);
            mysqli_close($con);
            $list->error = "Richiesta malformata";
            die(json_encode($list));
        }
        mysqli_stmt_bind_param($stmt, "ii", $_SESSION["id_utente"], $id_cibo);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_errno($stmt);
        if ($res != 0) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
            mysqli_rollback($con);
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            $list->error = "Richiesta malformata";
            die(json_encode($list));
        }

        mysqli_commit($con);
        mysqli_stmt_close($stmt);
        mysqli_close($con);
        $list->id = $id_cibo;
        $list->affetto = $arr["affetto"];
        $list->esp_affetto = $arr["esp_affetto"];
        $list->quantita = $q["quantita"] - 1;
        echo json_encode($list);
        die();
    }

    if (isset($_POST['id_personaggio']))
    {
        $stmt=mysqli_prepare($con,"Select p.id_personaggio,p.nome,p.descrizione,p.rarita,pc.livello,pc.affetto,pc.esp,pc.esp_affetto,u.coccole from Personaggi_Collezionati pc inner join Personaggi p on pc.id_personaggio = p.id_personaggio left outer join Utenti u on u.id_utente = pc.id_utente where pc.id_utente = ? and p.id_personaggio = ?");
        if ( !$stmt ) {                
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
            mysqli_close($con);
            $list->error = "Richiesta malformata";
            die(json_encode($list));
        }
        mysqli_stmt_bind_param($stmt,'ii',$_SESSION["id_utente"], $_POST['id_personaggio']);
        mysqli_stmt_execute($stmt);
        $res=mysqli_stmt_get_result($stmt);
        if ( !$res ) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_stmt_error($stmt) . PHP_EOL, 3, "../error.log");
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            $list->error = "Richiesta malformata";
            die(json_encode($list));
        }
        $char=mysqli_fetch_assoc($res);
        if($char)
            mysqli_stmt_close($stmt); 
        else if(!$char){       
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_stmt_error($stmt) . PHP_EOL, 3, "../error.log");
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            $list->error = "Non hai quel personaggio";
            die(json_encode($list));
        }

        $char["esp"]+=$exp;
        if(($char["esp"]) >= 100)
        {
            $char["livello"] = $char["livello"] + $char["esp"]/100;
            floor($char["livello"]);
            $char["esp"] = $char["esp"]%100;
        }
        $char["coccole"]+= 1;

        mysqli_begin_transaction($con);
        $stmt=mysqli_prepare($con,"UPDATE Personaggi_Collezionati pc INNER JOIN Utenti u ON pc.id_utente = u.id_utente SET pc.livello = ?, pc.esp = ?, u.coccole = ? WHERE pc.id_utente = ? AND pc.id_personaggio = ? ");
        if ( !$stmt ) {
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_error($con) . PHP_EOL, 3, "../error.log");
            mysqli_rollback($con);
            mysqli_close($con);
            $list->error = "Errore, riprovare";
            die(json_encode($list));
        }
        mysqli_stmt_bind_param($stmt,'iiiii',$char["livello"],$char["esp"],$char["coccole"], $_SESSION["id_utente"], $_POST['id_personaggio']);
        mysqli_stmt_execute($stmt);
        $rows=mysqli_affected_rows($con);

        if($rows==2){
            $_SESSION["coccola"]=true;
        }else{
            error_log(__FILE__ . ": " . __LINE__ . " " . mysqli_stmt_error($stmt) . PHP_EOL, 3, "../error.log");
            mysqli_rollback($con);
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            $list->error = "Errore, riprovare";
            die(json_encode($list));
        }
        
        mysqli_commit($con);
        mysqli_stmt_close($stmt); 
        mysqli_close($con);
        $list->livello = $char["livello"];
        $list->esp = $char["esp"];
        echo json_encode($list);
    }

?>