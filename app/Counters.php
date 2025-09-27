<?php

declare(strict_types=1);

namespace App;

class Counters
{
    private int $hombres = 0;
    private int $mujeres = 0;
    private int $alertas = 0;
    private int $general = 0;
    private int $sinHurge = 0;
    private int $sinAdmision = 0;
    private array $triage = [
        1 => 0,
        2 => 0,
        3 => 0,
        4 => 0,
        5 => 0
    ];

    public function toArray(): array
    {
        return [
            'hombres'   => $this->getHombres(),
            'mujeres'   => $this->getMujeres(),
            'alertas'   => $this->getAlertas(),
            'general'   => $this->getGeneral(),
            'sinHurge'  => $this->getSinHurge(),
            'triage'    => $this->getTriage(),
            'sinAdmision'   => $this->getSinAdmision(),
        ];
    }

    /**
     * Get the value of hombres
     */
    public function getHombres(): int
    {
        return $this->hombres;
    }

    /**
     * add the value of hombres
     */
    public function addHombre(int $hombres = 1): self
    {
        $this->hombres += $hombres;
        return $this;
    }

    /**
     * Get the value of mujeres
     */
    public function getMujeres(): int
    {
        return $this->mujeres;
    }

    /**
     * add the value of mujeres
     */
    public function addMujer(int $mujeres = 1): self
    {
        $this->mujeres += $mujeres;
        return $this;
    }

    /**
     * Get the value of alertas
     */
    public function getAlertas(): int
    {
        return $this->alertas;
    }

    /**
     * add the value of alertas
     */
    public function addAlerta(int $alertas = 1): self
    {
        $this->alertas += $alertas;
        return $this;
    }

    /**
     * Get the value of general
     */
    public function getGeneral(): int
    {
        return $this->general;
    }

    /**
     * add the value of general
     */
    public function addGeneral(int $general = 1): self
    {
        $this->general += $general;
        return $this;
    }

    /**
     * Get the value of sinHurge
     */
    public function getSinHurge(): int
    {
        return $this->sinHurge;
    }

    /**
     * add the value of sinHurge
     */
    public function addSinHurge(int $sinHurge = 1): self
    {
        $this->sinHurge += $sinHurge;
        return $this;
    }

    /**
     * Get the value of sinAdmision
     */
    public function getSinAdmision(): int
    {
        return $this->sinAdmision;
    }

    /**
     * add the value of sinAdmision
     */
    public function addSinAdmision(int $sinAdmision = 1): self
    {
        $this->sinAdmision += $sinAdmision;
        return $this;
    }

    /**
     * Get the value of triage
     */
    public function getTriage(): array
    {
        return $this->triage;
    }

    /**
     * add the value of triage
     */
    public function addTriage(int $triage, int $value = 1): self
    {
        if (array_key_exists($triage, $this->triage)) {
            $this->triage[$triage] += $value;
        }

        return $this;
    }
}
