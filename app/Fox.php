<?php
declare(strict_types=1);

namespace App;

use PDO;

/**
 * Esta clase representa la conexion con las tablas de fox pro mediante PDO
*/
class Fox extends PDO
{
    /**
     * @param string $source Ruta en la cual se encuentran las tablas a consultar.
     * @param string $user Usuario de la base de datos
     * @param string $pass Password de la base de datos
    */
    public function __construct(
        public readonly string $source,
        private string $user = "",
        private string $pass = ""
    ){

        parent::__construct(
            $this->getDsn(),
            $this->user,
            $this->pass,
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }

    public function getDsn(): string
    {
        return "odbc:Driver={Microsoft Visual FoxPro Driver};".
        "SourceType=DBF;SourceDB=$this->source;Exclusive=No;".
        "Collate=Machine;NULL=NO;DELETED=YES;BACKGROUNDFETCH=NO";
    }
}
