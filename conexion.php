<?php
    function conectar() {
        $nombreServidor = "192.168.1.8";
        $nombreUsuario = "publico";    
        $password = "Asotrauma";
        $dataBase = "asotraum_calidad";
        
        $con = mysqli_connect($nombreServidor, $nombreUsuario, $password);
        if (!$con) {
            die("Conexion Fallida: " . mysqli_connect_error() . "<br>");
        } else {
            echo "";
        }
        
        mysqli_select_db($con, $dataBase);
        if (!$dataBase) {
            die ("Conexion fallida con la base de datos" . mysqli_connect_error() . "<br>");
        } else {
            echo "";
        }
        
        $_SESSION['server_id'] = $nombreServidor;
        
        return $con;
    }



?>