<?php

error_reporting(E_ERROR);

require_once __DIR__ . "/conexion_gema.php";
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/DatosHurge.php";

date_default_timezone_set("America/Bogota");

$fech = @$_POST['fe'] ?: @$_GET['fe'] ?: date('Y-m-d');
$con  = conectar_gema();
$con2 = conectar();
$anio = '';
$mes  = '';
$dia  = '';
$horaAct = date('H:i');

list($anio, $mes, $dia) = explode('-', $fech);

$fechaC = $mes . "-" . $dia . "-" . $anio;
$fechaR = date($dia . "-" . $mes . "-" . $anio);
$fechaActual = new DateTime($anio . "-" . $mes . "-" . $dia . " " . $horaAct);

$select = odbc_exec($con, "SELECT
        TRIAGEV2.Clas_tria, TRIAGEV2.Freg, TRIAGEV2.hora, TRIAGEV2.Atendido,
        TRIAGEV2.num_histo, sahistoc.edad, sahistoc.apellido1, sahistoc.Apellido2,
        sahistoc.nombre, sahistoc.nombre2, sahistoc.sexo
    FROM GEMA_MEDICOS\DATOS\TRIAGEV2.dbf
    LEFT JOIN gema10.d\salud\DATOS\sahistoc
        ON TRIAGEV2.num_histo = sahistoc.num_histo
    WHERE
        TRIAGEV2.freg = CTOD('$fechaC')
        AND TRIAGEV2.Atendido != 'C'
");

$row = array();
$admisiones = [];
$resultado = array();
$resultado2 = array();
$resultado3 = array();
$resultado4 = array();
$cant = array();
$p1 = 0;
$p2 = 1;
$superaTiempo = 0;
$chombres = 0;
$cmujeres = 0;
$ctriage = 0;
$churge = 0;

$sumaMinutos = 0;
$sumaMinutos5 = 0;
$sumaMinutos4 = 0;
$sumaMinutos3 = 0;
$sumaMinutos2 = 0;
$sumaMinutos1 = 0;

$sumaMinutos5TA = 0;
$sumaMinutos4TA = 0;
$sumaMinutos3TA = 0;
$sumaMinutos2TA = 0;
$sumaMinutos1TA = 0;

$t5 = 0;
$t4 = 0;
$t3 = 0;
$t2 = 0;
$t1 = 0;
$critico = "";
$nadvertencia = 0;
$sadm = 0;

/** Clase para realizar las consultas de Urgencias*/
$datosHurge = new DatosHurge();
while (odbc_fetch_row($select)) {
    //atributos de la tabla ptotc00
    $sexo = odbc_result($select, 'sexo');
    $fregt = trim(odbc_result($select, 'Freg'));
    $horat = trim(odbc_result($select, 'hora'));
    $clasetria = odbc_result($select, 'Clas_tria');
    $fechaHoraTria = new DateTime($fregt . " " . $horat);
    $edad = odbc_result($select, 'edad');
    $numhisto  = odbc_result($select, 'num_histo');
    $atendido  = odbc_result($select, 'Atendido');
    $apellido1 = convertEncoding(odbc_result($select, 'apellido1'));
    $apellido2 = convertEncoding(odbc_result($select, 'apellido2'));
    $nombrePaciente  = convertEncoding(odbc_result($select, 'nombre'));
    $nombre2Paciente = convertEncoding(odbc_result($select, 'nombre2'));
    $nombre = "$nombrePaciente $nombre2Paciente $apellido1 $apellido2";

    $selectPtotc = odbc_exec($con, "SELECT
            Docn, fecha as Freg, Hora
        FROM gema10.d\IPT\DATOS\PTOTC00
        WHERE
            tercero2 = $numhisto
            AND fecha <= CTOD('$fechaC') + 1
        ORDER BY fecha DESC, hora DESC
    ");
    $docn = empty(odbc_result($selectPtotc, 'Docn')) ? "" : odbc_result($selectPtotc, 'Docn');
    if ($docn) {
        $admisiones[] = $docn;
    }
    empty($docn) ? $sadm++ : $sadm;

    /** @var string $fecha Fecha de la Admision */
    $fecha = odbc_result($selectPtotc, 'Freg');
    /** @var string $hora Hora de la Admision */
    $hora = odbc_result($selectPtotc, 'Hora');
    $fechaHoraAdm = new DateTime($fecha." ".$hora);
    $diferencias  = $fechaHoraAdm->diff($fechaHoraTria);
    $cronometro   = $diferencias->format('%H h %i m');
    $cro = $cronometro;

    $horasd  = $diferencias->format('%H:%i');
    $horasd2 = explode(":", $horasd);
    $minutosT2 = ($horasd2[0] * 60) + $horasd2[1];
    $sumaMinutos += $minutosT2;

    // Hoja de Urgencias -----> <------
    $queryHurge = "SELECT fech_iatu, codigo, hora_iatu, fecha_egr, hora_egr, dest_sali
        FROM GEMA_MEDICOS\DATOS\RE_HURGE
        WHERE num_id = $numhisto AND freg >= CTOD('$fechaC')-1
        ORDER BY freg desc, hora desc
    ";
    $select3    = odbc_exec($con, $queryHurge);
    $medico     = empty(odbc_result($select3, 'Codigo')) ? "" : odbc_result($select3, 'Codigo');
    $fechaUrg   = odbc_result($select3, 'fech_iatu');
    $horaUrg    = odbc_result($select3, 'hora_iatu');
    $destEgrUrg = odbc_result($select3, 'dest_sali');
    $fechEgrUrg = ($destEgrUrg !== "0") ? odbc_result($select3, 'fecha_egr') : '';
    $horaEgrUrg = ($destEgrUrg !== "0") ? trim(odbc_result($select3, 'hora_egr')) : '';

    /**
     * Revisamos la informacion de los egresos.
     *
     * @var string $fechaEgreso Egreso de admision
     * @var string $horaEgreso Egreso de admision
     * @var ?\DateInterval $diffUrgEgreso Diferencia entre el egreso de urgencias y el de admision
     * @var ?\DateInterval $diffAdmEgreso Diferencia entre el egreso de urgencias y el de admision
     * @var ?array $infoUrgencias Información sobre la atención del paciente en urgencias
    */
    [$fechaEgreso, $horaEgreso, $diffUrgEgreso, $diffAdmEgreso, $infoUrgencias]= ['','',null, null, null];
    // Si ya tiene egreso de Urgencias
    if ($fechaUrg && !empty($docn)) {
        $infoUrgencias = $datosHurge->getAll($docn, $numhisto);
        $ingresoUrgencias = new DateTime($fechaUrg.' '.$horaUrg);
        $egresoUrgencias  = ($destEgrUrg !== "0")
            ? new DateTime($fechEgrUrg.' '.($horaEgrUrg ?: $horaUrg))
            : new DateTime() ;
        $diffUrgEgreso    = $egresoUrgencias->diff($ingresoUrgencias);

        // 1 es porque el paciente se puede ir para su hogar
        if ($destEgrUrg === "1" and !empty($docn)) {
            $select5 = odbc_exec($con, "SELECT fechae, horae
                FROM gema10.d\SALUD\DATOS\EGRESOS
                WHERE docn = $docn
            ");
            $fechaEgreso = odbc_result($select5, 'fechae') ?: '';
            $horaEgreso  = odbc_result($select5, 'horae')  ?: '';

            // Calculamos la diferencia con el egreso de urgencias
            $egresoAdmision = ($fechaEgreso === '')
                ? new DateTime()
                : new DateTime("$fechaEgreso $horaEgreso");

            $diffAdmEgreso = $egresoAdmision->diff($egresoUrgencias);
        }
    }

    $select5 = odbc_exec($con,
        "SELECT nombre
        FROM gema10.d\DGEN\DATOS\VENDEDOR
        WHERE Vendedor = '$medico'"
    );
    $nombreMedico = convertEncoding(odbc_result($select5, 'Nombre'));
    $fechaHoraUrg = new DateTime($fechaUrg . " " . $horaUrg);

    if ($fecha == "" || $fecha == null) {
        $diferencias2 = $fechaHoraUrg->diff($fechaHoraTria);
        $cronometro2 = "";
        $hraclr = $diferencias2->format('%H:%i');
        $cro2 = $cronometro2;
    } else {
        $diferencias2 = $fechaHoraUrg->diff($fechaHoraAdm);
        $diferenciasHra = $fechaHoraUrg->diff($fechaHoraAdm);
        $cronometro2 = $diferencias2->format('%H h %i m');
        $hraclr =  $diferenciasHra->format('%H:%i');
        $cro2 = $cronometro2;
    }

    $hraclr2 = explode(":", $hraclr);
    $minutosT = ($hraclr2[$p1] * 60) + $hraclr2[$p2];
    intval($minutosT);

    if ($nro_adm > 0) {
        $dato = mysqli_fetch_array($consAdm);
        $adm_hora = $dato["adm_hora"];

        $adm_hora = HoraAMinuto($adm_hora);
        $resta = $minutosT2 - $adm_hora;
        if ($resta >= 20 && $atendido != "C") {
            $marca = "S";

            array_push($resultado, array('docn' => $docn, 'fecha' => $fecha . ' Hr ' . $hora, 'nombreced' => $nombre . '-' . $numhisto, 'cronometro' => $cronometro, 'cro' => $cro));
            $superaTiempo = '<html><img src="Image/advertencia.png"></html>';
            $cronometro = '<html><strong><label style="color:red">' . $cronometro . '</label></strong></html>';
            $cronometro2 = '<html><strong><label style="color:red">' . $cronometro2 . '</label></strong></html>';
        } else {
            $superaTiempo = "";
        }
    }
    if ($minutosT2 >= 240 && $fecha == "" && $atendido != "C") {
        $marca = "S";
        array_push($resultado, array('docn' => $docn, 'fecha' => $fecha . ' Hr ' . $hora, 'nombreced' => $nombre . '-' . $numhisto, 'cronometro' => $cronometro, 'cro' => $cro));
        $superaTiempo = '<html><img src="Image/advertencia.png"></html>';
        $cronometro = '<html><strong><label style="color:red">' . $cronometro . '</label></strong></html>';
        $cronometro2 = '<html><strong><label style="color:red">' . $cronometro2 . '</label></strong></html>';
        if ($nro_adm > 0) {
        } else {
            //$insert = "INSERT INTO estadisticas(adm_numero, adm_hora)VALUES('$docn', '$horasd')";
            //mysqli_query($con2, $insert);
        }
    } else {
        $superaTiempo = "";
    }

    if ($sexo == "F") {
        if ($edad > 14) {
            $indicador = '<html><img src="Image/mujer.png"></html>';
        }else{
            $indicador = '<html><img src="Image/nina.png"></html>';
        }
    } elseif ($sexo == "M") {
        if ($edad > 14) {
            $indicador = '<html><img src="Image/hombre.png"></html>';
        }else{
            $indicador = '<html><img src="Image/nino.png"></html>';
        }
    }

    if ($clasetria == "5" && $atendido != "C") {
        $sumaMinutos5 += $minutosT;
        $sumaMinutos5TA += $minutosT2;
        ++$t5;
    }
    if ($clasetria == "4" && $atendido != "C") {
        $sumaMinutos4 += $minutosT;
        $sumaMinutos4TA += $minutosT2;
        ++$t4;
    }
    if ($clasetria == "3" && $atendido != "C") {
        $sumaMinutos3 += $minutosT;
        $sumaMinutos3TA += $minutosT2;
        ++$t3;
    }
    if ($clasetria == "2" && $atendido != "C") {
        $sumaMinutos2 += $minutosT;
        $sumaMinutos2TA += $minutosT2;
        ++$t2;
    }
    if ($clasetria == "1" && $atendido != "C") {
        $sumaMinutos1 += $minutosT;
        $sumaMinutos1TA += $minutosT2;
        ++$t1;
    }

    if ($fregt != "") {
        if ($fechaUrg == "") {
            if (($clasetria == "4" || $clasetria == "5") && $minutosT >= 240 && $atendido != "C") {
                $superaTiempo = '<html><img src="Image/advertencia.png"></html>';
                $cronometro2 = '<html><strong><label style="color:red">' . $cronometro2 . '</label></strong></html>';
                $critico = "";

                array_push($resultado2, array('docn' => $docn, 'fecha' => $fecha . ' Hr ' . $hora, 'nombreced' => $nombre . '-' . $numhisto, 'cronometro' => $cronometro2, 'clasetri' => $clasetria, 'cro' => $cro2));
                $marca = "S";
            } elseif ($minutosT >= 120 && $clasetria == "3" && $atendido != "C") {
                $superaTiempo = '<html><img src="Image/advertencia.png"></html>';
                $cronometro2 = '<html><strong><label style="color:red">' . $cronometro2 . '</label></strong></html>';
                $critico = "";
                array_push($resultado3, array('docn' => $docn, 'fecha' => $fecha . ' Hr ' . $hora, 'nombreced' => $nombre . '-' . $numhisto, 'cronometro' => $cronometro2, 'clasetri' => $clasetria, 'cro' => $cro2));
                $marca = "S";
            } elseif ($minutosT >= 25 && $clasetria == "2" || $clasetria == "1" && $atendido != "C") {
                $superaTiempo = '<html><img src="Image/advertencia.png"></html>';
                $critico = "S";
                $cronometro = '<html><strong><label style="color:red">' . $cronometro . '</label></strong></html>';
                array_push($resultado4, array('docn' => $docn, 'fecha' => $fecha . ' Hr ' . $hora, 'nombreced' => $nombre . '-' . $numhisto, 'cronometro' => $cronometro2, 'clasetri' => $clasetria, 'cro' => $cro2));
                $marca = "S";
            } else {
                $superaTiempo = "";
                $critico = "";
            }
        }
    }

    if ($fregt == "" || $fregt == null) {
        ++$ctriage;
    }
    if ($fechaUrg == "" || $fechaUrg == null) {
        ++$churge;
    }
    if ($superaTiempo != "" && $atendido != "C") {
        ++$nadvertencia;
    }
    if ($sexo == "M" && $atendido != "C") {
        ++$chombres;
    }
    if ($sexo == "F" && $atendido != "C") {
        ++$cmujeres;
    }

    if ($atendido != "C") {
        //echo $docn." ".$fecha." ".$hora." ".$nombre."  ".$numhisto." EDAD:".$edad." <br>".$cronometro."<br>";
        array_push(
            $row,
            array(
                'docn' => $docn,
                'sadm' => $sadm,
                'fecha' =>  $fecha ? $fecha . ' Hr ' . $hora : '',
                'nombreced' => $nombre . '-' . $numhisto . ' Edad:' . $edad,
                'ttriage' => $clasetria,
                'fhtri' => $fregt ? $fregt . ' Hr ' . $horat : '',
                'medico' => $medico,
                'nombreMedico' => $nombreMedico,
                'fechaUrg' => $fechaUrg ? $fechaUrg . ' Hr ' . $horaUrg : '',
                'horaUrg' => $horaUrg,
                'diferenciaAdm' => $cronometro,
                'diferenciaUrg' => $cronometro2,
                'diferenciaEgrUrg' => $diffUrgEgreso?->format('%H h %i m') ?? '',
                'diferenciaEgrAdm' => $diffAdmEgreso?->format('%H h %i m') ?? '',
                'diferenciaEgrUrgMin' => getMinutesFromInterval($diffUrgEgreso),
                'diferenciaEgrAdmMin' => getMinutesFromInterval($diffAdmEgreso),
                'superaTiempo' => $indicador . ' ' . $superaTiempo,
                'critico' => $critico,
                'hrclr' => $minutosT,
                'indic' => $indicador,
                'adv' => $nadvertencia,
                'egresoFecha' => $fechaEgreso,
                'egresoHora' => $horaEgreso,
                'egresoUrgFecha' => $fechEgrUrg,
                'egresoUrgHora'  => $horaEgrUrg,
                'egresoUrgDest'  => $destEgrUrg,
                'infoUrgencias'  => $infoUrgencias,
                'chombre' => $chombres,
                'cmujeres' => $cmujeres,
                'churge' => $churge,
                'ctria' => $ctriage,
                'fechaA' => $fechaR,
                'atendido' => $atendido,
                'minutosT' => $sumaMinutos,
                'mt5' => $sumaMinutos5,
                'mt4' => $sumaMinutos4,
                'mt3' => $sumaMinutos3,
                'mt2' => $sumaMinutos2,
                'mt1' => $sumaMinutos1,
                't5' => $t5,
                't4' => $t4,
                't3' => $t3,
                't2' => $t2,
                't1' => $t1,
                'mta5' => $sumaMinutos5TA,
                'mta4' => $sumaMinutos4TA,
                'mta3' => $sumaMinutos3TA,
                'mta2' => $sumaMinutos2TA,
                'mta1' => $sumaMinutos1TA,
                'marca' => $marca,
            )
        );
    }


    $minutosT = "";
    $superaTiempo = '';
    $cronometro2 = '';
    $cronometro = '';
    $critico = "";
    $indicador = "";
    $marca = "";
}

$consultaAdmisiones = odbc_exec($con, "SELECT docn, tercero2, fecha, hora
    FROM gema10.d\IPT\DATOS\PTOTC00
    WHERE
        fecha = CTOD('$fechaC')
        AND clasepro = '3'"
);
$admisiones2 = [];
while ($axx = odbc_fetch_array($consultaAdmisiones)) {
    $__docn = $axx['docn'];
    if (! in_array($__docn, $admisiones)) {
        $admisiones2[] = [
            "docn" => $__docn,
            "num_histo" => $axx['tercero2'],
            "freg" => $axx['fecha'],
            "hora" => $axx['hora']
        ];
    }
}

// Lo que va a pasar a continuación es una  medida desesperada
foreach ($admisiones2 as $admx){
    $select = odbc_exec($con, "SELECT
        edad, apellido1, Apellido2, nombre, nombre2, sexo, num_histo
    FROM gema10.d\salud\DATOS\sahistoc
    WHERE num_histo = $admx[num_histo]
    ");

    $sexo = odbc_result($select, 'sexo');
    // $fregt = date('Y-m-d');
    // $horat = date('H:i');
    $fregt = '';
    $horat = '';
    $clasetria = '';
    $fechaHoraTria = new DateTime();
    $edad = odbc_result($select, 'edad');
    $numhisto  = odbc_result($select, 'num_histo');
    $atendido  = '';
    $apellido1 = convertEncoding(odbc_result($select, 'apellido1'));
    $apellido2 = convertEncoding(odbc_result($select, 'apellido2'));
    $nombrePaciente  = convertEncoding(odbc_result($select, 'nombre'));
    $nombre2Paciente = convertEncoding(odbc_result($select, 'nombre2'));
    $nombre = "$nombrePaciente $nombre2Paciente $apellido1 $apellido2";
    $docn = $admx['docn'];

    /** @var string $fecha Fecha de la Admision */
    $fecha = $admx['freg'];
    /** @var string $hora Hora de la Admision */
    $hora = $admx['hora'];
    $fechaHoraAdm = new DateTime($fecha." ".$hora);
    $diferencias  = $fechaHoraTria->diff($fechaHoraAdm);
    $cronometro   = $diferencias->format('%H h %i m');
    $cro = $cronometro;

    $horasd  = $diferencias->format('%H:%i');
    $horasd2 = explode(":", $horasd);
    $minutosT2 = ($horasd2[0] * 60) + $horasd2[1];
    $sumaMinutos += $minutosT2;

    // Hoja de Urgencias -----> <------
    $queryHurge = "SELECT fech_iatu, codigo, hora_iatu, fecha_egr, hora_egr, dest_sali
        FROM GEMA_MEDICOS\DATOS\RE_HURGE
        WHERE docn = $docn
        ORDER BY freg desc, hora desc
    ";
    $select3    = odbc_exec($con, $queryHurge);
    $medico     = empty(odbc_result($select3, 'Codigo')) ? "" : odbc_result($select3, 'Codigo');
    $fechaUrg   = odbc_result($select3, 'fech_iatu');
    $horaUrg    = odbc_result($select3, 'hora_iatu');
    $destEgrUrg = odbc_result($select3, 'dest_sali');
    $fechEgrUrg = ($destEgrUrg !== "0") ? odbc_result($select3, 'fecha_egr') : '';
    $horaEgrUrg = ($destEgrUrg !== "0") ? trim(odbc_result($select3, 'hora_egr')) : '';

    /**
     * Revisamos la informacion de los egresos.
     *
     * @var string $fechaEgreso Egreso de admision
     * @var string $horaEgreso Egreso de admision
     * @var ?\DateInterval $diffUrgEgreso Diferencia entre el egreso de urgencias y el de admision
     * @var ?\DateInterval $diffAdmEgreso Diferencia entre el egreso de urgencias y el de admision
     * @var ?array $infoUrgencias Información sobre la atención del paciente en urgencias
    */
    [$fechaEgreso, $horaEgreso, $diffUrgEgreso, $diffAdmEgreso, $infoUrgencias]= ['','',null, null, null];
    // Si ya tiene egreso de Urgencias
    if ($fechaUrg && !empty($docn)) {
        $infoUrgencias = $datosHurge->getAll($docn, $numhisto);
        $ingresoUrgencias = new DateTime($fechaUrg.' '.$horaUrg);
        $egresoUrgencias  = ($destEgrUrg !== "0")
            ? new DateTime($fechEgrUrg.' '.($horaEgrUrg ?: $horaUrg))
            : new DateTime() ;
        $diffUrgEgreso    = $egresoUrgencias->diff($ingresoUrgencias);

        // 1 es porque el paciente se puede ir para su hogar
        if ($destEgrUrg === "1" and !empty($docn)) {
            $select5 = odbc_exec($con, "SELECT fechae, horae
                FROM gema10.d\SALUD\DATOS\EGRESOS
                WHERE docn = $docn
            ");
            $fechaEgreso = odbc_result($select5, 'fechae') ?: '';
            $horaEgreso  = odbc_result($select5, 'horae')  ?: '';

            // Calculamos la diferencia con el egreso de urgencias
            $egresoAdmision = ($fechaEgreso === '')
                ? new DateTime()
                : new DateTime("$fechaEgreso $horaEgreso");

            $diffAdmEgreso = $egresoAdmision->diff($egresoUrgencias);
        }
    }

    $select5 = odbc_exec($con,
        "SELECT nombre
        FROM gema10.d\DGEN\DATOS\VENDEDOR
        WHERE Vendedor = '$medico'"
    );
    $nombreMedico = convertEncoding(odbc_result($select5, 'Nombre'));
    $fechaHoraUrg = new DateTime($fechaUrg . " " . $horaUrg);

    if ($fecha == "" || $fecha == null) {
        $diferencias2 = $fechaHoraUrg->diff($fechaHoraTria);
        $cronometro2 = "";
        $hraclr = $diferencias2->format('%H:%i');
        $cro2 = $cronometro2;
    } else {
        $diferencias2 = $fechaHoraUrg->diff($fechaHoraAdm);
        $diferenciasHra = $fechaHoraUrg->diff($fechaHoraAdm);
        $cronometro2 = $diferencias2->format('%H h %i m');
        $hraclr =  $diferenciasHra->format('%H:%i');
        $cro2 = $cronometro2;
    }

    $hraclr2 = explode(":", $hraclr);
    $minutosT = ($hraclr2[$p1] * 60) + $hraclr2[$p2];
    intval($minutosT);

    if ($nro_adm > 0) {
        $dato = mysqli_fetch_array($consAdm);
        $adm_hora = $dato["adm_hora"];

        $adm_hora = HoraAMinuto($adm_hora);
        $resta = $minutosT2 - $adm_hora;
        if ($resta >= 20 && $atendido != "C") {
            $marca = "S";

            array_push($resultado, array('docn' => $docn, 'fecha' => $fecha . ' Hr ' . $hora, 'nombreced' => $nombre . '-' . $numhisto, 'cronometro' => $cronometro, 'cro' => $cro));
            $superaTiempo = '<html><img src="Image/advertencia.png"></html>';
            $cronometro = '<html><strong><label style="color:red">' . $cronometro . '</label></strong></html>';
            $cronometro2 = '<html><strong><label style="color:red">' . $cronometro2 . '</label></strong></html>';
        } else {
            $superaTiempo = "";
        }
    }
    if ($minutosT2 >= 240 && $fecha == "" && $atendido != "C") {
        $marca = "S";
        array_push($resultado, array('docn' => $docn, 'fecha' => $fecha . ' Hr ' . $hora, 'nombreced' => $nombre . '-' . $numhisto, 'cronometro' => $cronometro, 'cro' => $cro));
        $superaTiempo = '<html><img src="Image/advertencia.png"></html>';
        $cronometro = '<html><strong><label style="color:red">' . $cronometro . '</label></strong></html>';
        $cronometro2 = '<html><strong><label style="color:red">' . $cronometro2 . '</label></strong></html>';
        if ($nro_adm > 0) {
        } else {
            //$insert = "INSERT INTO estadisticas(adm_numero, adm_hora)VALUES('$docn', '$horasd')";
            //mysqli_query($con2, $insert);
        }
    } else {
        $superaTiempo = "";
    }

    if ($sexo == "F") {
        if ($edad > 14) {
            $indicador = '<html><img src="Image/mujer.png"></html>';
        }else{
            $indicador = '<html><img src="Image/nina.png"></html>';
        }
    } elseif ($sexo == "M") {
        if ($edad > 14) {
            $indicador = '<html><img src="Image/hombre.png"></html>';
        }else{
            $indicador = '<html><img src="Image/nino.png"></html>';
        }
    }

    if ($clasetria == "5" && $atendido != "C") {
        $sumaMinutos5 += $minutosT;
        $sumaMinutos5TA += $minutosT2;
        ++$t5;
    }
    if ($clasetria == "4" && $atendido != "C") {
        $sumaMinutos4 += $minutosT;
        $sumaMinutos4TA += $minutosT2;
        ++$t4;
    }
    if ($clasetria == "3" && $atendido != "C") {
        $sumaMinutos3 += $minutosT;
        $sumaMinutos3TA += $minutosT2;
        ++$t3;
    }
    if ($clasetria == "2" && $atendido != "C") {
        $sumaMinutos2 += $minutosT;
        $sumaMinutos2TA += $minutosT2;
        ++$t2;
    }
    if ($clasetria == "1" && $atendido != "C") {
        $sumaMinutos1 += $minutosT;
        $sumaMinutos1TA += $minutosT2;
        ++$t1;
    }

    if ($fregt != "") {
        if ($fechaUrg == "") {
            if (($clasetria == "4" || $clasetria == "5") && $minutosT >= 240 && $atendido != "C") {
                $superaTiempo = '<html><img src="Image/advertencia.png"></html>';
                $cronometro2 = '<html><strong><label style="color:red">' . $cronometro2 . '</label></strong></html>';
                $critico = "";

                array_push($resultado2, array('docn' => $docn, 'fecha' => $fecha . ' Hr ' . $hora, 'nombreced' => $nombre . '-' . $numhisto, 'cronometro' => $cronometro2, 'clasetri' => $clasetria, 'cro' => $cro2));
                $marca = "S";
            } elseif ($minutosT >= 120 && $clasetria == "3" && $atendido != "C") {
                $superaTiempo = '<html><img src="Image/advertencia.png"></html>';
                $cronometro2 = '<html><strong><label style="color:red">' . $cronometro2 . '</label></strong></html>';
                $critico = "";
                array_push($resultado3, array('docn' => $docn, 'fecha' => $fecha . ' Hr ' . $hora, 'nombreced' => $nombre . '-' . $numhisto, 'cronometro' => $cronometro2, 'clasetri' => $clasetria, 'cro' => $cro2));
                $marca = "S";
            } elseif ($minutosT >= 25 && $clasetria == "2" || $clasetria == "1" && $atendido != "C") {
                $superaTiempo = '<html><img src="Image/advertencia.png"></html>';
                $critico = "S";
                $cronometro = '<html><strong><label style="color:red">' . $cronometro . '</label></strong></html>';
                array_push($resultado4, array('docn' => $docn, 'fecha' => $fecha . ' Hr ' . $hora, 'nombreced' => $nombre . '-' . $numhisto, 'cronometro' => $cronometro2, 'clasetri' => $clasetria, 'cro' => $cro2));
                $marca = "S";
            } else {
                $superaTiempo = "";
                $critico = "";
            }
        }
    }

    if ($fregt == "" || $fregt == null) {
        ++$ctriage;
    }
    if ($fechaUrg == "" || $fechaUrg == null) {
        ++$churge;
    }
    if ($superaTiempo != "" && $atendido != "C") {
        ++$nadvertencia;
    }
    if ($sexo == "M" && $atendido != "C") {
        ++$chombres;
    }
    if ($sexo == "F" && $atendido != "C") {
        ++$cmujeres;
    }

    if ($atendido != "C") {
        //echo $docn." ".$fecha." ".$hora." ".$nombre."  ".$numhisto." EDAD:".$edad." <br>".$cronometro."<br>";
        array_push(
            $row,
            array(
                'docn' => $docn,
                'sadm' => $sadm,
                'fecha' =>  $fecha ? $fecha . ' Hr ' . $hora : '',
                'nombreced' => $nombre . '-' . $numhisto . ' Edad:' . $edad,
                'ttriage' => $clasetria,
                // 'fhtri' => $fregt ? $fregt . ' Hr ' . $horat : '',
                'fhtri' => $fechaHoraTria->format("Y-m-d \H\\r H:i"),
                'medico' => $medico,
                'nombreMedico' => $nombreMedico,
                'fechaUrg' => $fechaUrg ? $fechaUrg . ' Hr ' . $horaUrg : '',
                'horaUrg' => $horaUrg,
                'diferenciaAdm' => $cronometro,
                'diferenciaUrg' => $cronometro2,
                'diferenciaEgrUrg' => $diffUrgEgreso?->format('%H h %i m') ?? '',
                'diferenciaEgrAdm' => $diffAdmEgreso?->format('%H h %i m') ?? '',
                'diferenciaEgrUrgMin' => getMinutesFromInterval($diffUrgEgreso),
                'diferenciaEgrAdmMin' => getMinutesFromInterval($diffAdmEgreso),
                'superaTiempo' => $indicador . ' ' . $superaTiempo,
                'critico' => $critico,
                'hrclr' => $minutosT,
                'indic' => $indicador,
                'adv' => $nadvertencia,
                'egresoFecha' => $fechaEgreso,
                'egresoHora' => $horaEgreso,
                'egresoUrgFecha' => $fechEgrUrg,
                'egresoUrgHora'  => $horaEgrUrg,
                'egresoUrgDest'  => $destEgrUrg,
                'infoUrgencias'  => $infoUrgencias,
                'chombre' => $chombres,
                'cmujeres' => $cmujeres,
                'churge' => $churge,
                'ctria' => $ctriage,
                'fechaA' => $fechaR,
                'atendido' => $atendido,
                'minutosT' => $sumaMinutos,
                'mt5' => $sumaMinutos5,
                'mt4' => $sumaMinutos4,
                'mt3' => $sumaMinutos3,
                'mt2' => $sumaMinutos2,
                'mt1' => $sumaMinutos1,
                't5' => $t5,
                't4' => $t4,
                't3' => $t3,
                't2' => $t2,
                't1' => $t1,
                'mta5' => $sumaMinutos5TA,
                'mta4' => $sumaMinutos4TA,
                'mta3' => $sumaMinutos3TA,
                'mta2' => $sumaMinutos2TA,
                'mta1' => $sumaMinutos1TA,
                'marca' => $marca,
            )
        );
    }


    $minutosT = "";
    $superaTiempo = '';
    $cronometro2 = '';
    $cronometro = '';
    $critico = "";
    $indicador = "";
    $marca = "";
}
//Aquí termina la medida desesperada

header("Content-Type: application/json");
echo json_encode($row);

function HoraAMinuto($h)
{
    $h2 = explode(":", $h);
    return ($h2[0] * 60) + $h2[1];
}

/** Obtiene la cantidad de minutos de un intervalo de tiempo */
function getMinutesFromInterval(?\DateInterval $interval = null): int {
    if ($interval === null) return 0;

    return ($interval->h * 60) + $interval->i;
}

function convertEncoding(mixed $str): string
{
    if (! $str) return '';
    if (gettype($str) !== "string") return '';
    if (! $str = trim($str)) return '';
    $prevEncode = mb_detect_encoding($str, [
        "CP1252", // <- encoding por defecto de Fox
        "UTF-8"
    ]);

    return mb_convert_encoding($str, "UTF-8", $prevEncode);
}


// function getInfo
