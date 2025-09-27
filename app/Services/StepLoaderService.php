<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Steps\Admision;
use App\Entity\Steps\EgresoAdmision;
use App\Entity\Steps\EgresoUrge;
use App\Entity\Steps\Hurge;
use App\Query;

class StepLoaderService
{
    public function __construct(
        public readonly Query $q
    ) {}

    /** @return array{0:Admision,1:Hurge,2:EgresoUrge,3:EgresoAdmision} */
    public function load(?int $docn): array
    {
        $egreso = $this->loadEgreso($docn);
        $admision = $this->loadAdmision($docn);
        [$hurge, $egresoUrge] = $this->loadHurgeWithEgreso($docn);
        //-----
        $admision->setNextDate($hurge->strTime);
        $egresoUrge->setNextDate($egreso->strTime);

        return [
            $admision,
            $hurge,
            $egresoUrge,
            $egreso
        ];
    }

    private function loadAdmision(?int $docn): Admision
    {
        $x = (!$docn) ? null : $this->q->getFechaHoraAdmision($docn);
        $fechaHora = ($x) ? "$x[fecha] $x[hora]" : null;

        return new Admision($fechaHora);
    }

    /** @return array{0:Hurge,1:EgresoUrge} */
    private function loadHurgeWithEgreso(?int $docn): array
    {
        $x = (!$docn) ? null : $this->q->getFechaHoraHurge($docn);
        if (!$docn || !$x) {
            return [
                new Hurge(null),
                new EgresoUrge("0", null)
            ];
        }

        $fechaEgreso = $x['fecha_egreso'];
        $horaEgreso  = $x['hora_egreso'];
        //-------
        $fechaHora   = "$x[fecha] $x[hora]";
        $fechaHoraEgreso = (empty($fechaEgreso)) ? "now" : "$fechaEgreso $horaEgreso";

        return [
            new Hurge($fechaHora, $fechaHoraEgreso),
            new EgresoUrge($x["destino"], $fechaHoraEgreso)
        ];
    }

    private function loadEgreso(?int $docn): EgresoAdmision
    {
        $x = (!$docn) ? null : $this->q->getFechaHoraEgreso($docn);
        if(!$docn || !$x) {
            return new EgresoAdmision(null);
        }
        $x['hora'] = ($x['hora'] === '  :     ') ? '' : $x['hora'];

        return new EgresoAdmision("$x[fecha] $x[hora]");
    }
}
