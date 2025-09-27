<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\StepInterface;
use App\Counters;
use App\Entity\Medico;
use App\Entity\Paciente;
use App\Entity\Steps\Triage;
use App\Query;
use App\Services\StepLoaderService;
use App\DatosHurge;

class InfoLoaderService
{
    private array $pacientes = [];
    private Counters $contadores;
    public readonly Query $query;
    public readonly DatosHurge $datosHurge;
    public readonly StepLoaderService $stepLoaderService;

    public function __construct(Query $query)
    {
        $this->query = $query;
        $this->contadores = new Counters;
        $this->datosHurge = new DatosHurge($this->query->db);
        $this->stepLoaderService = new StepLoaderService($this->query);
    }

    public function loadWithTriage(string $fechaForGema): void
    {
        $triages = $this->query->getTriages($fechaForGema);
        foreach ($triages as $triage) {
            $paciente = Paciente::fromArray($triage);
            $docn = $this->query->getDocn($fechaForGema, $paciente->documento);
            $infoTriage = new Triage(
                triage: (int) $triage['clase_triage'],
                time: "$triage[fecha] $triage[hora]",
                nextTime: null,
                admision: ($docn !== null)
            );

            $this->handleData($docn, $paciente, $infoTriage);
        }
    }

    /**
     * En algunas ocaciones se realiza primero la admisión y luego, pasado un
     * tiempo, se realiza el registro del triage. Esta función está enfocada en
     * buscar esas admisiones y tenerlas en cuenta.
     */
    public function loadWithoutTriage(string $fechaForGema): void
    {
        $admisionesUrgencias = $this->query->getAdmisionesUrgencias($fechaForGema);
        // Estos son los docn que ya se cargaron con el triage.
        $docnsCargados = array_filter(
            array_map(fn($x) => $x['docn'], $this->pacientes)
        );
        // Aquí encontramos todas las admisiones de urgencias que no tienen triage
        $admisiones = array_filter(
            $admisionesUrgencias,
            fn($x) => ! in_array($x['docn'], $docnsCargados)
        );

        foreach ($admisiones as $docn) {
            $infoPaciente = $this->query->getInfoPaciente($docn['documento']);
            $paciente = ($infoPaciente)
                ? Paciente::fromArray($infoPaciente)
                : new Paciente('', 0, $docn['documento'], 'M');
            $infoTriage = new Triage(0, null, null, true);

            $this->handleData((int) $docn['docn'], $paciente, $infoTriage);
        }
    }

    /**
     * Organizamos los pacientes para que se muestren primero aquellos que presentan alertas.
    */
    public function getData(): array
    {
        usort($this->pacientes, function($a, $b) {
            $aWarning = $a['alerta'];
            $bWarning = $b['alerta'];
            if ($aWarning || $bWarning) return $bWarning <=> $aWarning;

            $aHora = $a['steps']['triage']['fecha'] ?? 'now';
            $bHora = $b['steps']['triage']['fecha'] ?? 'now';
           return strtotime($bHora) <=> strtotime($aHora);
        });

        return [
            "data" => $this->pacientes,
            "contadores" => $this->contadores->toArray()
        ];
    }

    private function handleData(?int $docn, Paciente $paciente, Triage $infoTriage): void
    {
        // Obtenemos la información de las horas de los diferentes parsos
        [$admision, $hurge, $egresoUrge, $egreso] = $this->stepLoaderService->load($docn);
        $infoTriage->setNextDate($admision->strTime);

        // Información de la hoja de Urgencias
        $informacionHurgencias = ($docn) ?
            $this->datosHurge->getAll((string) $docn, $paciente->documento)
            : [];

        // Información del médico
        $medicoInfo = null;
        if ($docn && $_med = $this->query->getMedico($docn)) {
            $medicoInfo = new Medico($_med['codigo'], $_med['nombre']);
        }

        // Actualizar contadores
        $this->contadores->addGeneral();
        (!$docn) && $this->contadores->addSinAdmision();
        $this->contadores->addTriage($infoTriage->triage);
        (!$hurge->strTime) && $this->contadores->addSinHurge();
        ($paciente->genero === "F")
            ? $this->contadores->addMujer()
            : $this->contadores->addHombre();
        $hayAlerta = array_reduce(
            [$infoTriage, $admision, $hurge, $egresoUrge, $egreso],
            function(bool $c, StepInterface $s) {
                if ($s->warning()) $this->contadores->addAlerta();
                return $c || $s->warning();
            },
            false
        );

        // Array con la información
        $this->pacientes[] = [
            "docn" => $docn,
            "medico" => $medicoInfo,
            "paciente" => $paciente,
            "clase_triage" => $infoTriage->triage,
            "egreso_urge" => $egresoUrge->destino,
            "infoUrgencias" => $informacionHurgencias,
            "alerta" => $hayAlerta,
            "steps" => [
                "triage"      => $infoTriage->toArray(),
                "admision"    => $admision->toArray(),
                "hurge"       => $hurge->toArray(),
                "egresoHurge" => $egresoUrge->toArray(),
                "egreso"      => $egreso->toArray()
            ]
        ];
    }
}
