<?php

declare(strict_types=1);

namespace App;

final class DatosHurge
{
    public function __construct(
        public readonly Fox $fox
    ) { }

    public function getAll(string $docn, string $cc): array
    {
        $data = [
            'imagenes'  => $this->getImagenes($docn, $cc),
            'lab'       => $this->getExamenes($docn),
            'intercon'  => $this->getSolinter($docn),
            'evolucion' => $this->getEvoluciones($docn),
            'tratamiento' => $this->getTratamiento($docn)
        ];

        return $data;
    }

    public function getImagenes(string $docn, string $cc): array
    {
        $res = $this->fox->query(
            "SELECT D.fecha_sol, D.hora_sol, D.fecha_exa, D.hora_exa, D.punto_at as tipo, D.anotacion, X.descrip as nombre
            FROM GEMA10.d\SALUD\DATOS\IMG_MUC as C
            LEFT JOIN GEMA10.d\SALUD\DATOS\IMG_MUCD AS D
            ON C.numex = D.numex
            LEFT JOIN GEMA10.d\SALUD\DATOS\IMG_EXC AS X
            ON D.codigo = X.codigo
            WHERE C.num_histo = $cc and C.ord_ss_no = $docn"
        );

        if ($res === false) throw new \Exception(
            "No se pudo recuperar las Imagenes del paciente"
        );

        $returnData = [];
        $data = $res->fetchAll();
        foreach($data as $res) {
            $returnData[] = [
                'fechaSol'     => $res['fecha_sol'],
                'horaSol'      => $res['hora_sol'],
                'fechaHoraSol' => $res['fecha_sol'].' '.$res['hora_sol'],
                'fechaFin'     => $res['fecha_exa'],
                'horaFin'      => $res['hora_exa'],
                'fechaHoraFin' => $res['fecha_exa'] ?
                    $res['fecha_exa'].' '.$res['hora_exa']
                    : 'now',
                'ok' => ($res['anotacion'] === '01'),
                // Campos extra
                'tipo'   => $res['tipo'],
                'nombre' => self::trimUtf8($res['nombre']??'')
            ];
        }


        return $returnData;
    }

    public function getSolinter(string $docn): array
    {
        $res = $this->fox->query(
            "SELECT fecha_sol, hora_sol, fecha_int, hora_int, estado
            FROM GEMA_MEDICOS\DATOS\SOLINTER
            WHERE docn = $docn"
        );

        if ($res === false) throw new \Exception(
            "No se pudo recuperar las interconsultas del paciente"
        );

        $returnData = [];
        $data = $res->fetchAll();
        foreach($data as $res) {
            $returnData[] = [
                'fechaSol'     => $res['fecha_sol'],
                'horaSol'      => $res['hora_sol'],
                'fechaHoraSol' => $res['fecha_sol'].' '.$res['hora_sol'],
                'fechaFin'     => $res['fecha_int'],
                'horaFin'      => $res['hora_int'],
                'fechaHoraFin' => trim($res['fecha_int']) ?
                    $res['fecha_int'].' '.$res['hora_int']
                    : 'now',
                'ok' => ($res['estado'] === 'R')
            ];
        }


        return $returnData;
    }

    public function getExamenes(string $docn): array
    {
        $res = $this->fox->query(
            "SELECT freg as fecha_sol, hora as hora_sol, diag_ing
            FROM GEMA_MEDICOS\DATOS\SAHISFOR
            WHERE docn = $docn AND tipo = 'E'"
        );

        if ($res === false) throw new \Exception(
            "No se pudo recuperar las interconsultas del paciente"
        );

        $returnData = [];
        $data = $res->fetchAll();
        foreach($data as $res) {
            $returnData[] = [
                'fechaSol'     => $res['fecha_sol'],
                'horaSol'      => $res['hora_sol'],
                'fechaHoraSol' => $res['fecha_sol'].' '.$res['hora_sol'],
                'fechaFin'     => $res['fecha_sol'],
                'horaFin'      => $res['hora_sol'],
                'fechaHoraFin' => $res['fecha_sol'].' '.$res['hora_sol'],
                'ok'           => true,
                // Campos Extra
                'diag' => $res['diag_ing']
            ];
        }


        return $returnData;
    }

    public function getEvoluciones(string $docn): array
    {
        $res = $this->fox->query(
            "SELECT freg as fecha_sol, hora as hora_sol
            FROM GEMA_medicos\DATOS\RE_HURGEE
            WHERE docn = $docn"
        );

        if ($res === false) throw new \Exception(
            "No se pudo recuperar las interconsultas del paciente"
        );

        $returnData = [];
        $data = $res->fetchAll();
        foreach($data as $res) {
            $returnData[] = [
                'fechaSol'     => $res['fecha_sol'],
                'horaSol'      => $res['hora_sol'],
                'fechaHoraSol' => $res['fecha_sol'].' '.$res['hora_sol'],
                'fechaFin'     => $res['fecha_sol'],
                'horaFin'      => $res['hora_sol'],
                'fechaHoraFin' => $res['fecha_sol'].' '.$res['hora_sol'],
                'ok'           => true
            ];
        }


        return $returnData;
    }

    public function getTratamiento(string $docn): ?string
    {
        $x = $this->fox->query(
            "SELECT tratami
            FROM GEMA_MEDICOS\DATOS\RE_HURGE
            WHERE docn = $docn"
        );

        $data = $x->fetchColumn();
        return $data ?: null;
    }

    private function getLatest(array $input): array
    {
        function find(array $items) {
            $return = [...$items];
            usort($return, function($a, $b) {
                if ($a['ok']) return -1;
                if ($b['ok']) return 1;

                return strtotime($a['fechaHoraSol']) <=> strtotime($b['fechaHoraSol']);
            });
            return $return;
        };

        $flattedArray = [];
        foreach ($input as $key => $data) {
            @[$first] = find($data);
            if ($first) $flattedArray[] = $first;
        }
        dump($flattedArray);
        dd(find($flattedArray));
        return [];
    }

    /**
     * Convierte el texto en utf8 y quita los espacios en blanco. USAR
     * SOLAMENTE CON LAS CONSULTAS DE FOX
    */
    public static function trimUtf8(string $str): string
    {
        $prevEncode = mb_detect_encoding($str, [
            "CP1252", // <- encoding por defecto de Fox
            "UTF-8"
        ]);

        return trim(
            mb_convert_encoding($str, 'UTF-8', $prevEncode)
        );
    }

}
