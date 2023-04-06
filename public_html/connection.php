<?php
    function connection(){
        $con = mysqli_connect('localhost','S4977546','Qiqisuperfan','S4977546');
        if (mysqli_connect_errno($con)) {
            error_log(__FILE__.": ".__LINE__." ".mysqli_connect_error($con). PHP_EOL,3,"../error.log");
            die("Errore, riprovare!");
        }
        return $con;
    }
?>