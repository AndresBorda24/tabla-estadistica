<?php

declare(strict_types=1);

namespace App\Entity\Steps;

use App\Contracts\StepInterface;

class Triage extends BaseStep implements StepInterface
{
    public function __construct(
        public readonly int $triage,
        ?string $time,
        ?string $nextTime,
        /** Determina si se encontró o no la admisión */
        public readonly bool $admision,
        public readonly int $turnoId 
    ) {
        parent::__construct($time, $nextTime);
    }

    public function warning(): bool
    {
        if($this->admision && $this->strTime) return false;

        // Minutos de diferencia con la admisión
        $m = $this->getDiffInSeconds() / 60;
        $t = $this->triage;

        // Si no hay triage pero si admisión
        if ($this->admision && !$this->strTime) return $m >= 30;

        return match(true) {
            (in_array($t, [4,5]) && $m >= 240) => true,
            (in_array($t, [1,2]) && $m >= 25)  => true,
            ($t === 3 && $m >= 120) => true,
            default => false
        };
    }
}
