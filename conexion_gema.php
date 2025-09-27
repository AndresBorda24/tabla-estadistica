<?php
function conectar_gema() {
    $ODBCdriver = "Driver={Microsoft Visual FoxPro Driver};SourceType=DBF;SourceDB=Z:\;
            Exclusive=No;Collate=Machine;NULL=NO;DELETED=YES;BACKGROUNDFETCH=NO;"; 
     $user = ""; 
     $pwd = ""; 
     $con = ""; 

    if( !($con = odbc_connect($ODBCdriver,$user,$pwd)) ){ 
        echo "Conexion fallida con la base de datos.<br>";
    }
    //echo "conexion" . $con;
    return $con;
}

conectar_gema();

?>
