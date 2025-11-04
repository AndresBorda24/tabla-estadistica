<?php

declare(strict_types=1);

namespace App;

class Query
{
    public function __construct(
        public readonly Fox $db = new Fox("Z:\\")
    ) {}

    public static function convertEncoding(mixed $str): string
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


    public function getTriages(string $fecha): array
    {
        $triages = $this->db->query("SELECT
                T.Clas_tria, T.Freg, T.hora, T.Atendido, T.num_histo, T.turno_id, T.docn,
                P.edad, P.apellido1, P.Apellido2, P.nombre, P.nombre2, P.sexo
            FROM GEMA_MEDICOS\DATOS\TRIAGEV2.dbf AS T
            LEFT JOIN gema10.d\salud\DATOS\sahistoc AS P
                ON T.num_histo = P.num_histo
            WHERE
                T.freg = CTOD('$fecha')
                AND T.Atendido != 'C'
        ");

        return array_map(function ($t) {
            $nombreArray = array_map(fn($x) => static::convertEncoding($x), [
                $t['apellido1'],
                $t['apellido2'],
                $t['nombre'],
                $t['nombre2']
            ]);
            $nombre = implode(" ", array_filter($nombreArray));

            return [
                "clase_triage" => $t['clas_tria'],
                "fecha" => $t['freg'],
                "hora"  => $t['hora'],
                "atendido"  => $t['atendido'],
                "documento" => $t['num_histo'],
                "edad"   => $t['edad'],
                "genero" => $t['sexo'],
                "nombre" => $nombre,
                "turno_id" => (int) $t['turno_id'],
                "docn" => (int) $t['docn']
            ];
        }, $triages->fetchAll());
    }

    public function getDocn(string $fecha, string|int $documento): ?int
    {
        $x = $this->db->query(
            "SELECT docn, clasepro FROM gema10.d\IPT\DATOS\PTOTC00
            WHERE
                (fecha BETWEEN CTOD('$fecha') - 1 AND CTOD('$fecha') + 1)
                AND tercero2 = $documento
            ORDER BY fecha DESC, hora DESC"
        );

        if(!$x) return null;

        $docns = $x->fetchAll(\PDO::FETCH_ASSOC);
        if (count($docns) === 0) return null;

        // Por defecto tomamos la última admisión
        $docn  = (int) $docns[0]['docn'];
        foreach ($docns as $admision) {
            if ((int) $admision['clasepro'] === 3) {
                $docn = (int) $admision['docn'];
                break;
            }
        }

        return ($docn === 0) ? null : $docn;
    }

    public function getFechaHoraAdmision(int $docn): array
    {
        $x = $this->db->query(
            "SELECT fecha, hora FROM gema10.d\IPT\DATOS\PTOTC00
            WHERE docn = $docn"
        );

        return $x->fetch();
    }

    public function getFechaHoraHurge(int $docn): ?array
    {
        $x = $this->db->query(
            "SELECT
                fech_iatu AS fecha, hora_iatu AS hora,
                fecha_egr AS fecha_egreso, hora_egr as hora_egreso,
                dest_sali AS destino
            FROM GEMA_MEDICOS\DATOS\RE_HURGE
            WHERE docn = $docn"
        );

        $data = $x->fetch();
        return $data ?: null;
    }

    public function getFechaHoraEgreso(int $docn): ?array
    {
        $x = $this->db->query(
            "SELECT fechae AS fecha, horae as hora
            FROM gema10.d\SALUD\DATOS\EGRESOS
            WHERE docn = $docn"
        );

        $data = $x->fetch();
        return $data ?: null;
    }

    public function getMedico(int $docn): ?array
    {
        $x = $this->db->query(
            "SELECT H.codigo, V.nombre
            FROM GEMA_MEDICOS\DATOS\RE_HURGE AS H
            LEFT JOIN gema10.d\DGEN\DATOS\VENDEDOR AS V
                ON V.vendedor = H.codigo
            WHERE docn = $docn"
        );

        if (!$x = $x->fetch()) return null;

        return [
            'codigo' => $x['codigo'],
            'nombre' => self::convertEncoding($x['nombre'])
        ];
    }

    public function getAdmisionesUrgencias(string $fecha): array
    {
        $x = $this->db->query(
            "SELECT docn, tercero2 AS documento
            FROM gema10.d\IPT\DATOS\PTOTC00
            WHERE
                fecha = CTOD('$fecha')
                AND clasepro = '3'"
        );

        if (!$x || !$admisiones = $x->fetchAll()){
            throw new \Exception(
                "Error al buscar admisiones: ".$this->db->errorCode()
            );
        }

        return $admisiones;
    }

    public function getInfoPaciente(string $documento): ?array
    {
        $x = $this->db->query("SELECT
                P.edad, P.apellido1, P.Apellido2, P.nombre, P.nombre2, P.sexo,
                P.num_histo
            FROM gema10.d\salud\DATOS\sahistoc AS P
            WHERE num_histo = $documento
        ");
        if (!$x ||!$t = $x->fetch()) return null;

        $nombreArray = array_map(fn($x) => static::convertEncoding($x), [
            $t['apellido1'],
            $t['apellido2'],
            $t['nombre'],
            $t['nombre2']
        ]);
        $nombre = implode(" ", array_filter($nombreArray));

        return [
            "documento" => $t['num_histo'],
            "edad"   => $t['edad'],
            "genero" => $t['sexo'],
            "nombre" => $nombre
        ];
    }
}
